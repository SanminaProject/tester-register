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
        Schema::create('tester_event_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date'); // when the event occurred
            $table->text('description'); // detailed description
            $table->dateTime('resolved_date')->nullable();
            $table->text('resolution_description')->nullable();

            $table->unsignedBigInteger('tester_id');
            $table->unsignedBigInteger('event_type');
            $table->unsignedBigInteger('created_by_user_id');
            $table->unsignedBigInteger('resolved_by_user_id')->nullable();
            $table->unsignedBigInteger('issue_status')->nullable();
            $table->unsignedBigInteger('maintenance_schedule_id')->nullable();
            $table->unsignedBigInteger('calibration_schedule_id')->nullable();

            // Index for faster lookups by tester
            $table->index('tester_id', 'idx_tester_event_logs_tester');

            // Foreign key constraints
            $table->foreign('tester_id')->references('id')->on('testers')->cascadeOnDelete();
            $table->foreign('event_type')->references('id')->on('event_types');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('resolved_by_user_id')->references('id')->on('users');
            $table->foreign('issue_status')->references('id')->on('issue_statuses');
            $table->foreign('maintenance_schedule_id')->references('id')->on('tester_maintenance_schedules');
            $table->foreign('calibration_schedule_id')->references('id')->on('tester_calibration_schedules');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tester_event_logs');
    }
};
