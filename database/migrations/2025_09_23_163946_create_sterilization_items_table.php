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
        Schema::create('sterilization_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sterilization_process_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained();
            $table->foreignId('surgical_set_id')->nullable()->constrained();
            
            $table->enum('item_status', ['pending', 'sterilized', 'failed']);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sterilization_items');
    }
};
