<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('purchase_order_receipts')->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Cantidades
            $table->integer('quantity_ordered')->comment('Cantidad ordenada originalmente');
            $table->integer('quantity_received')->comment('Cantidad recibida en ESTA recepción específica');
            
            // Precio al momento de la recepción
            $table->decimal('unit_price', 10, 2)->comment('Precio unitario');
            
            // Control de calidad y trazabilidad
            $table->string('batch_number')->nullable()->comment('Número de lote');
            $table->date('expiry_date')->nullable()->comment('Fecha de caducidad');
            
            // Notas específicas de esta recepción
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('receipt_id');
            $table->index('product_id');
            $table->index('batch_number');
            $table->index('expiry_date');
            $table->index('condition');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_items');
    }
};