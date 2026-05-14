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
        Schema::create('stock_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            
            // Solo nos quedamos con el número de serie o grabado láser DPM
            $table->string('serial_number')->index(); 
            
            $table->enum('status', ['sterile', 'dirty', 'in_process', 'in_surgery', 'maintenance', 'retired', 'implanted'])->default('sterile')->index();
            $table->foreignId('current_surgery_id')->nullable()->constrained('scheduled_surgeries')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('storage_locations')->nullOnDelete();
            $table->integer('total_uses')->default(0);
            $table->timestamp('last_maintenance_at')->nullable();
            $table->timestamp('sterilization_expires_at')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'serial_number']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_units');
    }
};
