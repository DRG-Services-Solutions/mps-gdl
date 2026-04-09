<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique()->index();
            $table->foreignId('receipt_id')->constrained('purchase_order_receipts')->onDelete('cascade');
            $table->foreignId('product_unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('printer_name')->default('zebra_rfid_01');
            $table->text('zpl_commands');
            $table->string('epc_code', 24)->index(); // Código hex del RFID
            $table->json('label_data'); // producto, lote, caducidad, etc
            $table->enum('status', ['pending', 'printing', 'completed', 'failed', 'cancelled'])->default('pending')->index();
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};