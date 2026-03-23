<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Quitar la FK existente
        Schema::table('surgery_preparations', function (Blueprint $table) {
            $table->dropForeign(['pre_assembled_package_id']);
        });

        // 2. Hacer la columna nullable vía SQL directo (sin doctrine/dbal)
        DB::statement('ALTER TABLE surgery_preparations MODIFY pre_assembled_package_id BIGINT UNSIGNED NULL');

        // 3. Recrear la FK nullable
        Schema::table('surgery_preparations', function (Blueprint $table) {
            $table->foreign('pre_assembled_package_id')
                ->references('id')
                ->on('pre_assembled_packages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surgery_preparations', function (Blueprint $table) {
            $table->dropForeign(['pre_assembled_package_id']);
        });

        DB::statement('ALTER TABLE surgery_preparations MODIFY pre_assembled_package_id BIGINT UNSIGNED NOT NULL');

        Schema::table('surgery_preparations', function (Blueprint $table) {
            $table->foreign('pre_assembled_package_id')
                ->references('id')
                ->on('pre_assembled_packages')
                ->cascadeOnDelete();
        });
    }
};
