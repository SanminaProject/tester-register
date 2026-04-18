<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTesterAssignmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assignments = [
            [
                'user_email' => 'admin@example.com',
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE',
            ],
            [
                'user_email' => 'admin@example.com',
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE #2',
            ],
            [
                'user_email' => 'technician@example.com',
                'tester_name' => '9S_SWDL2',
            ],
            [
                'user_email' => 'technician@example.com',
                'tester_name' => 'DIT1',
            ],
            [
                'user_email' => 'manager@example.com',
                'tester_name' => 'EST3',
            ],
            [
                'user_email' => 'technician@example.com',
                'tester_name' => 'EST4',
            ],
            [
                'user_email' => 'manager@example.com',
                'tester_name' => '9S_SWDL1',
            ],
        ];

        foreach ($assignments as $assignment) {
            $userId = DB::table('users')
                ->where('email', $assignment['user_email'])
                ->value('id');

            $testerId = DB::table('testers')
                ->where('name', $assignment['tester_name'])
                ->value('id');

            if ($userId && $testerId) {
                DB::table('user_tester_assignments')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'tester_id' => $testerId,
                    ]
                );
            }
        }
    }
}