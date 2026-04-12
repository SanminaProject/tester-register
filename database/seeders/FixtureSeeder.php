<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class FixtureSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $fixtures = [
            [
                'name' => 'Fixture A',
                'description' => 'Test fixture for board A',
                'manufacturer' => 'TAKAYA',
                'tester' => 'TAKAYA FLYING PROBE APT 8400CE',
                'location' => 'PROTO',
                'status' => 'inactive',
            ],
            [
                'name' => 'Fixture B',
                'description' => 'Secondary fixture',
                'manufacturer' => 'Sanmina',
                'tester' => 'DIT1',
                'location' => 'DNWP-CM',
                'status' => 'active',
            ],
        ];

        foreach ($fixtures as $fixture) {

            $statusId = DB::table('asset_statuses')
                ->where('name', $fixture['status'])
                ->value('id');

            $locationId = DB::table('tester_and_fixture_locations')
                ->where('name', $fixture['location'])
                ->value('id');

            $testerId = DB::table('testers')
                ->where('name', $fixture['tester'])
                ->value('id');

            DB::table('fixtures')->updateOrInsert(
                ['name' => $fixture['name']],
                [
                    'description' => $fixture['description'],
                    'manufacturer' => $fixture['manufacturer'],
                    'created_at' => now(),
                    'tester_id' => $testerId,
                    'location_id' => $locationId,
                    'fixture_status' => $statusId,
                ]
            );
        }
    }
}
