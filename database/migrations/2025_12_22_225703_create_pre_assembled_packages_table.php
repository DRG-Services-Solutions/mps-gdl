<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_assembled_packages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Código único del paquete');
            $table->string('name')->comment('Nombre descriptivo');

            $table->foreignId('surgery_checklist_id')->constrained('surgical_checklists')->comment('Tipo de cirugía/check list');

            // RFID del paquete completo (contenedor)
            $table->string('package_epc')->unique()->nullable()->comment('EPC del contenedor/caja del paquete');
            
            // Estados
            $table->enum('status', ['available', 'in_preparation', 'in_surgery', 'maintenance'])->default('available')->comment('Estado del paquete');
            
            // Ubicación física
            $table->foreignId('storage_location_id')->nullable()->constrained('storage_locations')->comment('Ubicación en área de pre-armados');
            
            // DAtos de uso
            $table->dateTime('last_used_at')->nullable()->comment('Última vez usado');
            $table->integer('times_used')->default(0)->comment('Veces usado');
            
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            //indices
            $table->index('code');
            $table->index('surgery_checklist_id');
            $table->index('status');
            $table->index('package_epc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_assembled_packages');
    }
};