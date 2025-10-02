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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();            $table->string('medical_record_number')->unique(); // Número de expediente
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            
            // Información de contacto
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            
            // Información médica básica
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            
            // Seguro médico
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            
            // Contacto de emergencia
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            
            // Estado
            $table->enum('status', ['active', 'inactive', 'deceased'])->default('active');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('medical_record_number');
            $table->index('status');
            $table->index(['last_name', 'first_name']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
