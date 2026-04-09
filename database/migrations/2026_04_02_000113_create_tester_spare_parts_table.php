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
        Schema::create('tester_spare_parts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('manufacturer_part_number')->nullable();
            $table->unsignedInteger('quantity_in_stock')->default(0);
            $table->unsignedInteger('reorder_level');
            $table->date('last_order_date')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('tester_id', 'idx_tester_spare_parts_tester');

            $table->unsignedInteger('tester_id');
            $table->unsignedInteger('supplier_id')->nullable();

            // Foreign key constraints
            $table->foreign('tester_id')
                ->references('id')
                ->on('testers');

            $table->foreign('supplier_id')
                ->references('id')
                ->on('tester_spare_part_suppliers')
                ->noActionOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tester_spare_parts');
    }
};
