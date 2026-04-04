<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TesterMaintenanceProceduresSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testerMaintenanceProcedures = [
            [
                'type' => 'Standard Maintenance',
                'interval_value' => '6',
                'description' => null,
                'interval_unit' => 'months',
            ],
            [
                'type' => 'Full Maintenance',
                'interval_value' => '12',
                'description' => null,
                'interval_unit' => 'months',
            ],
            [
                'type' => 'Short Maintenance',
                'interval_value' => '2',
                'description' => null,
                'interval_unit' => 'weeks',
            ],
        ];

        foreach ($testerMaintenanceProcedures as $testerMaintenanceProcedure) {
            $intervalUnitId = DB::table('procedure_interval_units')
                ->where('name', $testerMaintenanceProcedure['interval_unit'])
                ->value('id');

            DB::table('tester_maintenance_procedures')->updateOrInsert(
                ['type' => $testerMaintenanceProcedure['type']],
                [
                    'interval_value' => $testerMaintenanceProcedure['interval_value'],
                    'description' => $testerMaintenanceProcedure['description'],
                    'interval_unit' => $intervalUnitId,
                ]
            );
        }
    }
}
