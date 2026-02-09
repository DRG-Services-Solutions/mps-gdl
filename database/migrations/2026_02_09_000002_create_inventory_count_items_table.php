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
        Schema::create('inventory_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_count_id')->constrained()->onDelete('cascade');
            
            // Producto
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('product_code'); // Snapshot
            $table->string('product_name'); // Snapshot
            
            // Unidad específica (para RFID)
            $table->foreignId('product_unit_id')->nullable()->constrained()->onDelete('set null');
            
            // Identificadores escaneados
            $table->string('epc')->nullable(); // Tag RFID escaneado
            $table->string('serial_number')->nullable();
            $table->string('barcode_scanned')->nullable(); // Código de barras escaneado
            $table->string('batch_number')->nullable();
            
            // Cantidades
            $table->integer('expected_quantity')->default(0); // Lo que dice el sistema
            $table->integer('counted_quantity')->default(0);  // Lo que se contó físicamente
            $table->integer('difference')->default(0);        // counted - expected
            
            // Estado del item
            $table->enum('status', [
                'pending',    // Pendiente de contar
                'matched',    // Coincide exactamente
                'surplus',    // Sobrante (hay más de lo esperado)
                'shortage',   // Faltante (hay menos de lo esperado)
                'not_found',  // No encontrado físicamente
                'unexpected', // Encontrado pero no esperado en sistema
                'damaged',    // Encontrado pero dañado
                'expired'     // Encontrado pero caducado
            ])->default('pending');
            
            // Información de escaneo
            $table->timestamp('scanned_at')->nullable();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('scan_method')->nullable(); // rfid, barcode, manual
            
            // Para reconteos
            $table->integer('recount_number')->default(0);
            $table->timestamp('last_recount_at')->nullable();
            
            // Justificación de discrepancia
            $table->text('discrepancy_reason')->nullable();
            $table->boolean('discrepancy_justified')->default(false);
            $table->foreignId('justified_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['inventory_count_id', 'status']);
            $table->index(['inventory_count_id', 'product_id']);
            $table->index('epc');
            $table->index('barcode_scanned');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_count_items');
    }
};
