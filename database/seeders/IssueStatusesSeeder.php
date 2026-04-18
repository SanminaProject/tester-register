<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class IssueStatusesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $issueStatuses = [
            ['name' => 'Active'],
            ['name' => 'spare'],
            ['name' => 'stored'],
            ['name' => 'transferred'],
            ['name' => 'disassembled'],
            ['name' => 'scrapped'],
        ];

        foreach ($issueStatuses as $status) {
            DB::table('issue_statuses')->updateOrInsert(
                ['name' => $status['name']],
                $status
            );
        }
    }
}

