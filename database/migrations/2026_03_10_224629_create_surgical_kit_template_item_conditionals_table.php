<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgical_kit_template_item_conditionals', function (Blueprint $table) {
            $table->id();

            // ── Relación con el item ──────────────────────────────────────
            $table->unsignedBigInteger('surgical_kit_template_item_id');
            $table->foreign('surgical_kit_template_item_id', 'skt_cond_item_fk')
                  ->references('id')->on('surgical_kit_template_items')
                  ->cascadeOnDelete();

            // ── Criterios de aplicación ───────────────────────────────────
            // Solo doctor y hospital (al menos uno requerido)
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->foreign('doctor_id', 'skt_cond_doctor_fk')
                  ->references('id')->on('doctors')
                  ->nullOnDelete();

            $table->unsignedBigInteger('hospital_id')->nullable();
            $table->foreign('hospital_id', 'skt_cond_hospital_fk')
                  ->references('id')->on('hospitals')
                  ->nullOnDelete();

            // ── Tipo de acción ────────────────────────────────────────────
            // adjust_quantity | replace | add_dependency
            $table->string('action_type', 30);

            // ── Valores según action_type ─────────────────────────────────
            $table->unsignedInteger('quantity_override')->nullable();   // adjust_quantity

            $table->unsignedBigInteger('target_product_id')->nullable(); // replace | add_dependency
            $table->foreign('target_product_id', 'skt_cond_target_product_fk')
                  ->references('id')->on('products')
                  ->nullOnDelete();

            $table->unsignedInteger('dependency_quantity')->nullable();  // add_dependency

            // ── Meta ──────────────────────────────────────────────────────
            $table->string('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by', 'skt_cond_created_by_fk')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgical_kit_template_item_conditionals');
    }
};