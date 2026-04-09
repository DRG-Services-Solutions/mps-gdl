<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_contents', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('pre_assembled_package_id')
                ->constrained('pre_assembled_packages')
                ->onDelete('cascade');
            
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('restrict');
            
            $table->foreignId('product_unit_id')
                ->nullable()
                ->constrained('product_units')
                ->onDelete('set null');
            
            $table->integer('quantity')->default(1);
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_contents');
    }
};