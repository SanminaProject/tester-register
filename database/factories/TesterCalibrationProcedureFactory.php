<?php

namespace Database\Factories;

use App\Models\TesterCalibrationProcedure;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProcedureIntervalUnit;

class TesterCalibrationProcedureFactory extends Factory
{
    protected $model = TesterCalibrationProcedure::class;

    public function definition(): array
    {
        return [
            'type' => 'Standard Calibration',
            'interval_value' => 6,
            'description' => null,
            'interval_unit' => ProcedureIntervalUnit::factory(),
        ];
    }
}