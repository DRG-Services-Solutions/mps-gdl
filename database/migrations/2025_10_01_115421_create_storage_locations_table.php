<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_locations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Código único: ALM-01, RECEP, etc.');
            $table->string('name')->comment('Nombre descriptivo');
            $table->text('description')->nullable();
            $table->enum('type', ['warehouse', 'reception', 'quarantine', 'shipping'])->default('warehouse');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_locations');
    }
};