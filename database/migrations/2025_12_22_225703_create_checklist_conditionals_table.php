<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_conditionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_item_id')
                ->constrained('checklist_items')
                ->onDelete('cascade')
                ->comment('Producto del checklist al que aplica este condicional');
            
            // CONDICIONALES ===                
            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->onDelete('cascade')
                ->comment('Doctor específico (null = aplica a todos)');
            
            $table->foreignId('hospital_id')
                ->nullable()
                ->constrained('hospitals')
                ->onDelete('cascade')
                ->comment('Hospital específico (null = aplica a todos)');
            
            $table->foreignId('modality_id')
                ->nullable()
                ->constrained('modalities')
                ->onDelete('cascade')
                ->comment('Modalidad de pago (null = aplica a todas)');
            
            $table->foreignId('legal_entity_id')
                ->nullable()
                ->constrained('legal_entities')
                ->onDelete('cascade')
                ->comment('Entidad legal que factura (null = aplica a todas)');
            
            //CANTIDAD ===
            
            $table->integer('quantity_override')
                ->nullable()
                ->comment('Cantidad que REEMPLAZA la base. Ejemplo: base=2, override=5 → usar 5 (no 2+5)');
            
            //PRODUCTOS ADICIONALES ===
            
            $table->boolean('is_additional_product')
                ->default(false)
                ->comment('true = producto que NO está en checklist base pero debe incluirse');
            
            $table->integer('additional_quantity')
                ->nullable()
                ->comment('Cantidad del producto adicional (solo si is_additional_product=true)');
            
            //METADATA ===
            
            $table->text('notes')
                ->nullable()
                ->comment('Razón del condicional (ej: "Dr. Ramírez requiere instrumental adicional")');
            
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->comment('Usuario que creó el condicional');
            
            $table->timestamps();
            
            //ÍNDICES PARA PERFORMANCE ===
            
            $table->index('checklist_item_id');
            $table->index('doctor_id');
            $table->index('hospital_id');
            $table->index('modality_id');
            $table->index('legal_entity_id');
            $table->index('is_additional_product');
            
            // Índice compuesto para búsquedas rápidas
            $table->index(
                ['doctor_id', 'hospital_id', 'modality_id', 'legal_entity_id'], 
                'conditional_search_idx');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_conditionals');
    }
};