<?php

namespace Database\Factories;

use App\Models\TesterCalibrationSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Tester;
use Carbon\Carbon;

class TesterCalibrationScheduleFactory extends Factory
{
    protected $model = TesterCalibrationSchedule::class;

    public function definition(): array
    {
        $lastCalibration = Carbon::now()->subMonths(rand(1, 12));

        return [
            'schedule_created_date' => now(),
            'last_calibration_date' => $lastCalibration,
            'next_calibration_due' => (clone $lastCalibration)->addMonths(6),

            // relationships
            'tester_id' => Tester::factory(),
            'calibration_id' => DB::table('tester_calibration_procedures')->inRandomOrder()->value('id'),
            'calibration_status' => DB::table('schedule_statuses')->inRandomOrder()->value('id'),
            'last_calibration_by_user_id' => User::factory(),
            'next_calibration_by_user_id' => User::factory(),
        ];
    }
}
