<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_surgeries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Código único de cirugía');
            
            // Check List aplicable
            $table->foreignId('checklist_id')->constrained('surgical_checklists')->comment('Check list a utilizar');
            
            // Información de la cirugía
            $table->foreignId('hospital_id')->constrained('hospitals')->comment('Hospital donde se realizará');

            $table->foreignId('doctor_id')->constrained('doctors')->comment('Doctor que operará');
            $table->foreignId('hospital_modality_config_id')->nullable()->after('checklist_id')->constrained('hospital_modality_configs');
            

            
            
            $table->dateTime('surgery_datetime')->comment('Fecha y hora de la cirugía');
            $table->string('patient_name')->nullable()->comment('Nombre del paciente (opcional)');
            $table->text('surgery_notes')->nullable();
            
            // Estados del flujo
            $table->enum('status', [
                'scheduled',      
                'in_preparation', 
                'ready',          
                'in_surgery',     
                'completed',      
                'cancelled'       
            ])->default('scheduled');
            
            // Auditoría
            $table->foreignId('scheduled_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('code');
            $table->index('checklist_id');
            $table->index('hospital_id');
            $table->index('doctor_id');
            $table->index('surgery_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_surgeries');
    }
};