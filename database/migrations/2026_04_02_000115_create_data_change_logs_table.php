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
        Schema::create('data_change_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('changed_at');
            $table->text('explanation');

            // foreign keys
            $table->unsignedInteger('tester_id')->nullable();
            $table->unsignedInteger('fixture_id')->nullable();
            $table->unsignedInteger('spare_part_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();

            // foreign key constraints
            $table->foreign('tester_id')->references('id')->on('testers')->nullOnDelete();
            $table->foreign('fixture_id')->references('id')->on('fixtures')->nullOnDelete();
            $table->foreign('spare_part_id')->references('id')->on('tester_spare_parts')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_change_logs');
    }
};
