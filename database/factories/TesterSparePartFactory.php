<?php

namespace Database\Factories;

use App\Models\TesterSparePart;
use App\Models\Tester;
use App\Models\TesterSparePartSupplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class TesterSparePartFactory extends Factory
{
    protected $model = TesterSparePart::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'manufacturer_part_number' => fake()->optional()->bothify('PN-######'),
            'quantity_in_stock' => fake()->numberBetween(0, 100),
            'reorder_level' => fake()->numberBetween(5, 20),
            'last_order_date' => fake()->optional()->date(),
            'unit_price' => fake()->randomFloat(2, 1, 100),
            'description' => fake()->sentence(),
            'tester_id' => Tester::factory(),
            'supplier_id' => TesterSparePartSupplier::factory(),
        ];
    }
}
