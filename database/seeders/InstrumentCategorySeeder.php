<?php

namespace Database\Seeders;

use App\Models\InstrumentCategory;
use Illuminate\Database\Seeder;

class InstrumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Instrumentales', 'slug' => 'instrumentales', 'description' => 'Instrumentos quirúrgicos individuales (pinzas, separadores, mangos, etc.)'],
            ['name' => 'Kit de Instrumentales', 'slug' => 'kit-instrumentales', 'description' => 'Cajas y sets que contienen múltiples instrumentos'],
            ['name' => 'Equipos', 'slug' => 'equipos', 'description' => 'Equipos y aparatos médicos (motores, torres, cámaras, etc.)'],
        ];

        foreach ($categories as $category) {
            InstrumentCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}