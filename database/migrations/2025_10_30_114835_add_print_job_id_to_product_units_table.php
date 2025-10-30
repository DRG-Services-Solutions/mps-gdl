<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->foreignId('print_job_id')->nullable()->after('expiration_date')->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropForeign(['print_job_id']);
            $table->dropColumn('print_job_id');
        });
    }
};