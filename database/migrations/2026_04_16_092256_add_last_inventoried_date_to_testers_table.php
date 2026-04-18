<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('testers', function (Blueprint $table) {
            $table->dateTime('last_inventoried_date')->nullable()->after('implementation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testers', function (Blueprint $table) {
            $table->dropColumn('last_inventoried_date');
        });
    }
};
