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
        Schema::create('tester_maintenance_procedures', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 100);
            $table->integer('interval_value');
            $table->text('description')->nullable();
            $table->integer('interval_unit');

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
        Schema::dropIfExists('tester_maintenance_procedures');
    }
};
