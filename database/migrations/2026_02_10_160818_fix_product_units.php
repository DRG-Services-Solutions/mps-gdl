<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            
            $table->dropColumn('current_status');

           
            $table->foreignId('supplier_id')
                  ->nullable()
                  ->after('acquisition_date')
                  ->constrained('suppliers')
                  ->nullOnDelete()
                  ->comment('Proveedor de esta unidad específica');

            $table->string('supplier_invoice')
                  ->nullable()
                  ->after('supplier_id')
                  ->comment('Número de factura del proveedor');

            $table->foreignId('purchase_order_id')
                  ->nullable()
                  ->after('supplier_invoice')
                  ->constrained('purchase_orders')
                  ->nullOnDelete()
                  ->comment('Orden de compra de donde proviene esta unidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            // Restaurar current_status
            $table->string('current_status')->nullable()->after('status');

            // Eliminar foreign keys
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['purchase_order_id']);

            // Eliminar columnas
            $table->dropColumn([
                'supplier_id',
                'supplier_invoice',
                'purchase_order_id',
            ]);
        });
    }
};