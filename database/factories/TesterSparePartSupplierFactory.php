<?php

namespace Database\Factories;

use App\Models\TesterSparePartSupplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class TesterSparePartSupplierFactory extends Factory
{
    protected $model = TesterSparePartSupplier::class;

    public function definition(): array
    {
        return [
            'supplier_name' => fake()->company(),
            'contact_person' => fake()->optional()->name(),
            'contact_email' => fake()->optional()->email(),
            'contact_phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
        ];
    }
}
