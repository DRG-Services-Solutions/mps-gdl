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
        Schema::create('inventory_summaries', function (Blueprint $table) {
            $table->id();
            //llaves foraneas
            $table->foreignId('legal_entity_id')->constrained()->onDelete('cascade');
            $table->foreignId('sub_warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            //campos unicos lote y caducidad
            $table->string('batch_number')->nullable();
            $table->date('expiration_date')->nullable();

            //campos de auditoria
            $table->decimal('quantity_on_hand', 15, 4)->default(0);
            $table->decimal('quantity_reserved', 15, 4)->default(0);
            $table->timestamps();

            

            $table->unique(['warehouse_id', 'product_id', 'lot_number'], 'unique_stock_per_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_summaries');
    }
};
