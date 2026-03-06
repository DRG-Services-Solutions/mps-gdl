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
        Schema::create('surgical_kit_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgical_kit_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_required')->default(1);
            $table->boolean('is_mandatory')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();$table->unique(['surgical_kit_template_id', 'product_id'], 'template_product_unique');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgical_kit_template_items');
    }
};
