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
        Schema::create('surgical_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_specialty_id')->constrained();
            $table->string('procedure_code')->unique();
            $table->string('patient_id'); // ID del paciente
            $table->string('surgeon_name');
            $table->string('surgeon_id')->nullable();
            $table->datetime('scheduled_at');
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgical_procedures');
    }
};
