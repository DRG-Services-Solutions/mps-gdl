<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════════════════════
        // CAMPOS FINANCIEROS EN SHIPPING_NOTES
        // ═══════════════════════════════════════════════════════════
        Schema::table('shipping_notes', function (Blueprint $table) {
            $table->decimal('subtotal', 14, 2)->default(0)
                ->after('checklist_evaluation')
                ->comment('Suma de items facturables');

            $table->decimal('tax_rate', 5, 4)->default(0.1600)
                ->after('subtotal')
                ->comment('Tasa de IVA (default 16%)');

            $table->decimal('tax_amount', 14, 2)->default(0)
                ->after('tax_rate')
                ->comment('Monto del IVA calculado');

            $table->decimal('grand_total', 14, 2)->default(0)
                ->after('tax_amount')
                ->comment('Total con IVA');

            $table->timestamp('printed_at')->nullable()
                ->after('confirmed_at')
                ->comment('Fecha/hora de última impresión del PDF');
        });

        // ═══════════════════════════════════════════════════════════
        // CAMPOS DE URGENCIA EN SHIPPING_NOTE_ITEMS
        // ═══════════════════════════════════════════════════════════
        Schema::table('shipping_note_items', function (Blueprint $table) {
            $table->boolean('is_urgency')->default(false)
                ->after('exclude_from_invoice')
                ->comment('Item agregado como urgencia de última hora');

            $table->string('urgency_reason', 500)->nullable()
                ->after('is_urgency')
                ->comment('Motivo de la urgencia');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_notes', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'tax_rate',
                'tax_amount',
                'grand_total',
                'printed_at',
            ]);
        });

        Schema::table('shipping_note_items', function (Blueprint $table) {
            $table->dropColumn(['is_urgency', 'urgency_reason']);
        });
    }
};
