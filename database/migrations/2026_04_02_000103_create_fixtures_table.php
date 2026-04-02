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
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();

            // index for faster lookups of fixtures by tester
            $table->index('tester_id', 'idx_fixtures_tester');

            // Foreign keys
            $table->foreign('tester_id')
                  ->references('id')
                  ->on('testers')
                  ->cascadeOnDelete();

            $table->foreign('location_id')
                  ->references('id')
                  ->on('tester_and_fixture_locations')
                  ->nullOnDelete();

            $table->foreign('fixture_status')
                  ->references('id')
                  ->on('asset_statuses')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
