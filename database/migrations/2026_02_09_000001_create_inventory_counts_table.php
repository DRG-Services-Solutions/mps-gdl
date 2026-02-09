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
        Schema::create('inventory_counts', function (Blueprint $table) {
            $table->id();
            $table->string('count_number')->unique(); // INV-2026-001
            
            // Tipo de inventario
            $table->enum('type', [
                'full',        // Inventario completo (cierre anual)
                'partial',     // Por ubicación/zona específica
                'cyclic',      // Rotativo (clasificación ABC)
                'spot_check'   // Aleatorio/auditoría sorpresa
            ])->default('partial');
            
            // Método de conteo
            $table->enum('method', [
                'rfid_bulk',     // Escaneo masivo RFID
                'rfid_handheld', // Pistola RFID portátil
                'barcode_scan',  // Código de barras
                'manual'         // Conteo manual
            ])->default('barcode_scan');
            
            // Estado del inventario
            $table->enum('status', [
                'draft',           // Borrador, preparando
                'in_progress',     // En proceso de conteo
                'pending_review',  // Conteo terminado, pendiente revisión
                'approved',        // Aprobado, ajustes aplicados
                'cancelled'        // Cancelado
            ])->default('draft');
            
            // Alcance del inventario
            $table->foreignId('legal_entity_id')->constrained()->onDelete('cascade');
            $table->foreignId('sub_warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('storage_location_id')->nullable()->constrained()->onDelete('set null');
            
            // Fechas del proceso
            $table->timestamp('scheduled_at')->nullable(); // Fecha programada
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Usuarios responsables
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Resumen (se actualiza al finalizar)
            $table->integer('total_expected')->default(0);
            $table->integer('total_counted')->default(0);
            $table->integer('total_matched')->default(0);
            $table->integer('total_discrepancies')->default(0);
            $table->decimal('accuracy_percentage', 5, 2)->nullable(); // 99.50%
            
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['status', 'legal_entity_id']);
            $table->index(['type', 'status']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_counts');
    }
};
