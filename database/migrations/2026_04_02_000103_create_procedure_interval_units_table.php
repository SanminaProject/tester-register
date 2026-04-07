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
        Schema::create('procedure_interval_units', function (Blueprint $table) {
            $table->increments('id'); // auto-incrementing primary key
            $table->string('name', 50)->unique()->notNullable(); // unit of time (Days, Weeks, Months or Years)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_interval_units');
    }
};
