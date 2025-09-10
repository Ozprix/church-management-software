<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Admin', 'User', 'Super Admin']),
            'permissions' => json_encode([]),
        ];
    }

    public function admin()
    {
        return $this->state([
            'name' => 'Admin',
            'permissions' => json_encode([
                'manage_members',
                'manage_families',
                'manage_attendance',
                'manage_events',
                'manage_donations',
                'manage_expenses',
                'manage_campaigns',
                'manage_communications',
                'manage_prayer_requests',
                'manage_volunteers',
                'manage_reports',
            ]),
        ]);
    }

    public function superAdmin()
    {
        return $this->state([
            'name' => 'Super Admin',
            'permissions' => json_encode([
                'manage_users',
                'manage_roles',
                'manage_members',
                'manage_families',
                'manage_attendance',
                'manage_events',
                'manage_donations',
                'manage_expenses',
                'manage_campaigns',
                'manage_communications',
                'manage_prayer_requests',
                'manage_volunteers',
                'manage_reports',
            ]),
        ]);
    }

    public function user()
    {
        return $this->state([
            'name' => 'User',
            'permissions' => json_encode([
                'view_members',
                'view_families',
                'view_attendance',
                'view_events',
                'view_donations',
                'view_expenses',
                'view_campaigns',
                'view_communications',
                'view_prayer_requests',
                'view_volunteers',
                'view_reports',
            ]),
        ]);
    }
}
