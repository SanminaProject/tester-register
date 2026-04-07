<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProcedureIntervalUnitsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $procedureIntervalUnits = [
            ['name' => 'days'],
            ['name' => 'weeks'],
            ['name' => 'months'],
            ['name' => 'years'],
        ];

        foreach ($procedureIntervalUnits as $unit) {
            DB::table('procedure_interval_units')->updateOrInsert(
                ['name' => $unit['name']],
                $unit
            );
        }
    }
}

