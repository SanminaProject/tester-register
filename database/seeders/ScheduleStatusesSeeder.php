<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ScheduleStatusesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $scheduleStatuses = [
            ['name' => 'scheduled'],
            ['name' => 'overdue'],
            ['name' => 'completed'],
        ];

        foreach ($scheduleStatuses as $status) {
            DB::table('schedule_statuses')->updateOrInsert(
                ['name' => $status['name']],
                $status
            );
        }
    }
}
