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
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Identificadores únicos (solo uno será usado según el tipo de producto)
            $table->string('epc')->unique()->nullable()->comment('Código EPC para RFID');
            $table->string('serial_number')->unique()->nullable()->comment('Número de serie para instrumentales');
            
            // Información de lote y caducidad
            $table->string('batch_number')->nullable()->comment('Número de lote del fabricante');
            $table->date('expiration_date')->nullable()->comment('Fecha de caducidad');
            $table->date('manufacture_date')->nullable()->comment('Fecha de fabricación');
            
            // Estado actual de la unidad
            $table->enum('status', [
                'available',        // Disponible para uso
                'in_use',           // En uso actualmente
                'reserved',         // Reservado para cirugía
                'in_sterilization', // En proceso de esterilización
                'maintenance',      // En mantenimiento
                'quarantine',       // En cuarentena/revisión
                'damaged',          // Dañado/No funcional
                'expired',          // Caducado
                'lost',             // Extraviado
                'retired'           // Dado de baja
            ])->default('available');
            
            // Ubicación actual
            $table->foreignId('current_location_id')
                  ->nullable()
                  ->constrained('storage_locations')
                  ->onDelete('set null')
                  ->comment('Ubicación física actual');
            
            // Información para instrumentales
            $table->integer('sterilization_cycles')->default(0)->comment('Número de ciclos de esterilización');
            $table->date('last_sterilization_date')->nullable()->comment('Última fecha de esterilización');
            $table->date('next_maintenance_date')->nullable()->comment('Próxima fecha de mantenimiento programado');
            $table->integer('max_sterilization_cycles')->nullable()->comment('Ciclos máximos permitidos');
            
            // Información de costos
            $table->decimal('acquisition_cost', 10, 2)->nullable()->comment('Costo de adquisición');
            $table->date('acquisition_date')->nullable()->comment('Fecha de adquisición');
            
            // Información del proveedor en esta unidad específica
            // TEMPORAL: Descomentar cuando exista la tabla suppliers
                //$table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
                //$table->string('supplier_invoice')->nullable()->comment('Número de factura del proveedor');
            // TEMPORAL: Descomentar cuando exista la tabla suppliers

            
            // Notas y observaciones
            $table->text('notes')->nullable()->comment('Observaciones generales');
            $table->text('damage_description')->nullable()->comment('Descripción de daños si aplica');
            
            // Campos de auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para optimizar consultas
            $table->index(['product_id', 'status'], 'idx_product_status');
            $table->index('current_location_id', 'idx_current_location');
            $table->index('expiration_date', 'idx_expiration');
            $table->index('status', 'idx_status');
            $table->index('batch_number', 'idx_batch');
            $table->index(['epc', 'deleted_at'], 'idx_epc_active');
            $table->index(['serial_number', 'deleted_at'], 'idx_serial_active');
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