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
        Schema::create('kit_assemblies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_unit_id')->constrained()->cascadeOnDelete(); 
            $table->foreignId('user_id')->constrained();       
            $table->enum('status', ['in_progress', 'completed', 'with_discrepancies'])->default('in_progress');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kit_assemblies');
    }
};
