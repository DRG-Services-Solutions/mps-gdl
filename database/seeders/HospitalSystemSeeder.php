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

        //hospital 1 de prueba
        $hospital1 = Hospital::create([
            'name' => 'CENTRO HOSPITALARIO MAC  S.A. DE C.V.',
            'rfc' => 'CHO0801174Z5',
            'is_active' => true
        ]);

        //  Hospitales
        $hospital =([
            [
                'name' => 'AMERICAS HOSPITAL SA DE CV',
                'rfc' => 'AHO960911E71',
                'is_active' => true
            ],
        
            [
                'name' => 'Hospital General San José',
                'rfc' => 'HGS900909H12',
                'is_active' => true
            ],
        ]);

        foreach ($hospital as $hospitalData) {
            Hospital::create($hospitalData);
        }

        // 4. Crear la Configuración Híbrida 
        // Ejemplo: Este hospital usa la Entidad 1 para Seguros y la Entidad 2 para Particulares
        HospitalModalityConfig::create([
            'hospital_id' => $hospital1->id,
            'modality_id' => $seguro->id,
            'legal_entity_id' => "1",
        ]);

        HospitalModalityConfig::create([
            'hospital_id' => $hospital1->id,
            'modality_id' => $particular->id,
            'legal_entity_id' => "2"
            
        ]);
    }
}