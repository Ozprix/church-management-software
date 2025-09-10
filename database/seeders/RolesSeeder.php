<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create or update Super Admin role
        Role::updateOrCreate(
            ['name' => 'Super Admin'],
            ['permissions' => json_encode([
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
                'manage_reports'
            ])]
        );

        // Create or update Admin role
        Role::updateOrCreate(
            ['name' => 'Admin'],
            ['permissions' => json_encode([
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
                'manage_reports'
            ])]
        );

        // Create or update User role
        Role::updateOrCreate(
            ['name' => 'User'],
            ['permissions' => json_encode([
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
                'view_reports'
            ])]
        );
    }
}