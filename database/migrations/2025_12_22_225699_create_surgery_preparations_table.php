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

            //Paquete asociado con la preparacion
            $table->foreignId('pre_assembled_package_id')->constrained('pre_assembled_packages')->onDelete('cascade')->comment('Paquete pre-armado asociado');

            // Relación correcta con Cirugías (Usando el nombre que ya tiene el índice en tu BD)
            $table->foreignId('scheduled_surgery_id')
                  ->constrained('scheduled_surgeries')
                  ->onDelete('cascade')
                  ->comment('Cirugía programada asociada');

           
            
            // Estados de preparación
            $table->enum('status', [
                'pending',   
                'comparing', 
                'picking',   
                'verifying', 
                'completed'  
            ])->default('pending');
            
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            
            // Usuarios responsable y verificador
            $table->foreignId('prepared_by')->nullable()->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices para optimización de búsquedas
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgery_preparations');
    }
};