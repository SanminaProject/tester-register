<?php

namespace Database\Factories;

use App\Models\CalibrationSchedule;
use App\Models\Tester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CalibrationSchedule>
 */
class CalibrationScheduleFactory extends Factory
{
    protected $model = CalibrationSchedule::class;

    public function definition(): array
    {
        return [
            'tester_id' => Tester::factory(),
            'scheduled_date' => $this->faker->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'procedure' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
            'notes' => $this->faker->optional()->text(),
            'completed_date' => null,
            'performed_by' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'completed_date' => $this->faker->dateTime(),
                'performed_by' => $this->faker->name(),
            ];
        });
    }
}
