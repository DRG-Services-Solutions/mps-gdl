<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicalSpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            [
                'name' => 'Cirugía General',
                'description' => 'Especialidad quirúrgica que trata enfermedades del sistema digestivo, abdomen, mama, tiroides y hernias.',
            ],
            [
                'name' => 'Traumatología y Ortopedia',
                'description' => 'Especialidad dedicada al diagnóstico y tratamiento de lesiones del sistema musculoesquelético.',
            ],
            [
                'name' => 'Ginecología y Obstetricia',
                'description' => 'Especialidad médico-quirúrgica enfocada en la salud reproductiva de la mujer y el embarazo.',
            ],
            [
                'name' => 'Neurocirugía',
                'description' => 'Especialidad quirúrgica que trata enfermedades del sistema nervioso central y periférico.',
            ],
            [
                'name' => 'Cirugía Cardiovascular',
                'description' => 'Especialidad quirúrgica del corazón y grandes vasos sanguíneos.',
            ],
            [
                'name' => 'Urología',
                'description' => 'Especialidad médico-quirúrgica que trata enfermedades del aparato urinario y reproductor masculino.',
            ],
            [
                'name' => 'Oftalmología',
                'description' => 'Especialidad médico-quirúrgica dedicada al tratamiento de enfermedades oculares.',
            ],
            [
                'name' => 'Otorrinolaringología',
                'description' => 'Especialidad médico-quirúrgica de oído, nariz y garganta.',
            ],
            [
                'name' => 'Cirugía Plástica y Reconstructiva',
                'description' => 'Especialidad quirúrgica enfocada en la reconstrucción y estética corporal.',
            ],
            [
                'name' => 'Cirugía Pediátrica',
                'description' => 'Especialidad quirúrgica dedicada al tratamiento de enfermedades en pacientes pediátricos.',
            ],
            [
                'name' => 'Anestesiología',
                'description' => 'Especialidad médica enfocada en el manejo del dolor y sedación durante procedimientos quirúrgicos.',
            ],
            [
                'name' => 'Cirugía Torácica',
                'description' => 'Especialidad quirúrgica del tórax, pulmones, esófago y mediastino.',
            ],
            [
                'name' => 'Cirugía Maxilofacial',
                'description' => 'Especialidad quirúrgica de cara, cráneo, cuello y cavidad oral.',
            ],
            [
                'name' => 'Cirugía Vascular',
                'description' => 'Especialidad quirúrgica del sistema vascular arterial y venoso.',
            ],
            [
                'name' => 'Coloproctología',
                'description' => 'Especialidad quirúrgica del colon, recto y ano.',
            ],
        ];

        $now = Carbon::now();
        
        foreach ($specialties as &$specialty) {
            $specialty['created_at'] = $now;
            $specialty['updated_at'] = $now;
        }

        DB::table('medical_specialties')->insert($specialties);
    }
}