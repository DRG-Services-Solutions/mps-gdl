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
        Schema::create('scheduled_surgery_additional_items', function (Blueprint $table) {
            $table->id();
            
            // Cirugía padre
            $table->foreignId('scheduled_surgery_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Producto (Insumo)
            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained('products')
                  ->onDelete('cascade');
            
            // Instrumento Individual
            $table->foreignId('instrument_id')
                  ->nullable()
                  ->constrained('instruments')
                  ->onDelete('cascade');
            
            // Kit de Instrumentos
            $table->foreignId('instrument_kit_id')
                  ->nullable()
                  ->constrained('instrument_kits') 
                  ->onDelete('cascade');
            
            $table->integer('quantity')->default(1);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_surgery_additional_items');
    }
};