<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TesterCalibrationSchedulesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testerCalibrationSchedules = [
            [
                'last_calibration_date' => Carbon::now()->subMonths(6),
                'next_calibration_due' => Carbon::now()->addMonths(6),

                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE',
                'calibration_type' => 'Standard Calibration',
                'status' => 'Scheduled',
                'last_user_email' => 'test@example.com',
                'next_user_email' => 'test@example.com',
            ],
            [
                'tester_name' => 'DIT1',
                'calibration_type' => 'Full Calibration',
                'status' => 'Scheduled',
                'last_calibration_date' => Carbon::now()->subYear(),
                'next_calibration_due' => Carbon::now()->addYear(),
                'last_user_email' => 'test@example.com',
                'next_user_email' => 'test@example.com',
            ],
        ];

        foreach ($testerCalibrationSchedules as $schedule) {
            $testerId = DB::table('testers')
                ->where('tester_name', $schedule['tester_name'])
                ->value('id');

            $calibrationId = DB::table('tester_calibration_procedures')
                ->where('type', $schedule['calibration_type'])
                ->value('id');

            $statusId = DB::table('schedule_statuses')
                ->where('name', $schedule['status'])
                ->value('id');

            $lastUserId = DB::table('users')
                ->where('email', $schedule['last_user_email'])
                ->value('id');

            $nextUserId = DB::table('users')
                ->where('email', $schedule['next_user_email'])
                ->value('id');

            DB::table('tester_calibration_schedules')->insert([
                'schedule_created_date' => now(),
                'last_calibration_date' => $schedule['last_calibration_date'],
                'next_calibration_due' => $schedule['next_calibration_due'],

                'tester_id' => $testerId,
                'calibration_id' => $calibrationId,
                'calibration_status' => $statusId,
                'last_calibration_by_user_id' => $lastUserId,
                'next_calibration_by_user_id' => $nextUserId,
            ]);
        }
    }
}
