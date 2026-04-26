<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AssetStatus;

class AssetStatusFactory extends Factory
{
    protected $model = AssetStatus::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['active', 'inactive', 'maintenance', 'retired', 'in-stock']),
        ];
    }
}
