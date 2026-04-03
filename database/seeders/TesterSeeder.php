<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TesterSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testers = [
            [
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE',
                'tester_description' => 'FLYING PROBE TESTER',
                'id_number_by_customer' => 'SN 9708037',
                'operating_system' => 'XP/APT4.13',
                'tester_type' => 'FLYING PRO',
                'product_family' => 'ALL',
                'manufacturer' => 'TAKAYA',
                'implementation_date' => '2003-01-01',
                
                'status' => 'Active',
                'location' => 'PROTO',
                'owner' => 'SANMINA',
            ],
            [
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE #2',
                'tester_description' => 'FLYING PROBE TESTER',
                'id_number_by_customer' => 'S/N 96100233',
                'operating_system' => 'XP/APT4.13',
                'tester_type' => 'FLYING PRO',
                'product_family' => 'ALL',
                'manufacturer' => 'TAKAYA',
                'implementation_date' => '2014-12-27',

                'status' => 'Active',
                'location' => 'PROTO',
                'owner' => 'SANMINA',
            ],
            [
                'tester_name' => 'DIT1',
                'tester_description' => 'Dyna2 Fan/MotherBoard Tester',
                'id_number_by_customer' => null,
                'operating_system' => 'SWIFT',
                'tester_type' => 'FUNC',
                'product_family' => 'DNWP-DYNA',
                'manufacturer' => 'Sanmina Haukipudas',
                'implementation_date' => '2009-10-20',
                
                'status' => 'Active',
                'location' => 'PROTO',
                'owner' => 'SANMINA',
            ],
            [
                'tester_name' => 'DST1',
                'tester_description' => 'Dyna2 Trunk/Tributary Tester',
                'id_number_by_customer' => null,
                'operating_system' => 'SWIFT',
                'tester_type' => 'FUNC',
                'product_family' => 'DNWP-DYNA',
                'manufacturer' => 'Haukipudas S-SCI',
                'implementation_date' => '2009-10-20',

                'status' => 'Active',
                'location' => 'DNWP-CM',
                'owner' => 'DWNP',
            ],
            [
                'tester_name' => '9S_SWDL1',
                'tester_description' => '9Solutions SWDL tester',
                'id_number_by_customer' => null,
                'operating_system' => null,
                'tester_type' => 'SWDOWNLOAD',
                'product_family' => '9S',
                'manufacturer' => '9Solutions',
                'implementation_date' => '2017-06-28',

                'status' => 'Active',
                'location' => '9S',
                'owner' => '9Solutions',
            ],
            [
                'tester_name' => '9S_SWDL2',
                'tester_description' => '9Solutions SWDL tester',
                'id_number_by_customer' => null,
                'operating_system' => null,
                'tester_type' => 'SWDOWNLOAD',
                'product_family' => '9S',
                'manufacturer' => '9Solutions',
                'implementation_date' => '2018-10-05',

                'status' => 'Active',
                'location' => '9S',
                'owner' => '9Solutions',
            ],
            [
                'tester_name' => 'EST2',
                'tester_description' => 'Electrical Safety Tester',
                'id_number_by_customer' => null,
                'operating_system' => 'Win7',
                'tester_type' => 'HIPOT',
                'product_family' => 'ALL',
                'manufacturer' => 'Haukipudas',
                'implementation_date' => '2019-12-02',

                'status' => 'Active',
                'location' => 'Tesu',
                'owner' => 'SANMINA',
            ],
        ];

        foreach ($testers as $tester) {

            $statusId = DB::table('asset_statuses')
                ->where('name', $tester['status'])
                ->value('id');

            $locationId = DB::table('tester_and_fixture_locations')
                ->where('name', $tester['location'])
                ->value('id');

            $ownerId = DB::table('tester_customers')
                ->where('name', $tester['owner'])
                ->value('id');

            DB::table('testers')->updateOrInsert(
                ['tester_name' => $tester['tester_name']],
                [
                    'tester_description' => $tester['tester_description'],
                    'id_number_by_customer' => $tester['id_number_by_customer'],
                    'operating_system' => $tester['operating_system'],
                    'tester_type' => $tester['tester_type'],
                    'product_family' => $tester['product_family'],
                    'manufacturer' => $tester['manufacturer'],
                    'implementation_date' => $tester['implementation_date'],

                    'tester_status' => $statusId,
                    'location_id' => $locationId,
                    'owner_id' => $ownerId,
                ]
            );
        }
    }
}
