<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surgical_kits', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Código único del prearmado');
            $table->string('name')->comment('Nombre del prearmado');
            $table->string('surgery_type')->comment('Tipo de cirugía asociada');
            $table->text('description')->nullable()->comment('Descripción del prearmado');
            $table->boolean('is_active')->default(true)->comment('Si el prearmado está activo');
            $table->foreignId('created_by')->nullable()->constrained('users')->comment('Usuario que creó el prearmado');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('surgery_type');
            $table->index('is_active');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgical_kits');
    }
};
