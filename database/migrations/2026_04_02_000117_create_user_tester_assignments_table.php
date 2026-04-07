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
        Schema::create('user_tester_assignments', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('tester_id');

            $table->primary(['user_id', 'tester_id']);

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('tester_id')
                ->references('id')->on('testers')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tester_assignments');
    }
};
