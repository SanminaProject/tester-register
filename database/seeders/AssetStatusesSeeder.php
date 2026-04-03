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
        DB::table('asset_statuses')->updateOrInsert(['name' => 'Active']);
        DB::table('asset_statuses')->updateOrInsert(['name' => 'Inactive']);
        DB::table('asset_statuses')->updateOrInsert(['name' => 'Maintenance']);
    }
}
