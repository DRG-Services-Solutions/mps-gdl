<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            
            // Cantidades
            $table->integer('quantity_ordered')->comment('Cantidad solicitada');
            $table->integer('quantity_received')->default(0)->comment('Cantidad recibida');
            
            // Precios
            $table->decimal('unit_price', 10, 2)->comment('Precio unitario');
            $table->decimal('subtotal', 10, 2)->comment('Subtotal línea');
            
            // Información del producto (snapshot)
            $table->string('product_code')->comment('Código del producto al momento de la orden');
            $table->string('product_name')->comment('Nombre del producto al momento de la orden');
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            $table->index('purchase_order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};