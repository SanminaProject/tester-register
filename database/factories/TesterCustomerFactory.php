<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TesterCustomer;

class TesterCustomerFactory extends Factory
{
    protected $model = TesterCustomer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
        ];
    }
}