<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preparation_scanned_units', function (Blueprint $table) {
            $table->id();

            $table->foreignId('surgery_preparation_id')
                ->constrained('surgery_preparations')
                ->cascadeOnDelete()
                ->comment('Preparación a la que pertenece el escaneo');

            $table->foreignId('product_unit_id')
                ->constrained('product_units')
                ->cascadeOnDelete()
                ->comment('Unidad física escaneada');

            $table->foreignId('product_id')
                ->constrained('products')
                ->comment('Producto (para consulta rápida)');

            $table->foreignId('scanned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que escaneó');

            $table->timestamp('scanned_at')
                ->useCurrent()
                ->comment('Momento del escaneo');

            $table->timestamps();

            // Evitar duplicados: misma unidad en misma preparación
            $table->unique(['surgery_preparation_id', 'product_unit_id'], 'prep_unit_unique');

            $table->index('surgery_preparation_id');
            $table->index('product_unit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preparation_scanned_units');
    }
};
