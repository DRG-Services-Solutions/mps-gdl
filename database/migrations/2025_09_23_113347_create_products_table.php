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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained();
            $table->foreignId('medical_specialty_id')->constrained();
            $table->foreignId('specialty_subcategory_id')->nullable()->constrained();


            $table->string('name');
            $table->string('code')->unique();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('description')->nullable();

            $table->boolean('rfid_enabled')->default(false);
            $table->string('rfid_tag_id')->nullable()->unique();
            $table->boolean('requires_sterilization')->default(false);
            $table->boolean('is_consumable')->default(false);
            $table->boolean('is_single_use')->default(false);

            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->integer('minimum_stock')->default(0);
            $table->integer('current_stock')->default(0);
            $table->string('storage_location')->nullable();


            $table->date('expiration_date')->nullable();
            $table->string('lot_number')->nullable();
            $table->json('specifications')->nullable(); 
            $table->enum('status', ['active', 'inactive', 'maintenance', 'retired'])->default('active');

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
