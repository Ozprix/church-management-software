<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MemberApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);
    }

    /**
     * Test retrieving a list of members.
     *
     * @return void
     */
    public function test_can_get_members_list()
    {
        // Create some members
        Member::factory()->count(5)->create();
        
        // Make the API request
        $response = $this->getJson('/api/members');
        
        // Assert the response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'data',
                        'current_page',
                        'per_page',
                        'last_page',
                        'total'
                    ]
                ])
                ->assertJson(['status' => 'success'])
                ->assertJsonCount(5, 'data.data');
    }

    /**
     * Test retrieving a single member.
     *
     * @return void
     */
    public function test_can_get_single_member()
    {
        // Create a member
        $member = Member::factory()->create();
        
        // Make the API request
        $response = $this->getJson("/api/members/{$member->id}");
        
        // Assert the response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'id' => $member->id,
                        'first_name' => $member->first_name,
                        'last_name' => $member->last_name
                    ]
                ]);
    }

    /**
     * Test creating a new member.
     *
     * @return void
     */
    public function test_can_create_member()
    {
        // Prepare member data
        $memberData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'membership_status' => 'active',
            'membership_date' => $this->faker->date()
        ];
        
        // Make the API request
        $response = $this->postJson('/api/members', $memberData);
        
        // Assert the response
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email'
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Member created successfully',
                    'data' => [
                        'first_name' => $memberData['first_name'],
                        'last_name' => $memberData['last_name'],
                        'email' => $memberData['email']
                    ]
                ]);
                
        // Assert the data was saved to the database
        $this->assertDatabaseHas('members', [
            'first_name' => $memberData['first_name'],
            'last_name' => $memberData['last_name'],
            'email' => $memberData['email']
        ]);
    }

    /**
     * Test updating a member.
     *
     * @return void
     */
    public function test_can_update_member()
    {
        // Create a member
        $member = Member::factory()->create();
        
        // Prepare update data
        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com'
        ];
        
        // Make the API request
        $response = $this->putJson("/api/members/{$member->id}", $updateData);
        
        // Assert the response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data'
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Member updated successfully',
                    'data' => [
                        'id' => $member->id,
                        'first_name' => 'Updated',
                        'last_name' => 'Name',
                        'email' => 'updated@example.com'
                    ]
                ]);
                
        // Assert the data was updated in the database
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com'
        ]);
    }

    /**
     * Test deleting a member.
     *
     * @return void
     */
    public function test_can_delete_member()
    {
        // Create a member
        $member = Member::factory()->create();
        
        // Make the API request
        $response = $this->deleteJson("/api/members/{$member->id}");
        
        // Assert the response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message'
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Member deleted successfully'
                ]);
                
        // Assert the data was deleted from the database
        $this->assertDatabaseMissing('members', [
            'id' => $member->id
        ]);
    }
}
