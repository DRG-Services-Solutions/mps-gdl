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
        Schema::create('kit_assembly_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_assembly_id')->constrained()->cascadeOnDelete();
            
            // El modelo de instrumento esperado en la receta (Ej: Pinza mosquito)
            $table->foreignId('component_item_id')->constrained('items'); 
            
            $table->integer('quantity_expected'); 
            $table->integer('quantity_found'); 
            
            // Si el operario escaneó con el lector de código de barras los grabados láser (DPM) de cada pinza
            $table->json('serial_numbers')->nullable()->comment('Grabados láser DPM escaneados individualmente');        
            
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kit_assembly_items');
    }
};
