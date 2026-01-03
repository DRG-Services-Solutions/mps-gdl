<?php

namespace Database\Seeders;

use App\Models\Modality;
use App\Models\LegalEntity;
use App\Models\Hospital;
use App\Models\HospitalModalityConfig;
use Illuminate\Database\Seeder;

class HospitalSystemSeeder extends Seeder
{
    public function run(): void
    {
        
        // Modalidades
        $seguro = Modality::create(['name' => 'Seguro']);
        $particular = Modality::create(['name' => 'Particular']);

        //  Hospital
        $hospital = Hospital::create([
            'name' => 'Hospital General San José',
            'rfc' => 'HGS900909H12',
            'is_active' => true
        ]);

        // 4. Crear la Configuración Híbrida 
        // Ejemplo: Este hospital usa la Entidad 1 para Seguros y la Entidad 2 para Particulares
        HospitalModalityConfig::create([
            'hospital_id' => $hospital->id,
            'modality_id' => $seguro->id,
            'legal_entity_id' => "1",
        ]);

        HospitalModalityConfig::create([
            'hospital_id' => $hospital->id,
            'modality_id' => $particular->id,
            'legal_entity_id' => "2"
            
        ]);
    }
}