<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_note_items', function (Blueprint $table) {
            $table->id();

            // ═══════════════════════════════════════════════════════════
            // REMISIÓN
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('shipping_note_id')
                ->constrained('shipping_notes')
                ->onDelete('cascade');

            // ═══════════════════════════════════════════════════════════
            // ORIGEN DEL ITEM
            // Exactamente uno de estos debe tener valor, o ambos null (standalone)
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('shipping_note_package_id')
                ->nullable()
                ->constrained('shipping_note_packages')
                ->onDelete('cascade')
                ->comment('Si viene de un paquete pre-armado');

            $table->foreignId('shipping_note_kit_id')
                ->nullable()
                ->constrained('shipping_note_kits')
                ->onDelete('cascade')
                ->comment('Si viene de un kit quirúrgico');

            $table->enum('item_origin', [
                'package',      // Producto de un paquete pre-armado (consumible)
                'kit',          // Producto de un kit quirúrgico (instrumental)
                'standalone',   // Producto individual agregado manualmente
                'conditional',  // Producto agregado por condicional (add_product/add_dependency)
            ])->comment('De dónde vino este item');

            // ═══════════════════════════════════════════════════════════
            // PRODUCTO
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('restrict');

            $table->foreignId('product_unit_id')
                ->nullable()
                ->constrained('product_units')
                ->onDelete('restrict')
                ->comment('Unidad física específica (EPC). Nullable hasta que se asigne');

            // ═══════════════════════════════════════════════════════════
            // TRAZABILIDAD AL CHECKLIST
            // Permite saber POR QUÉ este producto está en la remisión
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('checklist_item_id')
                ->nullable()
                ->constrained('checklist_items')
                ->onDelete('set null')
                ->comment('Item del checklist que originó este producto');

            $table->foreignId('checklist_conditional_id')
                ->nullable()
                ->constrained('checklist_conditionals')
                ->onDelete('set null')
                ->comment('Condicional que modificó la cantidad o agregó este producto');

            $table->string('conditional_description')->nullable()
                ->comment('Texto legible del condicional aplicado (snapshot)');

            // ═══════════════════════════════════════════════════════════
            // ORIGEN EN ALMACÉN
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('source_legal_entity_id')
                ->nullable()
                ->constrained('legal_entities')
                ->onDelete('restrict')
                ->comment('Razón social dueña del producto');

            $table->foreignId('source_sub_warehouse_id')
                ->nullable()
                ->constrained('sub_warehouses')
                ->onDelete('restrict')
                ->comment('Sub-almacén de donde sale');

            // ═══════════════════════════════════════════════════════════
            // CANTIDADES
            // ═══════════════════════════════════════════════════════════
            $table->integer('quantity_required')->default(1)
                ->comment('Cantidad del checklist evaluado (con condicionales)');

            $table->integer('quantity_sent')->default(0)
                ->comment('Cantidad que salió del almacén');

            $table->integer('quantity_returned')->default(0)
                ->comment('Cantidad que regresó de cirugía');

            $table->integer('quantity_used')->default(0)
                ->comment('Cantidad usada (no regresó) = sent - returned');

            // ═══════════════════════════════════════════════════════════
            // FACTURACIÓN
            // ═══════════════════════════════════════════════════════════
            $table->enum('billing_mode', [
                'sale',         // Venta definitiva (consumibles, lo que no regresa)
                'rental',       // Renta (instrumental que va y viene)
                'no_charge',    // Sin cargo (cortesía, demo, préstamo)
            ])->default('sale');

            $table->boolean('exclude_from_invoice')->default(false)
                ->comment('Heredado del condicional - va físicamente pero no se cobra');

            $table->decimal('unit_price', 12, 2)->default(0)
                ->comment('Precio unitario (renta o venta según billing_mode)');

            $table->decimal('total_price', 12, 2)->default(0)
                ->comment('unit_price × quantity_used (o quantity_sent para renta)');

            // ═══════════════════════════════════════════════════════════
            // ESTADO
            // ═══════════════════════════════════════════════════════════
            $table->enum('status', [
                'pending',      // En la remisión, pendiente de enviar
                'sent',         // Enviado a cirugía
                'in_surgery',   // En cirugía
                'returned',     // Regresó (no se usó)
                'used',         // Usado (no regresó) → se factura como venta
                'invoiced',     // Ya facturado
            ])->default('pending');

            // ═══════════════════════════════════════════════════════════
            // TIMESTAMPS DEL FLUJO
            // ═══════════════════════════════════════════════════════════
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('returned_at')->nullable();

            $table->timestamps();

            // ═══════════════════════════════════════════════════════════
            // ÍNDICES
            // ═══════════════════════════════════════════════════════════
            $table->index('shipping_note_id');
            $table->index('shipping_note_package_id');
            $table->index('shipping_note_kit_id');
            $table->index('product_id');
            $table->index('product_unit_id');
            $table->index('checklist_item_id');
            $table->index('billing_mode');
            $table->index('status');
            $table->index('item_origin');
            $table->index(['shipping_note_id', 'status']);
            $table->index(['shipping_note_id', 'item_origin']);
            $table->index(['shipping_note_id', 'billing_mode']);
            $table->index(['shipping_note_package_id', 'status']);
            $table->index(['shipping_note_kit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_note_items');
    }
};