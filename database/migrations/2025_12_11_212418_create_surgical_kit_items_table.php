<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgical_kit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgical_kit_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->comment('Producto del catálogo');
            $table->integer('quantity')->default(1)->comment('Cantidad de este producto en el kit');
            $table->text('notes')->nullable()->comment('Notas específicas del producto en este kit');
            $table->timestamps();
            
            // Evitar duplicados: un producto solo puede estar una vez por kit
            $table->unique(['surgical_kit_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgical_kit_items');
    }
};