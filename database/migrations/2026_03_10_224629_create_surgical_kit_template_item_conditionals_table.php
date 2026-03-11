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
        Schema::create('surgical_kit_template_item_conditionals', function (Blueprint $table) {
            // ── Relación con el item del kit ──────────────────────────────
            $table->foreignId('surgical_kit_template_item_id')
                  ->constrained('surgical_kit_template_items')
                  ->cascadeOnDelete();

            // ── Criterios de aplicación (todos opcionales) ────────────────
            $table->foreignId('doctor_id')
                  ->nullable()
                  ->constrained('doctors')
                  ->nullOnDelete();

            $table->foreignId('hospital_id')
                  ->nullable()
                  ->constrained('hospitals')
                  ->nullOnDelete();

            $table->foreignId('modality_id')
                  ->nullable()
                  ->constrained('modalities')
                  ->nullOnDelete();

            $table->foreignId('legal_entity_id')
                  ->nullable()
                  ->constrained('legal_entities')
                  ->nullOnDelete();

            // ── Tipo de acción ────────────────────────────────────────────
            $table->string('action_type', 30);

            // ── Valores según action_type ─────────────────────────────────
            $table->unsignedInteger('quantity_override')->nullable();   
            $table->unsignedInteger('additional_quantity')->nullable(); 
            $table->foreignId('target_product_id')                      
                  ->nullable()
                  ->constrained('products')
                  ->nullOnDelete();
            $table->unsignedInteger('dependency_quantity')->nullable(); 

            // ── Modificadores transversales ───────────────────────────────
            $table->boolean('exclude_from_invoice')->default(false);
            $table->boolean('requires_approval')->default(false);

            // ── Meta ──────────────────────────────────────────────────────
            $table->string('notes')->nullable();
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgical_kit_template_item_conditionals');
    }
};
