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
            'contact_person' => fake()->name(),
            'contact_email' => fake()->email(),
            'contact_phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
        ];
    }
}
