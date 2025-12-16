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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            
            // ═══════════════════════════════════════════════════════════
            // COTIZACIÓN
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('quotation_id')
                ->constrained('quotations')
                ->onDelete('cascade');
            
            // ═══════════════════════════════════════════════════════════
            // PRODUCTO ESPECÍFICO
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('product_unit_id')
                ->constrained('product_units')
                ->onDelete('restrict');
            
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('restrict');
            
            $table->integer('quantity')->default(1)
                ->comment('Cantidad de unidades físicas de este producto');
            
            // ═══════════════════════════════════════════════════════════
            // ORIGEN DEL PRODUCTO
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('source_legal_entity_id')
                ->constrained('legal_entities')
                ->onDelete('restrict');
            
            $table->foreignId('source_sub_warehouse_id')
                ->nullable()
                ->constrained('sub_warehouses')
                ->onDelete('restrict');
            
            // ═══════════════════════════════════════════════════════════
            // MODALIDAD DE COBRO ⭐ CRÍTICO
            // ═══════════════════════════════════════════════════════════
            $table->enum('billing_mode', ['rental', 'sale'])->default('rental');
            /*
             * rental (RENTA):
             *   - Se cobra por el uso, típicamente para instrumental
             *   - El producto regresa y se puede reutilizar
             *   
             * sale (VENTA):
             *   - Se cobra como venta definitiva
             *   - Para consumibles o productos que no regresan
             *   
             * VALIDACIÓN ESTRICTA (se aplica AL RETORNO):
             * - Consumibles Quirúrgicos → FORZOSAMENTE sale
             * - Instrumental Quirúrgico → FORZOSAMENTE rental
             * - No regresó → SIEMPRE sale (independiente del tipo)
             */
            
            // ═══════════════════════════════════════════════════════════
            // PRECIOS
            // ═══════════════════════════════════════════════════════════
            $table->decimal('rental_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            
            // ═══════════════════════════════════════════════════════════
            // CONTROL DE ENVÍO/RETORNO
            // ═══════════════════════════════════════════════════════════
            $table->integer('quantity_sent')->default(0)
                ->comment('Cantidad enviada a cirugía');
            
            $table->integer('quantity_returned')->default(0)
                ->comment('Cantidad que regresó de cirugía');
            
            // ═══════════════════════════════════════════════════════════
            // ESTADO
            // ═══════════════════════════════════════════════════════════
            $table->enum('status', [
                'pending',      // Pendiente de enviar
                'sent',         // Enviado a cirugía
                'returned',     // Regresó (no se usó)
                'used',         // Usado (no regresó)
                'invoiced'      // Facturado
            ])->default('pending');
            
            // ═══════════════════════════════════════════════════════════
            // FECHAS
            // ═══════════════════════════════════════════════════════════
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            
            $table->timestamps();
            
            // ═══════════════════════════════════════════════════════════
            // ÍNDICES
            // ═══════════════════════════════════════════════════════════
            $table->index('quotation_id');
            $table->index('product_unit_id');
            $table->index('product_id');
            $table->index('billing_mode');
            $table->index('status');
            $table->index(['quotation_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};