<?php

namespace Tests\Feature\Api;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
    }

    /** @test */
    public function it_can_list_members()
    {
        Member::factory()->count(3)->create();

        $response = $this->getJson('/api/members');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'links' => [
                    'first', 'last', 'prev', 'next'
                ],
                'meta' => [
                    'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total'
                ]
            ]);
    }

    /** @test */
    public function it_can_create_a_member()
    {
        $memberData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'gender' => 'male',
            'date_of_birth' => $this->faker->date(),
            'membership_status' => 'active',
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->stateAbbr,
            'zip' => $this->faker->postcode,
            'country' => $this->faker->country,
        ];

        $response = $this->postJson('/api/members', $memberData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'first_name' => $memberData['first_name'],
                    'last_name' => $memberData['last_name'],
                    'email' => $memberData['email'],
                ]
            ]);

        $this->assertDatabaseHas('members', [
            'email' => $memberData['email']
        ]);
    }

    /** @test */
    public function it_can_show_a_member()
    {
        $member = Member::factory()->create();

        $response = $this->getJson("/api/members/{$member->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $member->id,
                    'first_name' => $member->first_name,
                    'last_name' => $member->last_name,
                    'email' => $member->email,
                ]
            ]);
    }

    /** @test */
    public function it_can_update_a_member()
    {
        $member = Member::factory()->create();
        
        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/members/{$member->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $member->id,
                    'first_name' => 'Updated',
                    'last_name' => 'Name',
                    'email' => 'updated@example.com',
                ]
            ]);

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function it_can_delete_a_member()
    {
        $member = Member::factory()->create();

        $response = $this->deleteJson("/api/members/{$member->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Member deleted successfully.'
            ]);

        $this->assertDatabaseMissing('members', [
            'id' => $member->id
        ]);
    }

    /** @test */
    public function it_can_search_members()
    {
        $member1 = Member::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ]);
        
        $member2 = Member::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com'
        ]);

        $response = $this->getJson('/api/members/search?q=john');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ]);
    }

    /** @test */
    public function it_can_get_member_statistics()
    {
        Member::factory()->count(5)->create(['membership_status' => 'active']);
        Member::factory()->count(3)->create(['membership_status' => 'inactive']);
        Member::factory()->count(2)->create(['membership_status' => 'pending']);

        $response = $this->getJson('/api/members/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'active',
                    'inactive',
                    'pending',
                    'average_age',
                    'gender_distribution'
                ]
            ]);
    }

    /** @test */
    public function it_can_export_members()
    {
        Member::factory()->count(3)->create();

        $response = $this->getJson('/api/members/export?format=csv');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition');
    }

    /** @test */
    public function it_can_import_members()
    {
        Storage::fake('local');
        
        $header = 'first_name,last_name,email,phone,gender,date_of_birth,membership_status';
        $row1 = 'John,Doe,john@example.com,1234567890,male,1990-01-01,active';
        $row2 = 'Jane,Smith,jane@example.com,0987654321,female,1992-05-15,active';
        
        $content = implode("\n", [$header, $row1, $row2]);
        
        $file = UploadedFile::fake()->createWithContent(
            'members.csv',
            $content
        );

        $response = $this->postJson('/api/members/import', [
            'file' => $file,
            'update_existing' => false
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Members imported successfully.',
                'data' => [
                    'imported' => 2,
                    'updated' => 0,
                    'failed' => 0,
                    'errors' => []
                ]
            ]);

        $this->assertDatabaseHas('members', [
            'email' => 'john@example.com'
        ]);
        
        $this->assertDatabaseHas('members', [
            'email' => 'jane@example.com'
        ]);
    }

    /** @test */
    public function it_can_perform_bulk_actions()
    {
        $members = Member::factory()->count(3)->create(['membership_status' => 'active']);
        $memberIds = $members->pluck('id')->toArray();

        // Test bulk status update
        $response = $this->postJson('/api/members/bulk-actions', [
            'action' => 'update_status',
            'ids' => $memberIds,
            'status' => 'inactive'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Bulk action completed.',
                'data' => [
                    'updated' => 3,
                    'failed' => 0,
                    'errors' => []
                ]
            ]);

        // Verify the status was updated
        foreach ($memberIds as $id) {
            $this->assertDatabaseHas('members', [
                'id' => $id,
                'membership_status' => 'inactive'
            ]);
        }

        // Test bulk delete
        $response = $this->postJson('/api/members/bulk-actions', [
            'action' => 'delete',
            'ids' => $memberIds
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Bulk action completed.',
                'data' => [
                    'deleted' => 3,
                    'failed' => 0,
                    'errors' => []
                ]
            ]);

        // Verify the members were deleted
        $this->assertEquals(0, Member::whereIn('id', $memberIds)->count());
    }
}
