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
        Schema::create('sub_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_entity_id')
            ->constrained('legal_entities')
            ->onDelete('cascade');

            $table->string('name');
            $table->text('description')->nullable();
            
            
            $table->boolean('is_active')->default(true);
            
            // No permitir nombres duplicados dentro de la misma entidad legal
            $table->unique(['legal_entity_id', 'name']);

            $table->timestamps();
            $table->index(['legal_entity_id', 'is_active']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_warehouses');
    }
};
