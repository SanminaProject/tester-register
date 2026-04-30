<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TesterAsset;
use App\Models\Tester;

class TesterAssetFactory extends Factory
{
    protected $model = TesterAsset::class;

    public function definition(): array
    {
        return [
            'tester_id' => Tester::factory(),
            'asset_no' => fake()->unique()->bothify('ASSET-#####'),
        ];
    }
}
