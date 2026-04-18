<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataChangeLogsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $changeLogsData = [
            [
                'changed_at' => now()->subDays(30),
                'explanation' => 'Updated tester status from inactive to active',
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE',
                'fixture_name' => null,
                'spare_part_name' => null,
                'user_email' => 'admin@example.com',
            ],
            [
                'changed_at' => now()->subDays(25),
                'explanation' => 'Replaced probe tips - maintenance performed',
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE',
                'fixture_name' => null,
                'spare_part_name' => 'Probe Tip Set',
                'user_email' => 'technician@example.com',
            ],
            [
                'changed_at' => now()->subDays(20),
                'explanation' => 'Calibration standard ordered - stock running low',
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE',
                'fixture_name' => null,
                'spare_part_name' => 'Calibration Standard',
                'user_email' => 'admin@example.com',
            ],
            [
                'changed_at' => now()->subDays(15),
                'explanation' => 'Fixture A recalibrated and validated',
                'tester_name' => null,
                'fixture_name' => 'Fixture A',
                'spare_part_name' => null,
                'user_email' => 'technician@example.com',
            ],
            [
                'changed_at' => now()->subDays(10),
                'explanation' => 'Vacuum pump cartridge installed - preventative maintenance',
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE #2',
                'fixture_name' => null,
                'spare_part_name' => 'Vacuum Pump Cartridge',
                'user_email' => 'technician@example.com',
            ],
            [
                'changed_at' => now()->subDays(5),
                'explanation' => 'Test head motor replaced due to bearing wear',
                'tester_name' => 'DIT1',
                'fixture_name' => null,
                'spare_part_name' => 'Test Head Motor',
                'user_email' => 'admin@example.com',
            ],
            [
                'changed_at' => now()->subDays(2),
                'explanation' => 'Fixture B location changed to DNWP-CM zone',
                'tester_name' => null,
                'fixture_name' => 'Fixture B',
                'spare_part_name' => null,
                'user_email' => 'admin@example.com',
            ],
        ];

        foreach ($changeLogsData as $log) {
            $testerId = null;
            $fixtureId = null;
            $sparePartId = null;
            $userId = null;

            // Get IDs for related records
            if ($log['tester_name']) {
                $testerId = DB::table('testers')
                    ->where('name', $log['tester_name'])
                    ->value('id');
            }

            if ($log['fixture_name']) {
                $fixtureId = DB::table('fixtures')
                    ->where('name', $log['fixture_name'])
                    ->value('id');
            }

            if ($log['spare_part_name'] && $testerId) {
                $sparePartId = DB::table('tester_spare_parts')
                    ->where('name', $log['spare_part_name'])
                    ->where('tester_id', $testerId)
                    ->value('id');
            }

            if ($log['user_email']) {
                $userId = DB::table('users')
                    ->where('email', $log['user_email'])
                    ->value('id');
            }

            // Insert the change log
            DB::table('data_change_logs')->insert([
                'changed_at' => $log['changed_at'],
                'explanation' => $log['explanation'],
                'tester_id' => $testerId,
                'fixture_id' => $fixtureId,
                'spare_part_id' => $sparePartId,
                'user_id' => $userId,
            ]);
        }
    }
}
