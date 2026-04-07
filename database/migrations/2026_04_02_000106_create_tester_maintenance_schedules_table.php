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
        Schema::create('tester_maintenance_schedules', function (Blueprint $table) {
            $table->integer('id', true);
            $table->dateTime('schedule_created_date')->useCurrent();
            $table->dateTime('last_maintenance_date')->nullable();
            $table->dateTime('next_maintenance_due')->nullable();

            $table->unsignedInteger('tester_id');
            $table->unsignedInteger('maintenance_id');
            $table->unsignedInteger('maintenance_status')->nullable();
            $table->unsignedInteger('last_maintenance_by_user_id')->nullable();
            $table->unsignedInteger('next_maintenance_by_user_id')->nullable();

            // Index for faster lookups
            $table->index('tester_id', 'idx_tester_maintenance_schedules_tester');

            // Foreign key constraints
            $table->foreign('tester_id')->references('id')->on('testers')->cascadeOnDelete();
            $table->foreign('maintenance_id')->references('id')->on('tester_maintenance_procedures')->cascadeOnDelete();
            $table->foreign('maintenance_status')->references('id')->on('schedule_statuses')->nullOnDelete();
            $table->foreign('last_maintenance_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('next_maintenance_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tester_maintenance_schedules');
    }
};
