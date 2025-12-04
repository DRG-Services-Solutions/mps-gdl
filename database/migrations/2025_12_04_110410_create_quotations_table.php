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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            
            // Número de cotización
            $table->string('quotation_number', 50)->unique();
            
            // ═══════════════════════════════════════════════════════════
            // HOSPITAL / CLIENTE
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('hospital_id')
                ->constrained('hospitals')
                ->onDelete('restrict');
            
            // ═══════════════════════════════════════════════════════════
            // DOCTOR / CIRUJANO (opcional)
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->onDelete('set null');
            
            // ═══════════════════════════════════════════════════════════
            // CIRUGÍA
            // ═══════════════════════════════════════════════════════════
            $table->string('surgery_type', 255)->nullable();
            $table->date('surgery_date')->nullable();
            
            // ═══════════════════════════════════════════════════════════
            // FACTURACIÓN
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('billing_legal_entity_id')
                ->constrained('legal_entities')
                ->onDelete('restrict');
            
            // ═══════════════════════════════════════════════════════════
            // ESTADO
            // ═══════════════════════════════════════════════════════════
            $table->enum('status', [
                'draft',        // Borrador
                'sent',         // Enviada (opcional)
                'in_surgery',   // Material en cirugía
                'completed',    // Material retornado
                'invoiced'      // Ventas generadas
            ])->default('draft');
            
            // ═══════════════════════════════════════════════════════════
            // NOTAS
            // ═══════════════════════════════════════════════════════════
            $table->text('notes')->nullable();
            
            // ═══════════════════════════════════════════════════════════
            // AUDITORÍA
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            
            $table->timestamps();
            
            // ═══════════════════════════════════════════════════════════
            // ÍNDICES
            // ═══════════════════════════════════════════════════════════
            $table->index('quotation_number');
            $table->index('hospital_id');
            $table->index('doctor_id');
            $table->index('billing_legal_entity_id');
            $table->index('status');
            $table->index('surgery_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};