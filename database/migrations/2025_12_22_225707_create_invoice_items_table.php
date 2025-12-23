<?php
// database/migrations/xxxx_xx_xx_create_invoice_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade')->comment('Remisión a la que pertenece');
            
            $table->foreignId('product_id')->constrained('products')->comment('Producto');
            
            // Datos del producto (copia para histórico)
            $table->string('product_code')->comment('Código del producto (copia)');
            $table->string('product_name')->comment('Nombre del producto (copia)');
            
            // EPCs incluidos en esta línea
            $table->json('product_unit_ids')->nullable()->comment('Array de IDs de product_units (EPCs)');
            
            // Cantidades y precios
            $table->integer('quantity')->comment('Cantidad');
            $table->decimal('unit_price', 10, 2)->comment('Precio unitario');
            $table->decimal('subtotal', 12, 2)->comment('Subtotal');
            $table->decimal('iva', 12, 2)->comment('IVA');
            $table->decimal('total', 12, 2)->comment('Total');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('invoice_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};