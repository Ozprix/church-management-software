<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Member;
use App\Models\MemberStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MemberImportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminUser;
    protected $memberStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test member status
        $this->memberStatus = MemberStatus::create([
            'name' => 'Active Member',
            'description' => 'Active church member',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Create regular user
        $this->user = User::factory()->create();
        
        // Create admin user with permissions
        $this->adminUser = User::factory()->create();
        
        // Create and assign permissions
        $permission = Permission::firstOrCreate(['name' => 'members.import']);
        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo($permission);
        $this->adminUser->assignRole($role);
        
        // Fake the storage for testing file uploads
        Storage::fake('local');
    }

    /** @test */
    public function guest_cannot_import_members()
    {
        $response = $this->postJson('/api/members/import/process', []);
        $response->assertStatus(401);
    }

    /** @test */
    public function user_without_permission_cannot_import_members()
    {
        $this->actingAs($this->user);
        
        $response = $this->postJson('/api/members/import/process', []);
        
        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_import_request()
    {
        $this->actingAs($this->adminUser);
        
        $response = $this->postJson('/api/members/import/process', [
            'file' => 'not-a-file',
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_can_import_members_via_api()
    {
        $this->actingAs($this->adminUser);
        
        // Create a test CSV file
        $header = 'first_name,last_name,email,phone,membership_status';
        $row1 = 'John,Doe,john.doe@example.com,555-123-4567,Active Member';
        $row2 = 'Jane,Smith,jane.smith@example.com,555-987-6543,Active Member';
        
        $content = implode("\n", [$header, $row1, $row2]);
        
        $file = UploadedFile::fake()->createWithContent(
            'test_import.csv',
            $content
        );
        
        $response = $this->postJson('/api/members/import/process', [
            'file' => $file,
            'update_existing' => false,
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Import completed. 2 imported, 0 updated, 0 failed.',
            'data' => [
                'imported' => 2,
                'updated' => 0,
                'failed' => 0,
            ],
        ]);
        
        // Check if members were created
        $this->assertDatabaseHas('members', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        
        $this->assertDatabaseHas('members', [
            'email' => 'jane.smith@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
    }

    /** @test */
    public function it_can_download_import_template()
    {
        $this->actingAs($this->adminUser);
        
        $response = $this->get('/api/members/import/template');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="member_import_template.csv"');
        
        // Check if the response contains the expected CSV headers
        $content = $response->getContent();
        $this->assertStringContainsString('first_name', $content);
        $this->assertStringContainsString('last_name', $content);
        $this->assertStringContainsString('email', $content);
        $this->assertStringContainsString('phone', $content);
    }

    /** @test */
    public function it_handles_import_errors_gracefully()
    {
        $this->actingAs($this->adminUser);
        
        // Create a test CSV file with invalid data
        $header = 'first_name,last_name,email,phone';
        $row1 = 'John,Doe,invalid-email,not-a-phone';
        
        $content = implode("\n", [$header, $row1]);
        
        $file = UploadedFile::fake()->createWithContent(
            'test_invalid.csv',
            $content
        );
        
        $response = $this->postJson('/api/members/import/process', [
            'file' => $file,
            'update_existing' => false,
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'imported' => 0,
                'updated' => 0,
                'failed' => 1,
            ],
        ]);
        
        // Check error details
        $responseData = $response->json();
        $this->assertArrayHasKey('errors', $responseData['data']);
        $this->assertCount(1, $responseData['data']['errors']);
    }
}
