<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgical_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Código único del check list');
            $table->string('surgery_type')->comment('Tipo de cirugía');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            // Índices
            $table->index('code');
            $table->index('surgery_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgical_checklists');
    }
};