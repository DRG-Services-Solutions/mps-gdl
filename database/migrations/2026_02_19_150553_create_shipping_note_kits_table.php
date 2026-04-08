<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_note_kits', function (Blueprint $table) {
            $table->id();

            // ═══════════════════════════════════════════════════════════
            // REMISIÓN
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('shipping_note_id')
                ->constrained('shipping_notes')
                ->onDelete('cascade')
                ->comment('Remisión a la que pertenece');

            
            // ═══════════════════════════════════════════════════════════
            // PRECIO DE RENTA
            // ═══════════════════════════════════════════════════════════
            $table->decimal('rental_price', 12, 2)->default(0)
                ->comment('Precio de renta del kit completo');

            $table->boolean('exclude_from_invoice')->default(false)
                ->comment('Si es cortesía o no se factura');

            // ═══════════════════════════════════════════════════════════
            // ESTADO
            // ═══════════════════════════════════════════════════════════
            $table->enum('status', [
                'assigned',     // Kit asignado a la remisión
                'sent',         // Kit salió del almacén
                'in_surgery',   // Kit en cirugía
                'returned',     // Kit regresó completo
                'incomplete',   // Kit regresó con faltantes
                'reviewed',     // Kit revisado post-cirugía
            ])->default('assigned');

            $table->text('notes')->nullable();

            $table->timestamps();

            // ═══════════════════════════════════════════════════════════
            // ÍNDICES
            // ═══════════════════════════════════════════════════════════
            $table->index('shipping_note_id');
            $table->index('status');
            $table->index(['shipping_note_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_note_kits');
    }
};