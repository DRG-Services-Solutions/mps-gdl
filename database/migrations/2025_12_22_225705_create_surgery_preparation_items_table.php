<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgery_preparation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preparation_id')->constrained('surgery_preparations')->onDelete('cascade')->comment('Preparación a la que pertenece');

            $table->foreignId('product_id')->constrained('products')->comment('Producto requerido');
            
            // Cantidades del Check List
            $table->integer('quantity_required')->comment('Cantidad requerida según check list (con condicionales)');
            $table->boolean('is_mandatory')->comment('¿Es obligatorio?');
            
            // Cantidades del Pre-Armado
            $table->integer('quantity_in_package')->default(0)->comment('Cantidad disponible en pre-armado');
            
            // Cantidades Faltantes
            $table->integer('quantity_missing')->default(0)->comment('Cantidad faltante a surtir');
            $table->integer('quantity_picked')->default(0)->comment('Cantidad ya surtida');
            
            // Estado del item
            $table->enum('status', ['pending', 'in_package', 'complete', 'missing'])->default('pending')->comment('pending=no revisado, in_package=completo en paquete, complete=surtido, missing=falta');
            
            // Ubicación del producto si hay que buscarlo
            $table->foreignId('storage_location_id')->nullable()->constrained('storage_locations')->comment('Dónde encontrar el producto');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('preparation_id');
            $table->index('product_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgery_preparation_items');
    }
};