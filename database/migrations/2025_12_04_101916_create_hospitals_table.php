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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();

            // Informacion Basica
            $table->string('name', 255);

            //Contacto
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();

            //Direccion
            $table->text('address')->nullable();
            $table->string('rfc')->nullable();
            $table->string('razon_social')->nullable();

            $table->timestamps();

            //Indices
            $table->index('name');
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
