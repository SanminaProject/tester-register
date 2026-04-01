<?php

namespace Database\Factories;

use App\Models\SparePart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SparePart>
 */
class SparePartFactory extends Factory
{
    protected $model = SparePart::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' ' . $this->faker->word(),
            'part_number' => $this->faker->unique()->bothify('PART-####-??'),
            'quantity_in_stock' => $this->faker->numberBetween(0, 100),
            'unit_cost' => $this->faker->randomFloat(2, 1, 500),
            'supplier' => $this->faker->optional()->company(),
        ];
    }
}
