<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // ==========================================================
            // CLAVES FORÁNEAS (CLASIFICACIÓN)
            // ==========================================================

            $table->foreignId('manufacturer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete(); 
            $table->foreignId('subcategory_id')->nullable()->constrained()->nullOnDelete(); 
            $table->foreignId('specialty_id')->nullable()->constrained('medical_specialties')->nullOnDelete(); 

            // ==========================================================
            // IDENTIDAD Y CÓDIGOS
            // ==========================================================

            $table->string('name');
            $table->string('code')->unique();

            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('description')->nullable();

            // ==========================================================
            // GESTIÓN E INVENTARIO
            // ==========================================================

            // Características de la Identidad de Producto (Tipo)
            $table->boolean('requires_sterilization')->default(false);
            $table->boolean('is_consumable')->default(false);
            $table->boolean('is_single_use')->default(false);

            // Trazabilidad (RFID)
            $table->boolean('rfid_enabled')->default(false);
            $table->string('rfid_tag_id')->nullable()->unique(); // Tag UID o Identificador

            // Stock
            $table->decimal('unit_cost', 10, 2)->nullable(); 
            $table->integer('minimum_stock')->default(0);
            $table->integer('current_stock')->default(0); 
            $table->string('storage_location')->nullable();

            // Lote y Caducidad
            $table->date('expiration_date')->nullable();
            $table->string('lot_number')->nullable();
            $table->longText('specifications')->nullable(); 
            // Estado y Tiempos
            $table->enum('status', ['active', 'inactive', 'maintenance', 'retired'])->default('active');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};