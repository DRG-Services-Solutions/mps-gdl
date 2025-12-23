<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->comment('Folio de remisión');
            
            // Relación con cirugía
            $table->foreignId('scheduled_surgery_id')->constrained('scheduled_surgeries')->comment('Cirugía asociada');
            
            // Datos fiscales del cliente (copia para histórico)
            $table->foreignId('hospital_id')->constrained('legal_entities');
            $table->string('hospital_name')->comment('Nombre del hospital (copia)');
            $table->text('hospital_address')->nullable()->comment('Dirección (copia)');
            $table->string('hospital_rfc')->nullable()->comment('RFC (copia)');
            
            // Fecha y montos
            $table->date('invoice_date')->comment('Fecha de emisión');
            $table->decimal('subtotal', 12, 2)->comment('Subtotal');
            $table->decimal('iva', 12, 2)->comment('IVA');
            $table->decimal('total', 12, 2)->comment('Total');
            
            // Estado
            $table->enum('status', ['draft', 'issued', 'paid', 'cancelled'])->default('draft')->comment('Estado de la remisión');
            
            // Auditoría
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('invoice_number');
            $table->index('scheduled_surgery_id');
            $table->index('hospital_id');
            $table->index('invoice_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};