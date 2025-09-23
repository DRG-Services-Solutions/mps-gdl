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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->nullable()->constrained();
            $table->foreignId('surgical_set_id')->nullable()->constrained();
            $table->foreignId('surgical_procedure_id')->nullable()->constrained();
            
            $table->enum('movement_type', ['in', 'out', 'transfer', 'adjustment', 'sterilization']);
            $table->enum('reason', [
                'purchase', 'surgery_use', 'sterilization', 'maintenance', 
                'transfer', 'adjustment', 'expired', 'damaged', 'lost'
            ]);
            
            $table->integer('quantity');
            $table->integer('previous_stock')->default(0);
            $table->integer('new_stock')->default(0);
            $table->string('location_from')->nullable();
            $table->string('location_to')->nullable();
            $table->string('responsible_user');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
