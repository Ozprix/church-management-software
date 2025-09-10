<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        if ($superAdminRole) {
            // Create or update a Super Admin user
            User::updateOrCreate(
                ['email' => 'admin@church.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $superAdminRole->id,
                ]
            );
        }
    }
}