<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solo agregar columnas si no existen (por si la tabla ya fue modificada manualmente)
        Schema::table('surgical_kits', function (Blueprint $table) {
            if (!Schema::hasColumn('surgical_kits', 'name')) {
                $table->string('name', 255)->after('id');
            }
            if (!Schema::hasColumn('surgical_kits', 'code')) {
                $table->string('code', 50)->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('surgical_kits', 'surgery_type')) {
                $table->string('surgery_type', 255)->after('code');
            }
            if (!Schema::hasColumn('surgical_kits', 'description')) {
                $table->text('description')->nullable()->after('surgery_type');
            }
            if (!Schema::hasColumn('surgical_kits', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
            if (!Schema::hasColumn('surgical_kits', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surgical_kits', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropIndex(['surgery_type']);
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'name', 'code', 'surgery_type', 'description', 'is_active', 'created_by',
            ]);
        });
    }
};
