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

            //Product Types
            $table->foreignId('product_type_id')
                  ->constrained('product_types')
                  ->cascadeOnDelete();
            
            //===================
            //  BRANDS

            $table->foreignId('brand_id')
                  ->nullable()
                  ->constrained('brands')
                  ->nullOnDelete();
            
            // ==========================================================
            // CLAVES FORÁNEAS (CLASIFICACIÓN)
            // ==========================================================
          // Migración de products (¡Correcta!)
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            // ==========================================================
            // IDENTIDAD Y CÓDIGOS DEL PRODUCTO (CATÁLOGO)
            // ==========================================================
            $table->string('name');
            $table->string('code')->unique(); 
            $table->boolean('requires_sterilization')->default(0);
            $table->boolean('requires_refrigeration')->default(0);
            $table->boolean('requires_temperature')->default(0);

            // ==========================================================
            // TIPO DE TRAZABILIDAD
            // ==========================================================
            $table->enum('tracking_type', ['code', 'rfid', 'lote'])->default('code');

            
            // ==========================================================
            // INFORMACIÓN DE INVENTARIO GENERAL
            // ==========================================================
            
            $table->integer('minimum_stock')->default(0);
            $table->decimal('list_price', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);

            // ==========================================================
            // ESTADO DEL PRODUCTO EN EL CATÁLOGO
            // ==========================================================
            $table->enum('status', ['active', 'reservado', 'inactivo'])->default('active');

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