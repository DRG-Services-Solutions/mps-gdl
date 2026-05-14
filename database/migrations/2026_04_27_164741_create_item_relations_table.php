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
        Schema::create('item_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('related_item_id')->constrained('items')->cascadeOnDelete();
            $table->enum('type', ['required', 'suggested', 'compatible'])->default('compatible');
            $table->string('notes')->nullable(); 
            $table->timestamps();

            $table->unique(['item_id', 'related_item_id']);

            $table->index('related_item_id');
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_relations');
    }
};
