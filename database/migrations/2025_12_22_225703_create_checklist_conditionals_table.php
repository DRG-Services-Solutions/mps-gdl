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
            $table->foreignId('checklist_item_id')->constrained('checklist_items')->onDelete('cascade')->comment('ID del item del check list');
            
            // Condicionales por Hospital o Doctor
            $table->foreignId('legal_entity_id')->nullable()->constrained('legal_entities')->comment('Hospital/Doctor específico');
            
            // Condicionales por Modalidad
            $table->enum('payment_mode', ['particular', 'aseguradora'])->nullable()->comment('Modalidad de pago');
            
            // Tipo de condición
            $table->enum('condition_type', ['required', 'excluded', 'optional'])->default('required')->comment('required=obligatorio, excluded=excluir, optional=opcional');
            
            // Modificador de cantidad
            $table->decimal('quantity_multiplier', 5, 2)->default(1.00)->comment('Multiplicador de cantidad (ej: 1.5 = +50%)');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('checklist_item_id');
            $table->index('legal_entity_id');
            $table->index('payment_mode');
            $table->index('condition_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_conditionals');
    }
};