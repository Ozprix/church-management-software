<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Member;
use App\Models\MemberStatus;
use App\Services\MemberImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MemberImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $memberImportService;
    protected $activeStatus;
    protected $inactiveStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test member statuses
        $this->activeStatus = MemberStatus::create([
            'name' => 'Active Member',
            'description' => 'Active church member',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->inactiveStatus = MemberStatus::create([
            'name' => 'Inactive',
            'description' => 'Inactive member',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $this->memberImportService = app(MemberImportService::class);
    }

    /** @test */
    public function it_can_import_members_from_csv()
    {
        Storage::fake('imports');

        // Create a test CSV file
        $header = 'first_name,middle_name,last_name,email,phone,gender,date_of_birth,membership_status';
        $row1 = 'John,M,Doe,john.doe@example.com,555-123-4567,male,1985-05-15,Active Member';
        $row2 = 'Jane,,Smith,jane.smith@example.com,555-987-6543,female,1990-10-20,Active Member';
        
        $content = implode("\n", [$header, $row1, $row2]);
        
        $file = UploadedFile::fake()->createWithContent(
            'test_import.csv',
            $content
        );

        // Execute the import
        $result = $this->memberImportService->import($file, false, []);

        // Assertions
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['imported']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(0, $result['failed']);
        
        // Check if members were created
        $this->assertDatabaseHas('members', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'middle_name' => 'M',
            'last_name' => 'Doe',
            'membership_status_id' => $this->activeStatus->id,
        ]);
        
        $this->assertDatabaseHas('members', [
            'email' => 'jane.smith@example.com',
            'first_name' => 'Jane',
            'middle_name' => null,
            'last_name' => 'Smith',
            'membership_status_id' => $this->activeStatus->id,
        ]);
    }

    /** @test */
    public function it_can_update_existing_members()
    {
        // Create an existing member
        $member = Member::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '555-000-0000',
            'membership_status_id' => $this->inactiveStatus->id,
        ]);

        Storage::fake('imports');

        // Create a test CSV file with updated data
        $header = 'first_name,middle_name,last_name,email,phone,gender,date_of_birth,membership_status';
        $row1 = 'John,Michael,Doe,john.doe@example.com,555-123-4567,male,1985-05-15,Active Member';
        
        $content = implode("\n", [$header, $row1]);
        
        $file = UploadedFile::fake()->createWithContent(
            'test_update.csv',
            $content
        );

        // Execute the import with update_existing = true
        $result = $this->memberImportService->import($file, true, []);

        // Assertions
        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(1, $result['updated']);
        $this->assertEquals(0, $result['failed']);
        
        // Check if member was updated
        $member->refresh();
        $this->assertEquals('Michael', $member->middle_name);
        $this->assertEquals('555-123-4567', $member->phone);
        $this->assertEquals($this->activeStatus->id, $member->membership_status_id);
    }

    /** @test */
    public function it_handles_invalid_data()
    {
        Storage::fake('imports');

        // Create a test CSV file with invalid data
        $header = 'first_name,last_name,email,phone,date_of_birth';
        $row1 = 'John,Doe,invalid-email,not-a-phone,invalid-date';
        $row2 = 'Jane,Smith,jane.smith@example.com,555-987-6543,1990-10-20';
        
        $content = implode("\n", [$header, $row1, $row2]);
        
        $file = UploadedFile::fake()->createWithContent(
            'test_invalid.csv',
            $content
        );

        // Execute the import
        $result = $this->memberImportService->import($file, false, []);

        // Assertions
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['imported']); // Only one valid row
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(1, $result['failed']); // One row with validation errors
        
        // Check error details
        $this->assertCount(1, $result['errors']);
        $this->assertArrayHasKey('row', $result['errors'][0]);
        $this->assertArrayHasKey('errors', $result['errors'][0]);
        $this->assertArrayHasKey('email', $result['errors'][0]['errors']);
        $this->assertArrayHasKey('phone', $result['errors'][0]['errors']);
        $this->assertArrayHasKey('date_of_birth', $result['errors'][0]['errors']);
        
        // Check if valid row was imported
        $this->assertDatabaseHas('members', [
            'email' => 'jane.smith@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
    }

    /** @test */
    public function it_can_import_with_field_mapping()
    {
        Storage::fake('imports');

        // Create a test CSV file with custom headers
        $header = 'First,Last,Email,Phone,DOB,Status';
        $row1 = 'John,Doe,john.doe@example.com,555-123-4567,1985-05-15,Active Member';
        
        $content = implode("\n", [$header, $row1]);
        
        $file = UploadedFile::fake()->createWithContent(
            'test_mapping.csv',
            $content
        );

        // Define field mapping
        $mapping = [
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'Email',
            'phone' => 'Phone',
            'date_of_birth' => 'DOB',
            'membership_status' => 'Status',
        ];

        // Execute the import with field mapping
        $result = $this->memberImportService->import($file, false, $mapping);

        // Assertions
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(0, $result['failed']);
        
        // Check if member was imported with correct mapping
        $this->assertDatabaseHas('members', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '555-123-4567',
            'membership_status_id' => $this->activeStatus->id,
        ]);
    }
}
