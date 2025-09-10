<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MemberManagementPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!Schema::hasTable('permissions')) {
            $this->command?->warn('Skipping MemberManagementPermissionsSeeder: permissions table not found.');
            return;
        }

        $permissions = [
            [
                'name' => 'manage_member_skills',
                'display_name' => 'Manage Member Skills',
                'description' => 'Ability to add, edit, and remove skills for members',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'manage_member_interests',
                'display_name' => 'Manage Member Interests',
                'description' => 'Ability to add, edit, and remove interests for members',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'manage_spiritual_gifts',
                'display_name' => 'Manage Spiritual Gifts',
                'description' => 'Ability to add, edit, and remove spiritual gifts for members',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'manage_member_availability',
                'display_name' => 'Manage Member Availability',
                'description' => 'Ability to add, edit, and remove availability for members',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'view_ministry_matches',
                'display_name' => 'View Ministry Matches',
                'description' => 'Ability to view ministry matching recommendations for members',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Insert permissions
        foreach ($permissions as $permission) {
            // Check if permission already exists to avoid duplicates
            $exists = DB::table('permissions')
                ->where('name', $permission['name'])
                ->exists();

            if (!$exists) {
                DB::table('permissions')->insert($permission);
            }
        }

        // Assign permissions to admin role
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        
        if ($adminRoleId) {
            $permissionIds = DB::table('permissions')
                ->whereIn('name', array_column($permissions, 'name'))
                ->pluck('id');
            
            foreach ($permissionIds as $permissionId) {
                // Check if role permission already exists
                $exists = DB::table('permission_role')
                    ->where('permission_id', $permissionId)
                    ->where('role_id', $adminRoleId)
                    ->exists();
                
                if (!$exists) {
                    DB::table('permission_role')->insert([
                        'permission_id' => $permissionId,
                        'role_id' => $adminRoleId,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }

        // Assign member availability permission to member role (so members can manage their own availability)
        $memberRoleId = DB::table('roles')->where('name', 'member')->value('id');
        
        if ($memberRoleId) {
            $memberAvailabilityPermissionId = DB::table('permissions')
                ->where('name', 'manage_member_availability')
                ->value('id');
            
            if ($memberAvailabilityPermissionId) {
                // Check if role permission already exists
                $exists = DB::table('permission_role')
                    ->where('permission_id', $memberAvailabilityPermissionId)
                    ->where('role_id', $memberRoleId)
                    ->exists();
                
                if (!$exists) {
                    DB::table('permission_role')->insert([
                        'permission_id' => $memberAvailabilityPermissionId,
                        'role_id' => $memberRoleId,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
    }
}
