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
            
            // Referencia a la unidad esperada del sistema
            $table->foreignId('product_unit_id')->nullable()->constrained()->onDelete('set null');
            
            // Producto (para referencia rápida y casos donde no hay product_unit)
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Snapshot de datos del producto/unidad al momento del conteo
            $table->string('product_code');
            $table->string('product_name');
            $table->string('expected_epc')->nullable();        // EPC esperado del sistema
            $table->string('expected_serial')->nullable();     // Serial esperado
            $table->string('expected_batch')->nullable();      // Lote esperado
            
            // Datos escaneados/contados
            $table->string('scanned_epc')->nullable();         // EPC escaneado físicamente
            $table->string('scanned_serial')->nullable();      // Serial escaneado
            $table->string('scanned_barcode')->nullable();     // Código de barras escaneado
            $table->string('scanned_batch')->nullable();       // Lote encontrado
            
            // Para conteo por cantidad (productos sin serializar)
            $table->integer('expected_quantity')->default(1);  // Normalmente 1 para unidades individuales
            $table->integer('counted_quantity')->default(0);   // Lo que se contó físicamente
            $table->integer('difference')->default(-1);        // counted - expected
            
            // Estado del item
            $table->enum('status', [
                'pending',    // Pendiente de verificar
                'found',      // Encontrado (EPC/Serial coincide)
                'matched',    // Coincide (para conteo por cantidad)
                'surplus',    // Sobrante (encontrado pero no esperado)
                'missing',    // Faltante (esperado pero no encontrado)
                'wrong_location', // En ubicación incorrecta
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
            
            // Ubicación donde se encontró (puede diferir de la esperada)
            $table->foreignId('found_location_id')->nullable()->constrained('storage_locations')->onDelete('set null');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['inventory_count_id', 'status']);
            $table->index(['inventory_count_id', 'product_id']);
            $table->index('expected_epc');
            $table->index('scanned_epc');
            $table->index('expected_serial');
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
