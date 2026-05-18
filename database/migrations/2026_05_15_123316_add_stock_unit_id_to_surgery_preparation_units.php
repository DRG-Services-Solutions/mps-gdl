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
        Schema::table('surgery_preparation_units', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_unit_id')->nullable()->after('product_unit_id');
            $table->unsignedBigInteger('product_unit_id')->nullable()->change();

            $table->foreign('stock_unit_id')->references('id')->on('stock_units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surgery_preparation_units', function (Blueprint $table) {
            $table->dropForeign(['stock_unit_id']);
            $table->dropColumn('stock_unit_id');
            // Nota: SQLite y otros engines podrían quejarse al revertir a NOT NULL si hay registros.
        });
    }
};
