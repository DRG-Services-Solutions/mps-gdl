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
        Schema::create('storage_locations', function (Blueprint $table) {
            $table->id();
             // Identificación
            $table->string('code')->unique();
            $table->string('name'); 
            
            // Tipo de ubicación
            $table->enum('type', [
                'warehouse',        // Almacén general
                'operating_room',   // Quirófano
                'sterilization',    // Área de esterilización
                'pharmacy',         // Farmacia
                'storage',          // Bodega
                'external'          // Ubicación externa (préstamo)
            ])->default('warehouse');
            
            // Jerarquía (ubicación padre)
            $table->foreignId('parent_location_id')
                  ->nullable()
                  ->constrained('storage_locations')
                  ->nullOnDelete();
            
            // Ubicación física detallada
            $table->string('building')->nullable(); // Edificio
            $table->string('floor')->nullable();    // Piso
            $table->string('room')->nullable();     // Sala/Cuarto
            $table->string('area')->nullable();     // Área específica
            $table->string('shelf')->nullable();    // Estante
            
            // Información adicional
            $table->text('description')->nullable();
            $table->boolean('requires_authorization')->default(false); // Requiere autorización especial
            $table->boolean('is_active')->default(true);
            
            // Responsable de la ubicación
            $table->foreignId('responsible_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->softDeletes();
            
            // Índices
            $table->index('code');
            $table->index('type');
            $table->index(['is_active', 'type']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_locations');
    }
};
