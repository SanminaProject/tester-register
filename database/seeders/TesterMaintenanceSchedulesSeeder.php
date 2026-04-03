<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TesterMaintenanceSchedulesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testerMaintenanceSchedules = [
            [
                'last_maintenance_date' => Carbon::now()->subMonths(6),
                'next_maintenance_due' => Carbon::now()->addMonths(6),

                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE',
                'maintenance_type' => 'Standard Maintenance',
                'status' => 'Scheduled',
                'last_user' => 'Test User',
                'next_user' => 'Test User',
            ],
            [
                'tester_name' => 'DIT1',
                'maintenance_type' => 'Full Maintenance',
                'status' => 'Scheduled',
                'last_maintenance_date' => Carbon::now()->subYear(),
                'next_maintenance_due' => Carbon::now()->addYear(),
                'last_user' => 'Test User',
                'next_user' => 'Test User',
            ],
        ];

        foreach ($testerMaintenanceSchedules as $schedule) {
            $testerId = DB::table('testers')
                ->where('tester_name', $schedule['tester_name'])
                ->value('id');

            $maintenanceId = DB::table('tester_maintenance_procedures')
                ->where('type', $schedule['maintenance_type'])
                ->value('id');

            $statusId = DB::table('schedule_statuses')
                ->where('name', $schedule['status'])
                ->value('id');

            $lastUserId = DB::table('users')
                ->where('name', $schedule['last_user'])
                ->value('id');

            $nextUserId = DB::table('users')
                ->where('name', $schedule['next_user'])
                ->value('id');

            DB::table('tester_maintenance_schedules')->insert([
                'schedule_created_date' => now(),
                'last_maintenance_date' => $schedule['last_maintenance_date'],
                'next_maintenance_due' => $schedule['next_maintenance_due'],

                'tester_id' => $testerId,
                'maintenance_id' => $maintenanceId,
                'maintenance_status' => $statusId,
                'last_maintenance_by_user_id' => $lastUserId,
                'next_maintenance_by_user_id' => $nextUserId,
            ]);
        }
    }
}
