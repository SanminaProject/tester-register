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
            $table->foreignId('tester_id')->constrained()->onDelete('cascade');
            $table->date('scheduled_date');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->string('procedure');
            $table->text('notes')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('performed_by')->nullable();
            $table->timestamps();
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
