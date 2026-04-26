<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSparePartAssignmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assignments = [
            [
                'user_email' => 'admin@example.com',
                'spare_part_name' => 'PCB Assembly Board',
            ],
            [
                'user_email' => 'admin@example.com',
                'spare_part_name' => 'Calibration Standard',
            ],
            [
                'user_email' => 'technician@example.com',
                'spare_part_name' => 'Probe Tip Set',
            ],
            [
                'user_email' => 'technician@example.com',
                'spare_part_name' => 'Vacuum Pump Cartridge',
            ],
            [
                'user_email' => 'technician@example.com',
                'spare_part_name' => 'Test Head Motor',
            ],
            [
                'user_email' => 'manager@example.com',
                'spare_part_name' => 'Test Head Motor',
            ],
        ];

        foreach ($assignments as $assignment) {
            $userId = DB::table('users')
                ->where('email', $assignment['user_email'])
                ->value('id');

            $sparePartId = DB::table('tester_spare_parts')
                ->where('name', $assignment['spare_part_name'])
                ->value('id');

            if ($userId && $sparePartId) {
                DB::table('user_spare_part_assignments')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'spare_part_id' => $sparePartId,
                    ]
                );
            }
        }
    }
}