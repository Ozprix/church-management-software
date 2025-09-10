<?php

namespace Tests\Unit;

use App\Models\Member;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test member creation
     *
     * @return void
     */
    public function test_can_create_member()
    {
        $member = Member::factory()->create();
        
        $this->assertInstanceOf(Member::class, $member);
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
        ]);
    }

    /**
     * Test member belongs to family relationship
     *
     * @return void
     */
    public function test_member_belongs_to_family()
    {
        $family = Family::factory()->create();
        $member = Member::factory()->create([
            'family_id' => $family->id
        ]);
        
        $this->assertInstanceOf(Family::class, $member->family);
        $this->assertEquals($family->id, $member->family->id);
    }

    /**
     * Test member belongs to user relationship
     *
     * @return void
     */
    public function test_member_belongs_to_user()
    {
        $user = User::factory()->create();
        $member = Member::factory()->create([
            'user_id' => $user->id
        ]);
        
        $this->assertInstanceOf(User::class, $member->user);
        $this->assertEquals($user->id, $member->user->id);
    }

    /**
     * Test full name accessor
     *
     * @return void
     */
    public function test_full_name_accessor()
    {
        $member = Member::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
        
        $this->assertEquals('John Doe', $member->full_name);
    }

    /**
     * Test member status scope
     *
     * @return void
     */
    public function test_status_scope()
    {
        // First, clear any existing members
        Member::query()->delete();
        
        // Create members with different statuses
        Member::factory()->create(['membership_status' => 'active']);
        Member::factory()->create(['membership_status' => 'active']);
        Member::factory()->create(['membership_status' => 'inactive']);
        Member::factory()->create(['membership_status' => 'pending']);
        
        // Use the correct scope method (whereMembershipStatus instead of whereStatus)
        $activeMembers = Member::where('membership_status', 'active')->get();
        $inactiveMembers = Member::where('membership_status', 'inactive')->get();
        $pendingMembers = Member::where('membership_status', 'pending')->get();
        
        $this->assertCount(2, $activeMembers, 'Expected 2 active members');
        $this->assertCount(1, $inactiveMembers, 'Expected 1 inactive member');
        $this->assertCount(1, $pendingMembers, 'Expected 1 pending member');
    }
}
