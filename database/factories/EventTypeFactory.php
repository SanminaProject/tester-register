<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EventType;

class EventTypeFactory extends Factory
{
    protected $model = EventType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'issue',
                'maintenance',
                'calibration',
                'software_update',
                'hardware_change',
            ]),
        ];
    }

    public function maintenance(): static
    {
        return $this->state(fn () => ['name' => 'maintenance']);
    }

    public function calibration(): static
    {
        return $this->state(fn () => ['name' => 'calibration']);
    }

    public function issue(): static
    {
        return $this->state(fn () => ['name' => 'issue']);
    }
}
