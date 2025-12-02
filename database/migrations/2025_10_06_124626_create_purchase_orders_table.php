<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->comment('Número de orden: PO-2025-001');
            
            // Relaciones
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('legal_entity_id')->constrained('legal_entities')->onDelete('restrict');
            $table->foreignId('sub_warehouse_id')->nullable()->constrained('sub_warehouses')->onDelete('set null');
            
            
            // Estado
            $table->enum('status', [
                'pending',          
                'received',         
                'partial',          
                'cancelled',        
                'in_return'         
            ])->default('pending');
            
            // Fechas
            $table->date('order_date')->comment('Fecha de creación de la orden');
            $table->date('expected_date')->nullable()->comment('Fecha esperada de entrega');
            $table->date('received_date')->nullable()->comment('Fecha de recepción');
            
            // Montos
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            
            // Pago
            $table->boolean('is_paid')->default(false);
            $table->date('paid_date')->nullable();
            
            // Información adicional
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'order_date']);
            $table->index('supplier_id');
            $table->index('is_paid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};