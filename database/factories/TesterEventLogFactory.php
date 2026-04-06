<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Tester;
use Carbon\Carbon;
use App\Models\TesterCalibrationSchedule;
use App\Models\TesterMaintenanceSchedule;
use App\Models\TesterEventLog;
use App\Models\EventType;

/**
 * @extends Factory<Model>
 */
class TesterEventLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => Carbon::now()->subDays(rand(0, 5)),
            'description' => fake()->sentence(),

            'tester_id' => Tester::factory(),
            'event_type' => EventType::query()->inRandomOrder()->value('id'),
            'created_by_user_id' => User::factory(),

            'maintenance_schedule_id' => null, 
            'calibration_schedule_id' => null,

            'resolved_date' => null,
            'resolution_description' => null,
            'resolved_by_user_id' => null,
            'issue_status' => null,
        ];
    }

    public function maintenance(): static
    {
        return $this->state(function (array $attributes) {

            $schedule = TesterMaintenanceSchedule::factory()->create([
                'tester_id' => $attributes['tester_id'] ?? Tester::factory(),
            ]);

            $eventTypeId = EventType::firstOrCreate([
                'name' => 'maintenance'
            ])->id;

            return [
                'event_type' => $eventTypeId,
                'tester_id' => $schedule->tester_id,
                'maintenance_schedule_id' => $schedule->id,
            ];
        });
    }

    public function calibration(): static
    {
        return $this->state(function (array $attributes) {

            $schedule = TesterCalibrationSchedule::factory()->create([
                'tester_id' => $attributes['tester_id'] ?? Tester::factory(),
            ]);

            $eventTypeId = EventType::firstOrCreate([
                'name' => 'calibration'
            ])->id;

            return [
                'event_type' => $eventTypeId,
                'tester_id' => $schedule->tester_id,
                'calibration_schedule_id' => $schedule->id,
            ];
        });
    }
}
