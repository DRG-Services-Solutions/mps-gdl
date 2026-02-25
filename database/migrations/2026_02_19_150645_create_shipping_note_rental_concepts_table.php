<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_note_rental_concepts', function (Blueprint $table) {
            $table->id();

            // ═══════════════════════════════════════════════════════════
            // REMISIÓN
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('shipping_note_id')
                ->constrained('shipping_notes')
                ->onDelete('cascade');

            // ═══════════════════════════════════════════════════════════
            // CONCEPTO DE RENTA
            // ═══════════════════════════════════════════════════════════
            $table->string('concept', 255)
                ->comment('Descripción del cargo (ej: Renta de motor, Uso de sala, etc.)');

            $table->integer('quantity')->default(1);

            $table->decimal('unit_price', 12, 2)->default(0);

            $table->decimal('total_price', 12, 2)->default(0)
                ->comment('quantity × unit_price');

            $table->boolean('exclude_from_invoice')->default(false)
                ->comment('Si es cortesía o no se factura');

            // ═══════════════════════════════════════════════════════════
            // METADATA
            // ═══════════════════════════════════════════════════════════
            $table->text('notes')->nullable();

            $table->timestamps();

            // ═══════════════════════════════════════════════════════════
            // ÍNDICES
            // ═══════════════════════════════════════════════════════════
            $table->index('shipping_note_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_note_rental_concepts');
    }
};