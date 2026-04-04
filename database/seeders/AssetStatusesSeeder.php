<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AssetStatusesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('asset_statuses')->updateOrInsert(['name' => 'active']);
        DB::table('asset_statuses')->updateOrInsert(['name' => 'inactive']);
        DB::table('asset_statuses')->updateOrInsert(['name' => 'maintenance']);
    }
}
