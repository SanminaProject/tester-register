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
        Schema::create('testers', function (Blueprint $table) {
            $table->id();
            $table->string('tester_name', 100);
            $table->text('tester_description')->nullable();
            $table->string('id_number_by_customer', 50)->nullable();
            $table->string('operating_system', 50)->nullable();
            $table->string('tester_type', 50)->nullable();
            $table->string('product_family', 100)->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->date('implementation_date')->nullable();
            $table->text('additional_info')->nullable();

            // Foreign keys
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('owner_id')->nullable();
            $table->unsignedInteger('tester_status')->nullable();

            // Foreign key constraints
            $table->foreign('location_id')
                ->references('id')
                ->on('tester_and_fixture_locations')
                ->nullOnDelete();

            $table->foreign('owner_id')
                ->references('id')
                ->on('tester_customers')
                ->nullOnDelete();

            $table->foreign('tester_status')
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
        Schema::dropIfExists('testers');
    }
};
