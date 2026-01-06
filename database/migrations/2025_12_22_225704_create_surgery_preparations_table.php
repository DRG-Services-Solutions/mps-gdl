<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgery_preparations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgery_id')->constrained('scheduled_surgeries')->onDelete('cascade');

            // Pre-Armado seleccionado
            $table->foreignId('pre_assembled_package_id')->nullable()->constrained('pre_assembled_packages')->comment('Paquete pre-armado utilizado');
            $table->foreignId('scheduled_surgery_id')->constrained('scheduled_surgeries')->comment('Cirugía programada asociada');
            
            // Estados de preparación
            $table->enum('status', [
                'pending',        // Pendiente
                'comparing',      // Comparando check list vs pre-armado
                'picking',        // Surtiendo faltantes
                'verifying',      // Verificando
                'completed'       // Completado
            ])->default('pending');
            
            // Fechas
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            
            // Usuarios
            $table->foreignId('prepared_by')->nullable()->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('pre_assembled_package_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgery_preparations');
    }
};