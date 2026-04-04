<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TesterEventLogsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testerEventLogs = [
            [
                'date' => Carbon::now()->subDays(1),
                'description' => 'Standard Maintenance performed on TAKAYA FLYING PROBE APT 8400CE',

                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE',
                'event_type' => 'maintenance',
                'user_email' => 'test@example.com',
                'maintenance_type' => 'Standard Maintenance',
            ],
            [
                'date' => Carbon::now()->subDays(1),
                'description' => 'Full Calibration performed on DIT1',

                'tester_name' => 'DIT1',
                'event_type' => 'calibration',
                'user_email' => 'test@example.com',
                'calibration_type' => 'Full Calibration',
            ],
        ];

        foreach ($testerEventLogs as $log) {
            $testerId = DB::table('testers')
                ->where('tester_name', $log['tester_name'])
                ->value('id');

            $eventTypeId = DB::table('event_types')
                ->where('name', $log['event_type'])
                ->value('id');

            $userId = DB::table('users')
                ->where('email', $log['user_email'])
                ->value('id');

            $maintenanceScheduleId = null;

            if ($log['event_type'] === 'maintenance') {
                $maintenanceScheduleId = DB::table('tester_maintenance_schedules')
                    ->join('tester_maintenance_procedures', 'tester_maintenance_schedules.maintenance_id', '=', 'tester_maintenance_procedures.id')
                    ->where('tester_maintenance_procedures.type', $log['maintenance_type'])
                    ->where('tester_maintenance_schedules.tester_id', $testerId)
                    ->value('tester_maintenance_schedules.id');
            }

            $calibrationScheduleId = null;

            if ($log['event_type'] === 'calibration') {
                $calibrationScheduleId = DB::table('tester_calibration_schedules')
                    ->join('tester_calibration_procedures', 'tester_calibration_schedules.calibration_id', '=', 'tester_calibration_procedures.id')
                    ->where('tester_calibration_procedures.type', $log['calibration_type'])
                    ->where('tester_calibration_schedules.tester_id', $testerId)
                    ->value('tester_calibration_schedules.id');
            }

            DB::table('tester_event_logs')->insert([
                'date' => $log['date'],
                'description' => $log['description'],

                'tester_id' => $testerId,
                'event_type' => $eventTypeId,
                'created_by_user_id' => $userId,

                'maintenance_schedule_id' => $maintenanceScheduleId,
                'calibration_schedule_id' => $calibrationScheduleId,

                'resolved_date' => null,
                'resolution_description' => null,
                'resolved_by_user_id' => null,
                'issue_status' => null,
            ]);
        }
    }
}

