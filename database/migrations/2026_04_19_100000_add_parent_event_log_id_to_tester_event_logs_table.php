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
        Schema::table('tester_event_logs', function (Blueprint $table) {
            $table->unsignedInteger('parent_event_log_id')->nullable()->after('calibration_schedule_id');
            $table->index('parent_event_log_id', 'idx_tester_event_logs_parent');
            $table->foreign('parent_event_log_id')
                ->references('id')
                ->on('tester_event_logs')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tester_event_logs', function (Blueprint $table) {
            $table->dropForeign(['parent_event_log_id']);
            $table->dropIndex('idx_tester_event_logs_parent');
            $table->dropColumn('parent_event_log_id');
        });
    }
};
