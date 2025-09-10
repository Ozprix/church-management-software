<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_users()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_users()
    {
        $role = \App\Models\Role::factory()->admin()->create();
        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
            'role_id' => $role->id,
        ]);
        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Add User'); // More robust: check for a unique button or heading
    }
}
