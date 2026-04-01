<?php

namespace Database\Factories;

use App\Models\TesterCustomer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TesterCustomer>
 */
class TesterCustomerFactory extends Factory
{
    protected $model = TesterCustomer::class;

    public function definition(): array
    {
        return [
            'company_name' => $this->faker->unique()->company(),
            'address' => $this->faker->address(),
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->companyEmail(),
        ];
    }
}
