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
        Schema::create('product_components', function (Blueprint $table) {
            $table->id();

            // El Set o Equipo (Padre)
            $table->foreignId('parent_product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            // Pieza o consumible que va en el set o equipo (Hijo)
            $table->foreignId('child_product_id')->constrained('products')->cascadeOnDelete();

            // cantidad de piezas o consumibles que van en el set o equipo
            $table->integer('quantity')->default(1);

            $table->unique(['parent_product_id', 'child_product_id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_components');
    }
};
