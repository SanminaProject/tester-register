<?php

namespace Database\Factories;

use App\Models\TesterMaintenanceProcedure;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProcedureIntervalUnit;

class TesterMaintenanceProcedureFactory extends Factory
{
    protected $model = TesterMaintenanceProcedure::class;

    public function definition(): array
    {
        return [
            'type' => 'Standard Maintenance',
            'interval_value' => 6,
            'description' => null,
            'interval_unit' => ProcedureIntervalUnit::factory(),
        ];
    }
}