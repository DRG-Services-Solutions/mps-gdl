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
        Schema::create('surgeries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
                  ->constrained('patients')
                  ->cascadeOnDelete();
            
            // Información de la cirugía
            $table->string('surgery_code')->unique(); // Código interno de la cirugía
            $table->string('procedure_name');
            $table->text('procedure_description')->nullable();
            
            // Clasificación
            $table->foreignId('specialty_id')
                  ->nullable()
                  ->constrained('medical_specialties')
                  ->nullOnDelete();
            
            $table->enum('urgency', ['elective', 'urgent', 'emergency'])->default('elective');
            $table->enum('complexity', ['minor', 'moderate', 'major'])->default('moderate');
            
            // Programación
            $table->timestamp('scheduled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('estimated_duration_minutes')->nullable(); // Duración estimada
            $table->integer('actual_duration_minutes')->nullable(); // Duración real
            
            // Ubicación
            $table->foreignId('operating_room_id')
                  ->nullable()
                  ->constrained('storage_locations')
                  ->nullOnDelete();
            
            // Equipo médico
            $table->foreignId('surgeon_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->foreignId('anesthesiologist_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->json('surgical_team')->nullable(); // Array de IDs del equipo
            
            // Información clínica
            $table->text('preoperative_diagnosis')->nullable();
            $table->text('postoperative_diagnosis')->nullable();
            $table->text('surgical_notes')->nullable();
            $table->text('complications')->nullable();
            
            // Estado de la cirugía
            $table->enum('status', [
                'scheduled',    // Programada
                'confirmed',    // Confirmada
                'in_progress',  // En curso
                'completed',    // Completada
                'cancelled',    // Cancelada
                'postponed'     // Pospuesta
            ])->default('scheduled');
            
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('surgery_code');
            $table->index(['patient_id', 'scheduled_at']);
            $table->index('scheduled_at');
            $table->index('status');
            $table->index(['operating_room_id', 'scheduled_at']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgeries');
    }
};
