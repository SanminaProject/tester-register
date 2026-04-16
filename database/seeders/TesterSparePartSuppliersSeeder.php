<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TesterSparePartSuppliersSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'supplier_name' => 'TAKAYA Electronics',
                'contact_person' => 'John Smith',
                'contact_email' => 'sales@takaya-electronics.com',
                'contact_phone' => '+1-408-555-0100',
                'address' => '123 Tech Street, San Jose, CA 95110, USA',
            ],
            [
                'supplier_name' => 'Sanmina Component Solutions',
                'contact_person' => 'Marie Huuhtanen',
                'contact_email' => 'procure@sanmina.com',
                'contact_phone' => '+358-3-883-1111',
                'address' => 'Haukiputaa, Finland',
            ],
            [
                'supplier_name' => 'Premier Farnell',
                'contact_person' => 'Technical Support',
                'contact_email' => 'support@farnell.com',
                'contact_phone' => '+1-800-5-Farnell',
                'address' => '2711 North Sepulveda Boulevard, Manhattan Beach, CA 90266, USA',
            ],
            [
                'supplier_name' => 'RS Components',
                'contact_person' => 'Sales Department',
                'contact_email' => 'sales@rs-components.com',
                'contact_phone' => '+44-1536-201234',
                'address' => 'Corby, Northamptonshire, NN17 9RS, UK',
            ],
            [
                'supplier_name' => 'Arrow Electronics',
                'contact_person' => 'Account Manager',
                'contact_email' => 'sales@arrow.com',
                'contact_phone' => '+1-800-833-6232',
                'address' => '9470 West Bryn Mawr Avenue, Rosemont, IL 60018, USA',
            ],
            [
                'supplier_name' => 'Heilind Electronics',
                'contact_person' => 'Distribution Sales',
                'contact_email' => 'sales@heilind.com',
                'contact_phone' => '+1-610-592-0900',
                'address' => '7 Great Valley Parkway, Malvern, PA 19355, USA',
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('tester_spare_part_suppliers')->updateOrInsert(
                ['supplier_name' => $supplier['supplier_name']],
                [
                    'contact_person' => $supplier['contact_person'],
                    'contact_email' => $supplier['contact_email'],
                    'contact_phone' => $supplier['contact_phone'],
                    'address' => $supplier['address'],
                    'created_at' => now(),
                ]
            );
        }
    }
}
