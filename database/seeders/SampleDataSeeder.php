<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create sample users
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pastor John',
                'email' => 'pastor@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Secretary Sarah',
                'email' => 'secretary@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        $userIds = [];
        if (Schema::hasTable('users')) {
            $userColumns = array_flip(Schema::getColumnListing('users'));
            foreach ($users as $user) {
                $data = array_intersect_key($user, $userColumns);
                DB::table('users')->updateOrInsert(
                    ['email' => $user['email']],
                    array_diff_key($data, ['email' => true])
                );
            }
        }

        // Create sample member statuses
        $statuses = [
            ['name' => 'Active', 'description' => 'Active member'],
            ['name' => 'Inactive', 'description' => 'Inactive member'],
            ['name' => 'Visitor', 'description' => 'First-time visitor'],
            ['name' => 'Regular', 'description' => 'Regular attendee'],
            ['name' => 'Member', 'description' => 'Official member'],
        ];

        $statusIds = [];
        $statusIds = [];
        if (Schema::hasTable('member_statuses')) {
            $statusColumns = array_flip(Schema::getColumnListing('member_statuses'));
            foreach ($statuses as $status) {
                $data = array_intersect_key($status, $statusColumns);
                DB::table('member_statuses')->updateOrInsert(
                    ['name' => $status['name']],
                    array_diff_key($data, ['name' => true])
                );
            }
            $statusIds = DB::table('member_statuses')->pluck('id')->toArray();
        }

        // Create sample groups
        $groups = [
            ['name' => 'Worship Team', 'description' => 'Music and worship ministry'],
            ['name' => 'Youth Group', 'description' => 'Teen ministry'],
            ['name' => 'Men\'s Fellowship', 'description' => 'Men\'s ministry'],
            ['name' => 'Women\'s Ministry', 'description' => 'Women\'s ministry'],
            ['name' => 'Sunday School', 'description' => 'Children\'s ministry'],
        ];

        $groupIds = [];
        $groupIds = [];
        if (Schema::hasTable('groups')) {
            $groupColumns = array_flip(Schema::getColumnListing('groups'));
            foreach ($groups as $group) {
                $data = array_intersect_key($group, $groupColumns);
                DB::table('groups')->updateOrInsert(
                    ['name' => $group['name']],
                    array_diff_key($data, ['name' => true])
                );
            }
            $groupIds = DB::table('groups')->pluck('id')->toArray();
        }

        // Create sample members
        $members = [];
        $firstNames = ['John', 'Jane', 'Michael', 'Emily', 'David', 'Sarah', 'Robert', 'Jennifer', 'William', 'Elizabeth'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia', 'Rodriguez', 'Wilson'];
        $genders = ['Male', 'Female', 'Other'];
        $maritalStatuses = ['Single', 'Married', 'Divorced', 'Widowed'];
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        for ($i = 0; $i < 50; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $email = strtolower($firstName . '.' . $lastName . ($i > 0 ? $i : '') . '@example.com');
            $phone = '555-' . str_pad(rand(100, 999), 3, '0') . '-' . str_pad(rand(1000, 9999), 4, '0');
            $birthDate = now()->subYears(rand(18, 80))->subDays(rand(0, 365));
            $joinDate = now()->subYears(rand(0, 10))->subDays(rand(0, 365));
            $statusId = $statusIds[array_rand($statusIds)];
            
            $members[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'gender' => $genders[array_rand($genders)],
                'date_of_birth' => $birthDate,
                'marital_status' => $maritalStatuses[array_rand($maritalStatuses)],
                'blood_type' => $bloodTypes[array_rand($bloodTypes)],
                'address' => rand(100, 9999) . ' ' . ['Main', 'Oak', 'Pine', 'Maple', 'Cedar', 'Elm', 'Walnut', 'Cherry'][array_rand([0,1,2,3,4,5,6,7])] . ' St.',
                'city' => ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego'][array_rand([0,1,2,3,4,5,6,7])],
                'state' => ['NY', 'CA', 'IL', 'TX', 'AZ', 'PA', 'TX', 'CA'][array_rand([0,1,2,3,4,5,6,7])],
                'zip' => str_pad(rand(10000, 99999), 5, '0'),
                'country' => 'USA',
                'membership_status_id' => $statusId,
                'membership_date' => $joinDate,
                'baptism_date' => rand(0, 1) ? $joinDate->copy()->subDays(rand(30, 365)) : null,
                'notes' => rand(0, 1) ? 'Sample note for ' . $firstName . ' ' . $lastName : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert members in chunks to avoid memory issues
        if (Schema::hasTable('members')) {
            $memberColumns = array_flip(Schema::getColumnListing('members'));
            foreach (array_chunk($members, 10) as $chunk) {
                foreach ($chunk as $member) {
                    $data = array_intersect_key($member, $memberColumns);
                    DB::table('members')->updateOrInsert(
                        ['email' => $member['email']],
                        array_diff_key($data, ['email' => true])
                    );
                }
            }
        }

        // Assign members to groups
        $memberGroupData = [];
        $memberIds = DB::table('members')->pluck('id')->toArray();
        
        foreach ($memberIds as $memberId) {
            // Each member is in 1-3 random groups
            $numGroups = rand(1, 3);
            $assignedGroups = array_rand($groupIds, $numGroups);
            
            if (!is_array($assignedGroups)) {
                $assignedGroups = [$assignedGroups];
            }
            
            foreach ($assignedGroups as $groupIndex) {
                $memberGroupData[] = [
                    'member_id' => $memberId,
                    'group_id' => $groupIds[$groupIndex],
                    'role' => ['Member', 'Leader', 'Assistant', 'Volunteer'][array_rand([0,1,2,3])],
                    'joined_at' => now()->subDays(rand(1, 365)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert member-group relationships in chunks
        if (Schema::hasTable('group_member')) {
            $pivotColumns = array_flip(Schema::getColumnListing('group_member'));
            foreach (array_chunk($memberGroupData, 100) as $chunk) {
                foreach ($chunk as $row) {
                    $data = array_intersect_key($row, $pivotColumns);
                    DB::table('group_member')->updateOrInsert(
                        ['member_id' => $row['member_id'], 'group_id' => $row['group_id']],
                        array_diff_key($data, ['member_id' => true, 'group_id' => true])
                    );
                }
            }
        }

        $this->command->info('Sample data seeded successfully!');
    }
}
