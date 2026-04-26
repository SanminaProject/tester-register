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
        Schema::create('user_spare_part_assignments', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('spare_part_id');

            $table->primary(['user_id', 'spare_part_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('spare_part_id')
                ->references('id')
                ->on('tester_spare_parts')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_spare_part_assignments');
    }
};