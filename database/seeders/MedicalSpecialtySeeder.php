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
                'name' => 'Artroscopia de mano',
                'description' => 'cirugía mínimamente invasiva que utiliza una pequeña cámara (artroscopio) a través de incisiones pequeñas para examinar y reparar tejidos dentro o alrededor de la articulación de la mano.',
            ],
            [
                'name' => 'Artroscopia de hombro',
                'description' => 'cirugía mínimamente invasiva que utiliza una pequeña cámara (artroscopio) a través de incisiones pequeñas para examinar y reparar tejidos dentro o alrededor de la articulación del hombro.',
            ],
             [
                'name' => 'Artroscopia de Rodilla',
                'description' => 'cirugía mínimamente invasiva que utiliza una pequeña cámara (artroscopio) a través de incisiones pequeñas para examinar y reparar tejidos dentro o alrededor de la articulación de la rodilla.',
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