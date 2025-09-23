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
        Schema::create('procedure_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgical_procedure_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained();
            $table->foreignId('surgical_set_id')->nullable()->constrained();
            
            $table->integer('quantity_used')->default(1);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->datetime('assigned_at');
            $table->datetime('returned_at')->nullable();
            $table->enum('return_status', ['returned', 'consumed', 'damaged', 'lost'])->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_products');
    }
};
