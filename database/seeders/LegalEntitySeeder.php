<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalEntity;

class LegalEntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $legalEntities = [
            [
            'name' => 'Distribuciones MPS',
            'razon_social' => 'Distribuciones MPS',
            'rfc' => 'DMP191216HK5',
            'address' => 'Av. Del Pinar #2850, Col. Pinar de la Calma, C.P. 45080, Zapopan Jalisco',
            'phone' => '33 1289 9257',
            'email' => 'compras@materialesparalasalud.com',
            'is_active' => true,
            ],
        
            [
            'name' => 'Mario Alberto Balcazar',
            'razon_social' => 'Mario Alberto Balcazar',
            'rfc' => 'BAGM9010209G9',
            'address' => 'Rio Ocotlan #1231, Col. Las Aguilas, C.P. 45080, Zapopan Jalisco',
            'phone' => '33 1289 9257',
            'email' => 'compras@materialesparalasalud.com',
            'is_active' => true,
            ]
        ];

        foreach($legalEntities as $legal)
        {
            LegalEntity::create($legal);
            $this->command->info("✅ Legal Entity:  {$legal['name']} creada exitosamente");
        }


    }
}