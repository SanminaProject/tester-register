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
        Schema::create('tester_calibration_procedures', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
            $table->unsignedInteger('interval_value');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('interval_unit');

            // Foreign key constraint
            $table->foreign('interval_unit')
                  ->references('id')
                  ->on('procedure_interval_units')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tester_calibration_procedures');
    }
};
