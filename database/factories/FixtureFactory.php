<?php

namespace Database\Factories;

use App\Models\Fixture;
use App\Models\Tester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fixture>
 */
class FixtureFactory extends Factory
{
    protected $model = Fixture::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'serial_number' => $this->faker->unique()->bothify('FIX-????-####'),
            'tester_id' => Tester::factory(),
            'purchase_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
