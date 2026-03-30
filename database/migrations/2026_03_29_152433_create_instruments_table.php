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
        Schema::create('instruments', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();
            $table->string('name');
            $table->string('code', 30)->nullable();
            $table->foreignId('category_id')->constrained('instrument_categories')->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('kit_id')->nullable()->constrained('instrument_kits')->nullOnDelete();
            $table->foreignId('depends_on_id')->nullable()->constrained('instruments')->nullOnDelete();
            $table->enum('status', ['available', 'in_kit', 'in_surgery', 'maintenance', 'retired', 'lost'])->default('available');
            $table->enum('condition', ['good', 'fair', 'damaged'])->default('good');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['status', 'category_id']);
            $table->index('kit_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instruments');
    }
};
