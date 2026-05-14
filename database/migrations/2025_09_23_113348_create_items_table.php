<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            
            // 1. Identificadores Base (El Catálogo)
            $table->string('code')->unique()->comment('SKU o Código Interno del Hospital');
            $table->string('name')->comment('Ej: Consola Shaver Stryker');
            $table->text('description')->nullable();
            
            $table->string('type', 30)->index();
            $table->string('manufacturer')->nullable()->comment('Ej: Arthrex, Stryker, Smith&Nephew');
            $table->enum('status', ['esteril_almacen', 'crudo_almacen', 'cirugia', 'out', 'mantenimiento'])->default('esteril_almacen');

            // 3. Control Biomédico (El Valor Agregado de tu ERP)
            $table->boolean('requires_maintenance')->default(false)->comment('¿Requiere calibración biomédica?');
            $table->integer('maintenance_interval_uses')->nullable()->comment('Ciclos de uso antes de bloquear para mantenimiento');
            
            // 4. Estados y Trazabilidad Segura
            $table->boolean('is_active')->default(true)->comment('Para ocultar del catálogo sin borrar');
            $table->timestamps();
            
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};