<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TesterCustomersSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testerCustomers = [
            ['name' => 'SANMINA'],
            ['name' => 'DNWP'],
            ['name' => 'DWNP'],
            ['name' => '9Solutions'],
            ['name' => 'Haukipudas'],
        ];

        foreach ($testerCustomers as $customer) {
            DB::table('tester_customers')->updateOrInsert(
                ['name' => $customer['name']],
                $customer
            );
        }
    }
}
