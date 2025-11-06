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
        Schema::create('product_layouts', function (Blueprint $table) {
            $table->id();
            //Claves Foraneas
            $table->foreignId('storage_location_id')
                  ->constrained()  //Asumimos el nombre de la tabla de la llave foranea (storage_locations)
                  ->onDelete('cascade')
                  ->comment('Ubicacion principal de la bodega.');

            $table->foreignId('product_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->comment('Producto Asociado a esta ubicacion');

            //Campos de Ubicacion
            $table->unsignedSmallInteger('shelf')->comment('Numero de estante');
            $table->char('level', 2)->comment('Nivel de estante');
            $table->decimal('position', 8, 2)->comment('Posicion dentro del estante y nivel');

            $table->timestamps();

            //indice unico compuesto
            $table->unique(
                ['storage_location_id', 'shelf', 'level', 'position'],
                'unique_layout_position'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_layouts');
    }
};
