<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgery_preparation_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preparation_item_id')->constrained('surgery_preparation_items')->onDelete('cascade')->comment('Item de preparación');

            $table->foreignId('product_unit_id')->constrained('product_units')->comment('EPC/Serial específico asignado');

            // Origen de la unidad
            $table->enum('source_type', ['pre_assembled', 'warehouse'])->comment('pre_assembled=del paquete, warehouse=surtido del almacén');

            $table->foreignId('source_package_id')->nullable()->constrained('pre_assembled_packages')->comment('Paquete de origen (si aplica)');
            
            // Fechas
            $table->dateTime('assigned_at');
            $table->foreignId('assigned_by')->constrained('users');
            
            $table->timestamps();
            
            // Índices
            $table->index('preparation_item_id');
            $table->index('product_unit_id');
            $table->index('source_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgery_preparation_units');
    }
};