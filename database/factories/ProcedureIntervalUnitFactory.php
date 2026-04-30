<?php

namespace Database\Factories;

use App\Models\ProcedureIntervalUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcedureIntervalUnitFactory extends Factory
{
    protected $model = ProcedureIntervalUnit::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Days',
                'Weeks',
                'Months',
                'Years',
            ]),
        ];
    }
}