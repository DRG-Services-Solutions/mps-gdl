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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();

            // ¿A qué catálogo pertenece esta pieza física?
            $table->foreignId('product_id')->constrained('products');

            // Jerarquía Física: ¿Esta pieza está DENTRO de otra caja o set físico?
            $table->foreignId('parent_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();

            // Trazabilidad (Los códigos que escanea el almacenista)
            $table->string('serial_number')->unique()->nullable();
            $table->string('lot_number')->nullable();
            $table->string('rfid_tag')->unique()->nullable();

            // Ciclo de Vida
            $table->date('expiration_date')->nullable();
            $table->integer('quantity')->default(1);

            // Estado Físico
            $table->enum('status', [
                'disponible',       
                'incompleto',       
                'reservado',        
                'en_quirofano',     
                'en_esterilizacion',
                'baja'              
            ])->default('disponible');

            $table->timestamps();
            $table->softDeletes();

        });

        //Relaciones
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
