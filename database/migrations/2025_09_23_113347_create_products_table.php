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
            // IDENTIDAD Y CÓDIGOS DEL PRODUCTO (CATÁLOGO)
            // ==========================================================
            $table->string('name');
            $table->string('code')->unique(); // Código interno del catálogo
            $table->string('model')->nullable(); // Modelo del fabricante
            $table->text('description')->nullable();
            $table->text('specifications')->nullable(); // Especificaciones técnicas

            // ==========================================================
            // TIPO DE TRAZABILIDAD
            // ==========================================================
            $table->enum('tracking_type', ['stock', 'rfid', 'serial', 'none'])->default('stock');

            // ==========================================================
            // CARACTERÍSTICAS DEL TIPO DE PRODUCTO
            // ==========================================================
            $table->boolean('requires_sterilization')->default(false); // Instrumentales reutilizables
            $table->boolean('is_consumable')->default(false); // Consumibles de un solo uso
            $table->boolean('is_single_use')->default(false); // De un solo uso

            // ==========================================================
            // INFORMACIÓN DE INVENTARIO GENERAL
            // ==========================================================
            $table->decimal('unit_cost', 10, 2)->nullable(); // Costo unitario promedio
            $table->integer('minimum_stock')->default(0); // Stock mínimo deseado

            // ==========================================================
            // ESTADO DEL PRODUCTO EN EL CATÁLOGO
            // ==========================================================
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');

            $table->timestamps();
            $table->softDeletes();
            
            // ==========================================================
            // ÍNDICES PARA MEJORAR RENDIMIENTO
            // ==========================================================
            $table->index('code');
            $table->index('status');
            $table->index('tracking_type');
            $table->index(['category_id', 'status']);
            $table->index(['tracking_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};