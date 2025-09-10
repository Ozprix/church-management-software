<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MemberStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            [
                'name' => 'Active Member',
                'description' => 'Active church member with full privileges',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Regular Attender',
                'description' => 'Regularly attends but not an official member',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'New Believer',
                'description' => 'New to the faith and church community',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Visitor',
                'description' => 'First-time or occasional visitor',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inactive',
                'description' => 'Inactive member',
                'is_active' => false,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transferred',
                'description' => 'Transferred to another church',
                'is_active' => false,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Deceased',
                'description' => 'Deceased member',
                'is_active' => false,
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];


        // Upsert statuses by unique name
        foreach ($statuses as $status) {
            DB::table('member_statuses')->updateOrInsert(
                ['name' => $status['name']],
                array_diff_key($status, ['name' => true])
            );
        }
    }
}
