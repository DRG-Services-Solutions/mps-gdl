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
        // Si la tabla sales NO existe, crearla
        if (!Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->id();
                
                $table->string('sale_number', 50)->unique();
                
                // Relacionado a cotización
                $table->foreignId('quotation_id')
                    ->nullable()
                    ->constrained('quotations')
                    ->onDelete('restrict');
                
                $table->foreignId('quotation_item_id')
                    ->nullable()
                    ->constrained('quotation_items')
                    ->onDelete('restrict');
                
                // Razón social que factura
                $table->foreignId('billing_legal_entity_id')
                    ->constrained('legal_entities')
                    ->onDelete('restrict');
                
                // Origen del producto
                $table->foreignId('source_legal_entity_id')
                    ->constrained('legal_entities')
                    ->onDelete('restrict');
                
                $table->foreignId('source_sub_warehouse_id')
                    ->nullable()
                    ->constrained('sub_warehouses')
                    ->onDelete('restrict');
                
                // Producto
                $table->foreignId('product_unit_id')
                    ->constrained('product_units')
                    ->onDelete('restrict');
                
                $table->foreignId('product_id')
                    ->constrained('products')
                    ->onDelete('restrict');
                
                $table->integer('quantity')->default(1);
                
                // Hospital
                $table->foreignId('hospital_id')
                    ->nullable()
                    ->constrained('hospitals')
                    ->onDelete('restrict');
                
                // Tipo de venta
                $table->enum('sale_type', ['rental', 'consignment_used']);
                
                // Precios
                $table->decimal('cost_price', 10, 2);
                $table->decimal('sale_price', 10, 2);
                $table->decimal('margin', 10, 2)->storedAs('sale_price - cost_price');
                
                // Control
                $table->date('sale_date');
                $table->string('invoice_number', 100)->nullable();
                $table->text('notes')->nullable();
                
                $table->foreignId('created_by')
                    ->constrained('users')
                    ->onDelete('restrict');
                
                $table->timestamps();
                
                // Índices
                $table->index('quotation_id');
                $table->index('billing_legal_entity_id');
                $table->index('source_legal_entity_id');
                $table->index(['source_legal_entity_id', 'source_sub_warehouse_id']);
                $table->index('sale_type');
                $table->index('sale_date');
                $table->index('sale_number');
            });
        } else {
            // Si la tabla sales YA existe, agregar solo los campos nuevos
            Schema::table('sales', function (Blueprint $table) {
                // Verificar y agregar campos si no existen
                
                if (!Schema::hasColumn('sales', 'quotation_id')) {
                    $table->foreignId('quotation_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('quotations')
                        ->onDelete('restrict');
                }
                
                if (!Schema::hasColumn('sales', 'quotation_item_id')) {
                    $table->foreignId('quotation_item_id')
                        ->nullable()
                        ->after('quotation_id')
                        ->constrained('quotation_items')
                        ->onDelete('restrict');
                }
                
                if (!Schema::hasColumn('sales', 'billing_legal_entity_id')) {
                    $table->foreignId('billing_legal_entity_id')
                        ->after('quotation_item_id')
                        ->constrained('legal_entities')
                        ->onDelete('restrict');
                }
                
                if (!Schema::hasColumn('sales', 'source_legal_entity_id')) {
                    $table->foreignId('source_legal_entity_id')
                        ->after('billing_legal_entity_id')
                        ->constrained('legal_entities')
                        ->onDelete('restrict');
                }
                
                if (!Schema::hasColumn('sales', 'source_sub_warehouse_id')) {
                    $table->foreignId('source_sub_warehouse_id')
                        ->nullable()
                        ->after('source_legal_entity_id')
                        ->constrained('sub_warehouses')
                        ->onDelete('restrict');
                }
                
                if (!Schema::hasColumn('sales', 'hospital_id')) {
                    $table->foreignId('hospital_id')
                        ->nullable()
                        ->constrained('hospitals')
                        ->onDelete('restrict');
                }
                
                if (!Schema::hasColumn('sales', 'sale_type')) {
                    $table->enum('sale_type', ['rental', 'consignment_used'])
                        ->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                // Eliminar columnas agregadas
                if (Schema::hasColumn('sales', 'quotation_id')) {
                    $table->dropForeign(['quotation_id']);
                    $table->dropColumn('quotation_id');
                }
                
                if (Schema::hasColumn('sales', 'quotation_item_id')) {
                    $table->dropForeign(['quotation_item_id']);
                    $table->dropColumn('quotation_item_id');
                }
                
                if (Schema::hasColumn('sales', 'billing_legal_entity_id')) {
                    $table->dropForeign(['billing_legal_entity_id']);
                    $table->dropColumn('billing_legal_entity_id');
                }
                
                if (Schema::hasColumn('sales', 'source_legal_entity_id')) {
                    $table->dropForeign(['source_legal_entity_id']);
                    $table->dropColumn('source_legal_entity_id');
                }
                
                if (Schema::hasColumn('sales', 'source_sub_warehouse_id')) {
                    $table->dropForeign(['source_sub_warehouse_id']);
                    $table->dropColumn('source_sub_warehouse_id');
                }
                
                if (Schema::hasColumn('sales', 'hospital_id')) {
                    $table->dropForeign(['hospital_id']);
                    $table->dropColumn('hospital_id');
                }
                
                if (Schema::hasColumn('sales', 'sale_type')) {
                    $table->dropColumn('sale_type');
                }
            });
        }
    }
};