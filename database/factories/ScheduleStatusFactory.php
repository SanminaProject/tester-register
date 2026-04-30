<?php

namespace Database\Factories;

use App\Models\ScheduleStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleStatusFactory extends Factory
{
    protected $model = ScheduleStatus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['pending', 'completed', 'overdue']),
        ];
    }
}