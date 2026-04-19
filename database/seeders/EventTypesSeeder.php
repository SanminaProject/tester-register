<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EventTypesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $eventTypes = [
            ['name' => 'problem'],
            ['name' => 'solution'],
            ['name' => 'issue'],
            ['name' => 'maintenance'],
            ['name' => 'calibration'],
            ['name' => 'software_update'],
            ['name' => 'hardware_change'],
        ];

        foreach ($eventTypes as $eventType) {
            DB::table('event_types')->updateOrInsert(
                ['name' => $eventType['name']],
                $eventType
            );
        }
    }
}

