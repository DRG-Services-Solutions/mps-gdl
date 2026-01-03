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
        Schema::create('hospital_modality_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->onDelete('cascade');
            $table->foreignId('modality_id')->constrained()->onDelete('cascade');
            $table->foreignId('legal_entity_id')->constrained('legal_entities'); 
            $table->timestamps();

            $table->unique(['hospital_id', 'modality_id'], 'hosp_mod_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_hospital_modality_configs');
    }
};
