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
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('surgical_checklists')->onDelete('cascade')->comment('ID del check list');
            $table->foreignId('product_id')->constrained('products')->comment('ID del producto');
            $table->integer('quantity')->comment('Cantidad base requerida');
            $table->boolean('is_mandatory')->default(true)->comment('¿Es obligatorio?');
            $table->integer('order')->default(0)->comment('Orden de visualización');
            $table->text('notes')->nullable()->comment('Notas específicas');
            $table->timestamps();
            
            $table->index('checklist_id');
            $table->index('product_id');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
