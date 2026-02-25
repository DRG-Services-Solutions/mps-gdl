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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique(); // ADJ-2026-001
            
            // Relación con el conteo (opcional, puede haber ajustes manuales)
            $table->foreignId('inventory_count_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('inventory_count_item_id')->nullable()->constrained()->onDelete('set null');
            
            // Producto afectado
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_unit_id')->nullable()->constrained()->onDelete('set null');
            
            // Tipo de ajuste
            $table->enum('adjustment_type', [
                'surplus',     // Sobrante - agregar al inventario
                'shortage',    // Faltante - restar del inventario
                'damaged',     // Dañado - marcar como dañado
                'expired',     // Caducado - marcar como caducado
                'lost',        // Extraviado - marcar como perdido
                'found',       // Encontrado - reintegrar al inventario
                'correction',  // Corrección de error de sistema
                'transfer'     // Transferencia/reubicación
            ]);
            
            // Cantidades
            $table->integer('quantity'); // Positivo o negativo según el tipo
            $table->integer('quantity_before')->default(0); // Stock antes del ajuste
            $table->integer('quantity_after')->default(0);  // Stock después del ajuste
            
            // Ubicación afectada
            $table->foreignId('legal_entity_id')->constrained()->onDelete('cascade');
            $table->foreignId('sub_warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('storage_location_id')->nullable()->constrained()->onDelete('set null');
            
            // Justificación
            $table->text('reason')->nullable();
            $table->string('reference_document')->nullable(); // Acta, foto, documento soporte
            
            // Estado del ajuste
            $table->enum('status', [
                'pending',   // Pendiente de aprobación
                'approved',  // Aprobado y aplicado
                'rejected',  // Rechazado
                'reversed'   // Revertido
            ])->default('pending');
            
            // Usuarios y fechas
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('applied_at')->nullable(); // Cuando se aplicó al inventario
            
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['status', 'legal_entity_id']);
            $table->index(['adjustment_type', 'status']);
            $table->index('inventory_count_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
