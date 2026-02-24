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
            $table->foreignId('legal_entity_id')->nullable()->constrained('legal_entities')->onDelete('restrict');
            $table->foreignId('sub_warehouse_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('set null');
            $table->integer('reserved_quantity')->default(0)->comment('Cantidad reservada para cotizaciones');
            

            // Relación con el producto del catálogo
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Identificadores únicos (solo uno será usado según el tipo de producto)
            $table->string('epc')->nullable()->comment('Código EPC para RFID');
            $table->string('serial_number')->nullable()->comment('Número de serie para instrumentales');
            
            // Información de lote y caducidad
            $table->string('batch_number')->nullable()->comment('Número de lote del fabricante');
            $table->date('expiration_date')->nullable()->comment('Fecha de caducidad');
            $table->date('manufacture_date')->nullable()->comment('Fecha de fabricación');
            
            // Estado actual de la unidad
            $table->enum('status', [
                'available',        
                'in_use',           
                'reserved',         
                'in_sterilization', 
                'maintenance',      
                'quarantine',       
                'damaged',          
                'expired',          
                'lost',             
                'retired'           
            ])->default('available');
            
            // Ubicación actual
            $table->foreignId('current_location_id')
                  ->nullable()
                  ->constrained('storage_locations')
                  ->onDelete('set null')
                  ->comment('Ubicación física actual');

                

                  //informacion de la cirugia actual si está en uso
         
            //$table->foreignId('suergery_id')->constrained('surgeries')->onDelete('set null')->comment('Cirugía actual si está en uso');
            
            // Información de costos
            $table->decimal('acquisition_cost', 10, 2)->nullable()->comment('Costo de adquisición');
            $table->date('acquisition_date')->nullable()->comment('Fecha de adquisición');

            // Notas y observaciones
            $table->text('notes')->nullable()->comment('Observaciones generales');
            $table->text('damage_description')->nullable()->comment('Descripción de daños si aplica');
            
            // Campos de auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reserved_at')->nullable();
            $table->foreignId('reserved_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para optimizar consultas
            $table->index(['product_id', 'status'], 'idx_product_status');
            $table->index('current_location_id', 'idx_current_location');
            $table->index('expiration_date', 'idx_expiration');
            $table->index('status', 'idx_status');
            $table->index('batch_number', 'idx_batch');
            $table->unique(['epc', 'deleted_at'], 'unique_epc_active');
            $table->unique(['serial_number', 'deleted_at'], 'unique_serial_active');
            $table->index('sub_warehouse_id');        
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