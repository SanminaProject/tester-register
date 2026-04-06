<?php

namespace Database\Factories;

use App\Models\TesterMaintenanceSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use App\Models\Tester;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TesterMaintenanceScheduleFactory extends Factory
{
    protected $model = TesterMaintenanceSchedule::class;

    public function definition(): array
    {
        $lastMaintenance = Carbon::now()->subMonths(rand(1, 12));

        return [
            'schedule_created_date' => now(),
            'last_maintenance_date' => $lastMaintenance,
            'next_maintenance_due' => (clone $lastMaintenance)->addMonths(6),

            // relationships
            'tester_id' => Tester::factory(),
            'maintenance_id' => DB::table('tester_maintenance_procedures')->inRandomOrder()->value('id'),
            'maintenance_status' => DB::table('schedule_statuses')->inRandomOrder()->value('id'),
            'last_maintenance_by_user_id' => User::factory(),
            'next_maintenance_by_user_id' => User::factory(),
        ];
    }
}
