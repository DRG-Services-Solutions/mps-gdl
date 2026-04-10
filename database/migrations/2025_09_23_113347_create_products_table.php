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

            // Clasificación
            $table->foreignId('product_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();            
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();

            // Identidad
            $table->string('name');
            $table->string('code')->unique(); 

            //composición SET/EQUIPO
            $table->boolean('is_composite')->default(false);

            //Reglas fisicas y medicas
            $table->boolean('requires_sterilization')->default(0);
            $table->boolean('requires_refrigeration')->default(0);
            $table->boolean('requires_temperature')->default(0);
            $table->boolean('has_expiration_date')->default(false);
            $table->enum('tracking_type', ['code', 'rfid', 'serial', 'lote'])->default('code');

         

            // ==========================================================
            // INFORMACIÓN DE INVENTARIO GENERAL
            // ==========================================================
            
            $table->decimal('list_price', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);

            // ==========================================================
            // ESTADO DEL PRODUCTO EN EL CATÁLOGO
            // ==========================================================
            $table->enum('status', ['active', 'reserved', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();
            
            // ==========================================================
            // ÍNDICES PARA MEJORAR RENDIMIENTO
            // ==========================================================
            $table->index('code');
            $table->index('status');
            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};