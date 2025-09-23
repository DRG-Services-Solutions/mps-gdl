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
        Schema::create('sterilization_processes', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code')->unique();
            $table->enum('sterilization_method', ['autoclave', 'ethylene_oxide', 'plasma', 'steam']);
            $table->datetime('started_at');
            $table->datetime('completed_at')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'failed', 'cancelled'])->default('in_progress');
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('pressure', 5, 2)->nullable();
            $table->integer('cycle_time')->nullable(); // en minutos
            $table->string('operator_name');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sterilization_processes');
    }
};
