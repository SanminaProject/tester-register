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
        Schema::create('tester_calibration_schedules', function (Blueprint $table) {
            $table->id();
            $table->dateTime('schedule_created_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('last_calibration_date')->nullable();
            $table->dateTime('next_calibration_due')->nullable();

            $table->unsignedBigInteger('tester_id');
            $table->unsignedBigInteger('calibration_id');
            $table->unsignedBigInteger('calibration_status')->nullable();
            $table->unsignedBigInteger('last_calibration_by_user_id')->nullable();
            $table->unsignedBigInteger('next_calibration_by_user_id')->nullable();

            // Index for faster lookups
            $table->index('tester_id', 'idx_tester_calibration_schedules_tester');

            // Foreign key constraints
            $table->foreign('tester_id')->references('id')->on('testers')->cascadeOnDelete(); // TODO: update onDelete and onCascade behaviour if needed
            $table->foreign('calibration_id')->references('id')->on('tester_calibration_procedures')->cascadeOnDelete();
            $table->foreign('calibration_status')->references('id')->on('schedule_statuses')->nullOnDelete();
            $table->foreign('last_calibration_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('next_calibration_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tester_calibration_schedules');
    }
};
