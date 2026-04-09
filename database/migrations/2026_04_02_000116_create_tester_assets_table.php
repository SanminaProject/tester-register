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
        Schema::create('tester_assets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('asset_no', 100);
            $table->unsignedInteger('tester_id');

            // Foreign key
            $table->foreign('tester_id')
                ->references('id')
                ->on('testers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tester_assets');
    }
};
