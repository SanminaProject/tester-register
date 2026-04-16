<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TesterSparePartsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $sparePartsData = [
            [
                'name' => 'PCB Assembly Board',
                'manufacturer_part_number' => 'PCB-001-REV-A',
                'quantity_in_stock' => 5,
                'reorder_level' => 2,
                'last_order_date' => '2026-03-15',
                'unit_price' => 150.00,
                'description' => 'Main PCB assembly board for flying probe tester',
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE',
                'supplier_name' => 'TAKAYA Electronics',
            ],
            [
                'name' => 'Calibration Standard',
                'manufacturer_part_number' => 'CAL-STD-02',
                'quantity_in_stock' => 3,
                'reorder_level' => 1,
                'last_order_date' => '2026-02-28',
                'unit_price' => 2500.00,
                'description' => 'Precision calibration standard for accuracy verification',
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE',
                'supplier_name' => 'Premier Farnell',
            ],
            [
                'name' => 'Probe Tip Set',
                'manufacturer_part_number' => 'PROBE-TIP-100',
                'quantity_in_stock' => 12,
                'reorder_level' => 5,
                'last_order_date' => '2026-04-01',
                'unit_price' => 80.00,
                'description' => 'Replacement probe tips (set of 10)',
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE',
                'supplier_name' => 'Arrow Electronics',
            ],
            [
                'name' => 'Vacuum Pump Cartridge',
                'manufacturer_part_number' => 'PUMP-CART-50',
                'quantity_in_stock' => 2,
                'reorder_level' => 1,
                'last_order_date' => '2026-01-20',
                'unit_price' => 450.00,
                'description' => 'Vacuum pump replacement cartridge',
                'tester_name' => 'TAKAYA FLYING PROBE APT 8400CE #2',
                'supplier_name' => 'RS Components',
            ],
            [
                'name' => 'Test Head Motor',
                'manufacturer_part_number' => 'MOTOR-TH-200',
                'quantity_in_stock' => 1,
                'reorder_level' => 1,
                'last_order_date' => '2025-12-10',
                'unit_price' => 3200.00,
                'description' => 'Stepper motor for test head positioning',
                'tester_name' => 'DIT1',
                'supplier_name' => 'Sanmina Component Solutions',
            ],
        ];

        foreach ($sparePartsData as $sparePart) {
            $testerId = DB::table('testers')
                ->where('name', $sparePart['tester_name'])
                ->value('id');

            $supplierId = DB::table('tester_spare_part_suppliers')
                ->where('supplier_name', $sparePart['supplier_name'])
                ->value('id');

            if ($testerId) {
                DB::table('tester_spare_parts')->updateOrInsert(
                    ['name' => $sparePart['name'], 'tester_id' => $testerId],
                    [
                        'manufacturer_part_number' => $sparePart['manufacturer_part_number'],
                        'quantity_in_stock' => $sparePart['quantity_in_stock'],
                        'reorder_level' => $sparePart['reorder_level'],
                        'last_order_date' => $sparePart['last_order_date'],
                        'unit_price' => $sparePart['unit_price'],
                        'description' => $sparePart['description'],
                        'supplier_id' => $supplierId,
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
