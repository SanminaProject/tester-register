<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('schedule_statuses')->updateOrInsert(
            ['name' => 'completed'],
            ['name' => 'completed']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('schedule_statuses')
            ->where('name', 'completed')
            ->delete();
    }
};
