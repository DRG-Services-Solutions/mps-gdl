<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_note_packages', function (Blueprint $table) {
            $table->id();

            // ═══════════════════════════════════════════════════════════
            // REMISIÓN
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('shipping_note_id')
                ->constrained('shipping_notes')
                ->onDelete('cascade')
                ->comment('Remisión a la que pertenece');

            // ═══════════════════════════════════════════════════════════
            // PAQUETE PRE-ARMADO FÍSICO
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('pre_assembled_package_id')
                ->constrained('pre_assembled_packages')
                ->onDelete('restrict')
                ->comment('Paquete físico asignado');

            $table->foreignId('surgical_checklist_id')
                ->constrained('surgical_checklists')
                ->onDelete('restrict')
                ->comment('Checklist contra el que se comparó el paquete');

            // ═══════════════════════════════════════════════════════════
            // COMPARACIÓN CHECKLIST vs CONTENIDO REAL
            // Snapshot de la comparación al momento de asignar
            // ═══════════════════════════════════════════════════════════
            $table->json('comparison_snapshot')->nullable()
                ->comment('JSON: resultado de comparar contenido del paquete vs checklist evaluado');
            /*
             * Estructura del JSON:
             * {
             *   "completeness_percentage": 85.5,
             *   "total_required": 12,
             *   "total_available": 10,
             *   "items": [
             *     {
             *       "product_id": 42,
             *       "product_name": "Tornillo 4.0x30mm",
             *       "required": 4,
             *       "available_in_package": 4,
             *       "missing": 0,
             *       "is_complete": true
             *     },
             *     {
             *       "product_id": 55,
             *       "product_name": "Placa recta 6 orificios",
             *       "required": 2,
             *       "available_in_package": 1,
             *       "missing": 1,
             *       "is_complete": false
             *     }
             *   ]
             * }
             */

            // ═══════════════════════════════════════════════════════════
            // ESTADO DEL PAQUETE EN ESTA REMISIÓN
            // ═══════════════════════════════════════════════════════════
            $table->enum('status', [
                'assigned',     // Paquete asignado a la remisión
                'sent',         // Paquete salió del almacén
                'in_surgery',   // Paquete en cirugía
                'returned',     // Paquete regresó (pendiente revisión)
                'reviewed',     // Paquete revisado post-cirugía
            ])->default('assigned');

            $table->text('notes')->nullable();

            $table->timestamps();

            // ═══════════════════════════════════════════════════════════
            // ÍNDICES
            // ═══════════════════════════════════════════════════════════
            $table->index('shipping_note_id');
            $table->index('pre_assembled_package_id');
            $table->index('status');
            $table->index(['shipping_note_id', 'status']);

            // Un paquete solo puede estar en una remisión activa a la vez
            $table->unique(
                ['pre_assembled_package_id', 'shipping_note_id'],
                'unique_package_per_note'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_note_packages');
    }
};