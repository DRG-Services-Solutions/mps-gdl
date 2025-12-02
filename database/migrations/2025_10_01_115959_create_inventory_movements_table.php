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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            //Relacion con legal entity
            $table->foreignId('legal_entity_id')->nullable()->constrained('legal_entities')->onDelete('restrict');
            $table->foreignId('sub_warehouse_id')
                  ->nullable()
                  ->after('legal_entity_id')
                  ->constrained('sub_warehouses')
                  ->onDelete('set null');
            // ==========================================================
            // TIPO DE MOVIMIENTO
            // ==========================================================
            $table->enum('type', [
                'entry',           
                'exit',            
                'transfer',       
                'adjustment',      
                'sterilization',   
                'return',          
                'discard'          
            ]);
            
            // ==========================================================
            // PRODUCTO Y UNIDAD
            // ==========================================================
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            
            $table->foreignId('product_unit_id')
                  ->nullable()
                  ->constrained('product_units')
                  ->nullOnDelete();
            
            // Para productos sin tracking individual, se usa cantidad
            $table->integer('quantity')->default(1);
            
            // ==========================================================
            // UBICACIONES (ORIGEN Y DESTINO)
            // ==========================================================
            $table->foreignId('from_location_id')
                  ->nullable()
                  ->constrained('storage_locations')
                  ->nullOnDelete();
            
            $table->foreignId('to_location_id')
                  ->nullable()
                  ->constrained('storage_locations')
                  ->nullOnDelete();
            
            // ==========================================================
            // USUARIO RESPONSABLE
            // ==========================================================
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            // ==========================================================
            // INFORMACIÓN ADICIONAL
            // ==========================================================
            $table->string('reference_number')->nullable(); 
            $table->text('notes')->nullable();
            $table->string('reason')->nullable(); 
            
            // ==========================================================
            // RELACIÓN CON CIRUGÍAS/PACIENTES (Para salidas a quirófano)
            // ==========================================================
            //$table->foreignId('surgery_id')
            //      ->nullable()
            //      ->constrained('surgeries')
            //      ->nullOnDelete();
            
            //$table->foreignId('patient_id')
            //      ->nullable()
            //      ->constrained('patients')
            //      ->nullOnDelete();
            
            // ==========================================================
            // INFORMACIÓN DE COSTOS
            // ==========================================================
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            
            // ==========================================================
            // INFORMACIÓN DE LOTE (Para entradas)
            // ==========================================================
            $table->string('lot_number')->nullable();
            $table->date('expiration_date')->nullable();
            
            // ==========================================================
            // FECHAS
            // ==========================================================
            $table->timestamp('movement_date')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->timestamps();
            
            // ==========================================================
            // ÍNDICES PARA OPTIMIZACIÓN
            // ==========================================================
            $table->index(['product_id', 'type', 'movement_date']);
            $table->index(['product_unit_id', 'movement_date']);
            $table->index(['from_location_id', 'movement_date']);
            $table->index(['to_location_id', 'movement_date']);
            $table->index(['type', 'movement_date']);
            $table->index('reference_number');
            $table->index('sub_warehouse_id');        

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
