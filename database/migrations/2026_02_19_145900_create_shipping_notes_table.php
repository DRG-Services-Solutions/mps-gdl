<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_notes', function (Blueprint $table) {
            $table->id();

            // ═══════════════════════════════════════════════════════════
            // IDENTIFICACIÓN
            // ═══════════════════════════════════════════════════════════
            $table->string('shipping_number', 50)->unique()
                ->comment('Número de remisión: REM-2025-000001');

            // ═══════════════════════════════════════════════════════════
            // ORIGEN: CIRUGÍA PROGRAMADA
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('scheduled_surgery_id')
                ->constrained('scheduled_surgeries')
                ->onDelete('restrict')
                ->comment('Cirugía programada que origina esta remisión');

            // ═══════════════════════════════════════════════════════════
            // DATOS DE LA CIRUGÍA (heredados de ScheduledSurgery)
            // Se copian para independencia y consultas rápidas
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('hospital_id')
                ->constrained('hospitals')
                ->onDelete('restrict');

            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->onDelete('set null');

            $table->foreignId('surgical_checklist_id')
                ->constrained('surgical_checklists')
                ->onDelete('restrict')
                ->comment('Checklist usado para evaluar productos');

            $table->foreignId('hospital_modality_config_id')
                ->nullable()
                ->constrained('hospital_modality_configs')
                ->onDelete('set null')
                ->comment('Modalidad de pago del hospital');

            $table->string('surgery_type', 255)
                ->comment('Tipo de cirugía (texto del checklist)');

            $table->date('surgery_date');

            // ═══════════════════════════════════════════════════════════
            // FACTURACIÓN
            // ═══════════════════════════════════════════════════════════
            $table->foreignId('billing_legal_entity_id')
                ->constrained('legal_entities')
                ->onDelete('restrict')
                ->comment('Razón social que factura');

            // ═══════════════════════════════════════════════════════════
            // EVALUACIÓN DEL CHECKLIST
            // Almacena la evaluación completa de condicionales al crear
            // ═══════════════════════════════════════════════════════════
            $table->json('checklist_evaluation')->nullable()
                ->comment('JSON: productos evaluados con cantidades ajustadas y condicionales aplicados');
           

            // ═══════════════════════════════════════════════════════════
            // ESTADO
            // ═══════════════════════════════════════════════════════════
            $table->enum('status', [
                'draft',        
                'confirmed',   
                'sent',         
                'in_surgery',   
                'returned',     
                'completed',    
                'cancelled',    
            ])->default('draft');

            // ═══════════════════════════════════════════════════════════
            // NOTAS Y AUDITORÍA
            // ═══════════════════════════════════════════════════════════
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict');

            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // Timestamps de cada etapa del flujo
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('surgery_started_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // ═══════════════════════════════════════════════════════════
            // ÍNDICES
            // ═══════════════════════════════════════════════════════════
            $table->index('shipping_number');
            $table->index('scheduled_surgery_id');
            $table->index('hospital_id');
            $table->index('doctor_id');
            $table->index('surgical_checklist_id');
            $table->index('billing_legal_entity_id');
            $table->index('status');
            $table->index('surgery_date');
            $table->index('created_at');
            $table->index(['status', 'surgery_date']);
            $table->index(['hospital_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_notes');
    }
};