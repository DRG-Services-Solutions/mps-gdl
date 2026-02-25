<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            // Estado actual del producto
            $table->enum('current_status', [
                'in_stock',         // En almacén general
                'in_pre_assembled', // En un pre-armado
                'in_surgery',       // En cirugía
                'consumed',         // Consumido/vendido
                'returned',         // Devuelto
                'maintenance'       // Mantenimiento
            ])->default('in_stock')->after('status');
            
            // Si está en pre-armado, en cuál
            $table->foreignId('current_package_id')->nullable()->constrained('pre_assembled_packages')->after('current_status')->comment('Paquete actual (si está en pre-armado)');
            
            // Si está en cirugía, en cuál
            $table->foreignId('current_surgery_id')->nullable()->constrained('scheduled_surgeries')->after('current_package_id')->comment('Cirugía actual (si está en cirugía)');
            
            // Índices
            $table->index('current_status');
            $table->index('current_package_id');
            $table->index('current_surgery_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropForeign(['current_package_id']);
            $table->dropForeign(['current_surgery_id']);
            $table->dropIndex(['current_status']);
            $table->dropIndex(['current_package_id']);
            $table->dropIndex(['current_surgery_id']);
            $table->dropColumn(['current_status', 'current_package_id', 'current_surgery_id']);
        });
    }
};