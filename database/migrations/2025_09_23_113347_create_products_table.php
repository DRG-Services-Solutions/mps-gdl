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
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete(); // almacena los tipos de productos
            $table->foreignId('subcategory_id')->nullable()->constrained()->nullOnDelete(); // almacena los subprodcutos
            $table->foreignId('specialty_id')->nullable()->constrained('medical_specialties')->nullOnDelete(); 

            // ==========================================================
            // IDENTIDAD Y CÓDIGOS DEL PRODUCTO (CATÁLOGO)
            // ==========================================================
            $table->string('name');
            $table->string('code')->unique(); // Código interno del catálogo
            $table->text('description')->nullable();
           

            // ==========================================================
            // TIPO DE TRAZABILIDAD
            // ==========================================================
            $table->enum('tracking_type', ['code', 'rfid', 'serial'])->default('code');

            
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