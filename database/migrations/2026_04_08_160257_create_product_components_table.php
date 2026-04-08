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
        Schema::create('product_components', function (Blueprint $table) {
            $table->id();
            // El Set / Kit / Torre (Producto Padre)
            $table->foreignId('parent_product_id')->constrained('products')->cascadeOnDelete();
            
            // La pieza individual / Consumible (Producto Hijo)
            $table->foreignId('child_product_id')->constrained('products')->restrictOnDelete();
            
            // ¿Cuántas piezas de este hijo lleva el Set?
            $table->integer('quantity')->default(1);
            
            // ¿Es obligatorio para que el Set funcione?
            $table->boolean('is_mandatory')->default(true);
            
            $table->text('notes')->nullable();

            $table->timestamps();
            
            // Un Set no puede tener el mismo componente repetido en dos filas (solo se suma la cantidad)
            $table->unique(['parent_product_id', 'child_product_id'], 'unique_parent_child_component');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_components');
    }
};
