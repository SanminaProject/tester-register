<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // in this order
            RoleSeeder::class,
            AssetStatusesSeeder::class,
            ScheduleStatusesSeeder::class,
            TesterCustomersSeeder::class,
            TesterAndFixtureLocationsSeeder::class,
            TesterSeeder::class,
            FixtureSeeder::class,
            TesterSparePartSuppliersSeeder::class,
            TesterSparePartsSeeder::class,
            ProcedureIntervalUnitsSeeder::class,
            TesterMaintenanceProceduresSeeder::class,
            TesterCalibrationProceduresSeeder::class,
            TesterMaintenanceSchedulesSeeder::class,
            TesterCalibrationSchedulesSeeder::class,
            EventTypesSeeder::class,
            IssueStatusesSeeder::class,
            TesterEventLogsSeeder::class,
            DataChangeLogsSeeder::class,
            UserTesterAssignmentsSeeder::class,
        ]);
    }
}
