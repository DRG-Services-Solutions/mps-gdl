<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_assembled_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('pre_assembled_packages')->onDelete('cascade')->comment('ID del paquete pre-armado');
            $table->enum('status', ['active', 'used', 'damaged'])
                  ->default('active');


            $table->foreignId('product_id')->constrained('products')->comment('ID del producto');

            $table->foreignId('product_unit_id')->constrained('product_units')->comment('EPC específico del producto');

            $table->integer('quantity')->default(1)->comment('Cantidad (normalmente 1 por EPC)');
            
            $table->dateTime('added_at')->comment('Fecha cuando se agregó al paquete');
            $table->foreignId('added_by')->constrained('users');
            
            // Control de caducidad y PEPS
            $table->date('expiration_date')->nullable()->comment('Fecha de caducidad del producto');
            $table->date('entry_date')->nullable()->comment('Fecha de entrada para PEPS');
            
            $table->timestamps();
            
            // Índices
            $table->index('package_id');
            $table->index('product_id');
            $table->index('product_unit_id');
            $table->index('expiration_date');
            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_assembled_contents');
    }
};