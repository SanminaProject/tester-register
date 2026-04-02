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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tester_id')->constrained('testers')->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->enum('status', ['scheduled', 'completed', 'overdue'])->default('scheduled');
            $table->text('procedure');
            $table->date('completed_date')->nullable();
            $table->string('performed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tester_id', 'status']);
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
