<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique()->comment('Número de recepción único');
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('storage_locations')->onDelete('cascade');
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('received_at')->comment('Fecha y hora de recepción');
            $table->enum('status', ['pending', 'partial', 'completed', 'with_issues'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('receipt_number');
            $table->index('received_at');
            $table->index('purchase_order_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_receipts');
    }
};