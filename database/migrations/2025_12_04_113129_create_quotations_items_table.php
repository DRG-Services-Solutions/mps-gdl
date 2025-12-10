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
                ->constrained('quotations');
            
            // ═══════════════════════════════════════════════════════════
            // PRODUCTO ESPECÍFICO
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('product_unit_id')
                ->constrained('product_units')
                ->onDelete('restrict');
            
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('restrict');
            $table->integer('quantity')->default(1);

            
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
            $table->enum('billing_mode', ['rental', 'consignment']);
            /*
             * rental (RENTA):
             *   - Se cobra SIEMPRE, regrese o no
             *   
             * consignment (CONSIGNACIÓN):
             *   - Se cobra SOLO si NO regresa
             */
            
            // ═══════════════════════════════════════════════════════════
            // PRECIOS
            // ═══════════════════════════════════════════════════════════
            $table->decimal('rental_price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            
            // ═══════════════════════════════════════════════════════════
            // CONTROL DE ENVÍO/RETORNO
            // ═══════════════════════════════════════════════════════════
            $table->integer('quantity_sent')->default(1);
            $table->integer('quantity_returned')->default(0);
            
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
            $table->index('billing_mode');
            $table->index('status');
            
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