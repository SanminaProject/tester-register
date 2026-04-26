<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Fixture;
use App\Models\Tester;
use App\Models\TesterAndFixtureLocation;
use App\Models\AssetStatus;

class FixtureFactory extends Factory
{
    protected $model = Fixture::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'manufacturer' => fake()->optional()->company(),
            'tester_id' => Tester::factory(),
            'location_id' => TesterAndFixtureLocation::factory(),
            'fixture_status' => AssetStatus::factory(),
        ];
    }
}