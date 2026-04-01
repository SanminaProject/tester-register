<?php

namespace Database\Factories;

use App\Models\Tester;
use App\Models\TesterCustomer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tester>
 */
class TesterFactory extends Factory
{
    protected $model = Tester::class;

    public function definition(): array
    {
        return [
            'model' => $this->faker->words(2, true),
            'serial_number' => $this->faker->unique()->bothify('SN-????-####'),
            'customer_id' => TesterCustomer::factory(),
            'purchase_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'maintenance']),
            'location' => $this->faker->word(),
        ];
    }
}
