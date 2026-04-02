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
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tester_id')->constrained('testers')->cascadeOnDelete();
            $table->enum('type', ['maintenance', 'calibration', 'issue', 'repair', 'other']);
            $table->dateTime('event_date');
            $table->text('description');
            $table->string('performed_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tester_id', 'type']);
            $table->index('event_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_logs');
    }
};
