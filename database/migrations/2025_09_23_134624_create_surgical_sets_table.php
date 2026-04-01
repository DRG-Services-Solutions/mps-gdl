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
        Schema::create('surgical_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('requires_sterilization')->default(true);
            $table->boolean('rfid_enabled')->default(false);
            $table->string('rfid_tag_id')->nullable()->unique();
            $table->enum('status', ['available', 'in_use', 'sterilization', 'maintenance'])->default('available');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgical_sets');
    }
};
