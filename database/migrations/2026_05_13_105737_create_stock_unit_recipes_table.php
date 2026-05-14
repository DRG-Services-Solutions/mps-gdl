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
        Schema::create('stock_unit_recipes', function (Blueprint $table) {
            $table->id();
            
            // EL CAMBIO MAESTRO: El padre ahora es la Unidad Física (Ej: IN-MPS-2230)
            $table->foreignId('stock_unit_id')->constrained('stock_units')->cascadeOnDelete(); 
            
            // El hijo sigue siendo el catálogo lógico (Ej: "Consola Shaver Stryker")
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();  
            
            $table->integer('quantity')->default(1);
            
            // Mantenemos la genialidad de las reglas condicionales
            $table->enum('requirement_type', ['mandatory', 'optional', 'conditional'])->default('mandatory');
            $table->json('condition_rules')->nullable(); 
            $table->string('notes')->nullable(); 
            
            $table->timestamps();
            
            // Una unidad física no debería tener dos veces el mismo modelo en líneas separadas (se suma la cantidad)
            $table->unique(['stock_unit_id', 'item_id']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_unit_recipes');
    }
};
