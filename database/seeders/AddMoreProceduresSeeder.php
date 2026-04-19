<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddMoreProceduresSeeder extends Seeder
{
    public function run()
    {
        $monthsUnit = DB::table('procedure_interval_units')->where('name', 'Months')->value('id');
        if (!$monthsUnit) {
            $monthsUnit = DB::table('procedure_interval_units')->insertGetId(['name' => 'Months']);
        }
        $daysUnit = DB::table('procedure_interval_units')->where('name', 'Days')->value('id');
        if (!$daysUnit) {
            $daysUnit = DB::table('procedure_interval_units')->insertGetId(['name' => 'Days']);
        }

        $monthsToAdd = [1, 3, 6, 12, 18, 24, 30, 36, 48, 60];
        
        foreach ($monthsToAdd as $m) {
            // Maintenance
            $existsM = DB::table('tester_maintenance_procedures')->where('interval_value', $m)->where('interval_unit', $monthsUnit)->exists();
            if (!$existsM) {
                DB::table('tester_maintenance_procedures')->insert([
                    'type' => $m . ' Months Maintenance',
                    'interval_value' => $m,
                    'interval_unit' => $monthsUnit,
                ]);
            }
            // Calibration
            $existsC = DB::table('tester_calibration_procedures')->where('interval_value', $m)->where('interval_unit', $monthsUnit)->exists();
            if (!$existsC) {
                DB::table('tester_calibration_procedures')->insert([
                    'type' => $m . ' Months Calibration',
                    'interval_value' => $m,
                    'interval_unit' => $monthsUnit,
                ]);
            }
        }
    }
}
