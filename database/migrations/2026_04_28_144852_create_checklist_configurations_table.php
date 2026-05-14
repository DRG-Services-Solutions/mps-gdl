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
        Schema::create('checklist_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgical_checklist_id')
                  ->constrained('surgical_checklists')
                  ->cascadeOnDelete();
            $table->string('name')->comment('Ej: Torre Arthrex 4K');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_configurations');
    }
};
