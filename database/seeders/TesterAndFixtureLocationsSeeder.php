<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TesterAndFixtureLocationsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testerandFixtureLocations = [
            ['name' => 'PROTO'],
            ['name' => 'DNWP-CM'],
            ['name' => '9S'],
            ['name' => 'Tesu'],
        ];

        foreach ($testerandFixtureLocations as $location) {
            DB::table('tester_and_fixture_locations')->updateOrInsert(
                ['name' => $location['name']],
                $location
            );
        }
    }
}
