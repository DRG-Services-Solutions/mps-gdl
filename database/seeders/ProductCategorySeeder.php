<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'OSTEOSINTESIS', 'description' => ''],
            ['name' => 'PLACAS HUMERO PROXIMAL BLOQUEADAS', 'description' => ''],
            ['name' => 'PLACAS OLECRANON BLOQUEADAS', 'description' => ''],
            ['name' => 'PLACAS EN T BLOQUEADAS', 'description' => ''],
            ['name' => 'PLACAS EN L BLOQUEADAS', 'description' => ''],
            ['name' => 'PLACAS FEMUR PROXIMAL', 'description' => ''],
            ['name' => 'PLACAS DE RECONSTRUCCION PELVIS BLOQUEADAS', 'description' => ''],
            ['name' => 'PLACAS DE CONTACTO LIMITADO ANCHA', 'description' => ''],
            ['name' => 'PLACAS DE CONTACTO LIMITADO ANGOSTAS', 'description' => ''],
            ['name' => 'PLACAS TIBIA DISTAL MEDIAL', 'description' => ''],
            ['name' => 'PLACAS PEQUEOS FRAGMENTOS', 'description' => ''],
            ['name' => 'PLACAS DE RECONSTRUCCION BLOQUEADAS', 'description' => ''],
            ['name' => 'PLACAS 1/3 CAA BLOQUEADAS', 'description' => ''],
            ['name' => 'PLACAS CLAVICULA BLOQUEADAS', 'description' => ''],
            ['name' => 'PLACAS FEMUR DISTAL', 'description' => ''],
            ['name' => 'PLACAS TIBIAL LATERAL', 'description' => ''],
            ['name' => 'PLACAS DE RADIO', 'description' => ''],
            ['name' => 'PLACAS CLAVICULA DISTAL BLOQUEADAS', 'description' => ''],
            ['name' => 'PLACA PATELLA', 'description' => ''],
            ['name' => 'PLACAS PERONE BLOQUEADAS', 'description' => ''],
            ['name' => 'TORNILLO CORTICAL 3.5 MM', 'description' => ''],
            ['name' => 'TORNILLO ESPONJOSO ROSCA COMPLETA 4.0 MM', 'description' => ''],
            ['name' => 'TORNILLO CORTICAL 4.5', 'description' => ''],
            ['name' => 'TORNILLO ESPONJOSO 6.5', 'description' => ''],
            ['name' => 'TORNILLO BLOQUEADO 3.5 MM', 'description' => ''],
            ['name' => 'TORNILLO BLOQUEADO 5.0', 'description' => ''],
            ['name' => 'TORNILLO CORTICAL 2.7', 'description' => ''],
            ['name' => 'TORNILLO BLOQUEADO 2.4', 'description' => ''],
            ['name' => 'TORNILLO BLOQUEADO VARIABLE 2.4', 'description' => ''],
            ['name' => 'TORNILLO CANULADO 6.5', 'description' => ''],
            ['name' => 'TORNILLO CANULADO DOBLE COMPRESION 2,5MM', 'description' => ''],
            ['name' => 'TORNILLO CANULADO DOBLE COMPRESION 3,5MM', 'description' => ''],
            ['name' => 'TORNILLO CANULADO DOBLE COMPRESION 4,0MM', 'description' => ''],
            ['name' => 'CLAVO TIBIA', 'description' => ''],
            ['name' => 'CLAVO FEMUR CANULADO', 'description' => ''],
            ['name' => 'CLAVO PROXIMAL FEMORAL', 'description' => ''],
            ['name' => 'CLAVO HUMERO', 'description' => ''],
            ['name' => 'CLAVO FEMUR DISTAL', 'description' => ''],
            ['name' => 'FLEXIBLE REAMER', 'description' => ''],
            ['name' => 'GENERAL', 'description' => ''],
            ['name' => 'HOMBRO', 'description' => ''],
            ['name' => 'RODILLA', 'description' => ''],
            ['name' => 'PERONE', 'description' => ''],
            ['name' => 'PLACA CLAVICULA', 'description' => ''],
            ['name' => 'PLACA HOCKEY', 'description' => ''],
            ['name' => 'OLECRANON', 'description' => ''],
            ['name' => 'PLACAS RECTAS', 'description' => ''],
            ['name' => 'TERCIO CAÑA', 'description' => ''],
            ['name' => 'TIBIA DISTAL', 'description' => ''],
            ['name' => 'TIBIA PROXIMAL', 'description' => ''],
            ['name' => 'CANULADOS 4.0', 'description' => ''],
            ['name' => 'ARTROSCOPIA', 'description' => ''],
            ['name' => 'HALLUX', 'description' => ''],
            ['name' => 'CANULADOS 4.0 MM', 'description' => ''],
            ['name' => 'CORONOIDE', 'description' => ''],
            ['name' => 'CLAVICULA', 'description' => ''],
            ['name' => 'SPEEDTIP', 'description' => ''],
            ['name' => 'PIE', 'description' => ''],
            ['name' => 'CODO', 'description' => ''],
            ['name' => 'TOBILLO', 'description' => ''],
            ['name' => 'CALCANEO', 'description' => ''],
            ['name' => 'MANO', 'description' => ''],
            ['name' => 'PROTESIS', 'description' => ''],
            ['name' => 'PEQUEÑAS ARTICULACIONES', 'description' => ''],
            ['name' => 'ARTRODESIS MUÑECA', 'description' => ''],
            ['name' => 'RADIO', 'description' => ''],
            ['name' => 'CANULADOS 2 Y 3', 'description' => ''],
            ['name' => 'CADERA', 'description' => ''],
            ['name' => 'CANULADOS 5.0', 'description' => ''],
            ['name' => 'CANULADOS MEDARITS 1.7', 'description' => ''],
            ['name' => 'CANULADOS 7.0', 'description' => ''],
            ['name' => 'CANULADOS 1.7', 'description' => ''],
            ['name' => 'PLACA PHILOS', 'description' => ''], // Último elemento sin coma final
        ];

        $now = Carbon::now();

        foreach($categories as $category)
        {
            $category['created_at'] = $now;
            $category['updated_at'] = $now;
        }

        DB::table('product_categories')->insert($categories);
    }
}
