<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TesterAndFixtureLocation;

class TesterAndFixtureLocationFactory extends Factory
{
    protected $model = TesterAndFixtureLocation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'description' => fake()->optional()->sentence(),
            'address' => fake()->optional()->address(),
        ];
    }
}
