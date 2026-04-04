<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TesterCalibrationProceduresSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testerCalibrationProcedures = [
            [
                'type' => 'Standard Calibration',
                'interval_value' => '6',
                'description' => null,
                'interval_unit' => 'months',
            ],
            [
                'type' => 'Full Calibration',
                'interval_value' => '12',
                'description' => null,
                'interval_unit' => 'months',
            ],
            [
                'type' => 'Short Calibration',
                'interval_value' => '2',
                'description' => null,
                'interval_unit' => 'weeks',
            ],
        ];

        foreach ($testerCalibrationProcedures as $testerCalibrationProcedure) {
            $intervalUnitId = DB::table('procedure_interval_units')
                ->where('name', $testerCalibrationProcedure['interval_unit'])
                ->value('id');

            DB::table('tester_calibration_procedures')->updateOrInsert(
                ['type' => $testerCalibrationProcedure['type']],
                [
                    'interval_value' => $testerCalibrationProcedure['interval_value'],
                    'description' => $testerCalibrationProcedure['description'],
                    'interval_unit' => $intervalUnitId,
                ]
            );
        }
    }
}
