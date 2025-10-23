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
            $table->string('area', 50); 
            $table->string('organizer', 10);
            $table->unsignedSmallInteger('shelf_level');
            $table->unsignedSmallInteger('shelf_section');
            $table->unique(['area', 'organizer', 'shelf_level', 'shelf_section'], 'unique_full_location'); 
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_locations');
    }
};