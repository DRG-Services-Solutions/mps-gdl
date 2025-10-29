<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_receipts', function (Blueprint $table) {
            $table->string('invoice_file')->nullable()->after('notes')->comment('Ruta del archivo de factura');
            $table->string('invoice_number')->nullable()->after('receipt_number')->comment('Número de factura del proveedor');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_receipts', function (Blueprint $table) {
            $table->dropColumn(['invoice_file', 'invoice_number']);
        });
    }
};