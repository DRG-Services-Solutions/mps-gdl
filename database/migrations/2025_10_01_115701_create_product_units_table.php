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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            // Relación con el producto del catálogo
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            
            // ==========================================================
            // IDENTIFICADOR ÚNICO (EPC RFID O NÚMERO DE SERIE)
            // ==========================================================
            // Este es el identificador ÚNICO de esta unidad física
            $table->string('unique_identifier', 100)->unique();
            $table->enum('identifier_type', ['rfid', 'serial']);
            
            // ==========================================================
            // UBICACIÓN Y ESTADO ACTUAL
            // ==========================================================
            $table->foreignId('current_location_id')
                  ->nullable()
                  ->constrained('storage_locations')
                  ->nullOnDelete();
            
            $table->enum('status', [
                'available',        // Disponible para uso
                'in_use',          // En uso (asignado a cirugía/paciente)
                'in_sterilization', // En proceso de esterilización
                'maintenance',      // En mantenimiento/reparación
                'damaged',          // Dañado
                'discarded',        // Descartado/desechado
                'reserved'          // Reservado
            ])->default('available');
            
            // ==========================================================
            // INFORMACIÓN DE LOTE Y CADUCIDAD (Para consumibles)
            // ==========================================================
            $table->string('lot_number')->nullable();
            $table->date('expiration_date')->nullable();
            $table->date('received_date'); // Fecha de entrada al inventario
            
            // ==========================================================
            // INFORMACIÓN DE ESTERILIZACIÓN (Para instrumentales)
            // ==========================================================
            $table->integer('sterilization_cycles')->default(0);
            $table->date('last_sterilization_date')->nullable();
            $table->date('next_sterilization_due')->nullable();
            $table->integer('max_sterilization_cycles')->nullable(); // Ciclos máximos permitidos
            
            // ==========================================================
            // ASIGNACIÓN ACTUAL
            // ==========================================================
            //$table->foreignId('assigned_to_surgery_id')
            //      ->nullable()
            //      ->nullable()
            //      ->constrained('patients')
            //      ->nullOnDelete();
            
            $table->timestamp('assigned_at')->nullable();
            
            // ==========================================================
            // INFORMACIÓN DE COSTO (Para esta unidad específica)
            // ==========================================================
            $table->decimal('acquisition_cost', 10, 2)->nullable();
            
            // ==========================================================
            // NOTAS Y OBSERVACIONES
            // ==========================================================
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // ==========================================================
            // ÍNDICES PARA OPTIMIZACIÓN
            // ==========================================================
            $table->index('unique_identifier');
            $table->index(['product_id', 'status']);
            $table->index(['current_location_id', 'status']);
            $table->index('expiration_date');
            $table->index('next_sterilization_due');
            $table->index(['identifier_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
