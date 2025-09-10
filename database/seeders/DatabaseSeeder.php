<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesSeeder::class,
            UsersSeeder::class,
            UserSeeder::class,
            MemberManagementPermissionsSeeder::class,
            MemberStatusesTableSeeder::class, // Add this line
            SampleDataSeeder::class,
        ]);
    }
}