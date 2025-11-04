<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubcategorySeeder extends Seeder
{
    public function run(): void
    {
        // Primero obtenemos las categorías para relacionarlas
        $categories = DB::table('product_categories')->pluck('id', 'name');
        
        $subcategories = [
            // Instrumental Quirúrgico
            ['category' => 'Instrumental Quirúrgico', 'name' => 'Tijeras Quirúrgicas', 'description' => 'Tijeras Mayo, Metzenbaum, iris y de disección'],
            ['category' => 'Instrumental Quirúrgico', 'name' => 'Bisturís', 'description' => 'Mangos de bisturí y hojas desechables'],
            
            // Material de Curación
            ['category' => 'Consumibles Quirúrgicos', 'name' => 'Gasas Estériles', 'description' => 'Gasas de diferentes tamaños en presentación estéril'],
            ['category' => 'Consumibles Quirúrgicos', 'name' => 'Compresas', 'description' => 'Compresas quirúrgicas de diferentes tamaños'],
            
            // Guantes Quirúrgicos
            ['category' => 'Consumibles Quirúrgicos', 'name' => 'Guantes de Látex', 'description' => 'Guantes estériles de látex natural'],
            ['category' => 'Consumibles Quirúrgicos', 'name' => 'Guantes de Nitrilo', 'description' => 'Guantes estériles libres de látex'],
            ['category' => 'Consumibles Quirúrgicos', 'name' => 'Guantes de Exploración', 'description' => 'Guantes no estériles para procedimientos menores'],
            
            // Suturas
            ['category' => 'Consumibles Quirúrgicos', 'name' => 'Suturas Absorbibles', 'description' => 'Vicryl, Monocryl, PDS'],
            ['category' => 'Consumibles Quirúrgicos', 'name' => 'Suturas No Absorbibles', 'description' => 'Nylon, Seda, Prolene'],
            ['category' => 'Suturas', 'name' => 'Grapadoras Quirúrgicas', 'description' => 'Grapadoras cutáneas desechables'],
            
            // Jeringas y Agujas
            ['category' => 'Jeringas y Agujas', 'name' => 'Jeringas Desechables', 'description' => 'Jeringas de 1ml a 60ml'],
            ['category' => 'Jeringas y Agujas', 'name' => 'Agujas Hipodérmicas', 'description' => 'Agujas de diferentes calibres'],
            ['category' => 'Jeringas y Agujas', 'name' => 'Sistemas de Venoclisis', 'description' => 'Equipos para infusión intravenosa'],
            
            // Catéteres
            ['category' => 'Catéteres', 'name' => 'Catéteres Intravenosos', 'description' => 'Catéteres periféricos de diferentes calibres'],
            ['category' => 'Catéteres', 'name' => 'Catéteres Urinarios', 'description' => 'Sondas Foley y nelaton'],
            ['category' => 'Catéteres', 'name' => 'Catéteres de Drenaje', 'description' => 'Drenes Blake, Hemovac, Jackson-Pratt'],
            
            // Material de Esterilización
            ['category' => 'Material de Esterilización', 'name' => 'Contenedores de Esterilización', 'description' => 'Cajas y contenedores para autoclave'],
            ['category' => 'Material de Esterilización', 'name' => 'Indicadores Químicos', 'description' => 'Cintas y tiras indicadoras'],
            ['category' => 'Material de Esterilización', 'name' => 'Bolsas de Esterilización', 'description' => 'Bolsas autosellables para esterilización'],
            
            // Ropa Quirúrgica
            ['category' => 'Ropa Quirúrgica', 'name' => 'Batas Quirúrgicas', 'description' => 'Batas estériles desechables'],
            ['category' => 'Ropa Quirúrgica', 'name' => 'Campos Quirúrgicos', 'description' => 'Campos fenestrados y sencillos'],
            ['category' => 'Ropa Quirúrgica', 'name' => 'Cubrebocas', 'description' => 'Mascarillas quirúrgicas desechables'],
            
            // Implantes
            ['category' => 'Implantes', 'name' => 'Placas y Tornillos', 'description' => 'Implantes ortopédicos de titanio'],
            ['category' => 'Implantes', 'name' => 'Mallas Quirúrgicas', 'description' => 'Mallas para hernias y reparación'],
            ['category' => 'Implantes', 'name' => 'Prótesis', 'description' => 'Implantes protésicos diversos'],
            
            // Equipos de Laparoscopía
            ['category' => 'Equipos de Laparoscopía', 'name' => 'Trócares', 'description' => 'Trócares de diferentes diámetros'],
            ['category' => 'Equipos de Laparoscopía', 'name' => 'Pinzas Laparoscópicas', 'description' => 'Pinzas de agarre y disección'],
            
            // Consola
            ['category' => 'Consola / Equipo Base', 'name' => 'AR-9800', 'description' => 'Lápices y puntas de electrocirugía'],
            
            
            // Material de Ortopedia
            ['category' => 'Material de Ortopedia', 'name' => 'Instrumental de Osteosíntesis', 'description' => 'Destornilladores, brocas, gubias'],
            ['category' => 'Material de Ortopedia', 'name' => 'Férulas y Yesos', 'description' => 'Material de inmovilización'],
        ];

        $now = Carbon::now();
        $data = [];

        foreach ($subcategories as $sub) {
            $categoryId = $categories[$sub['category']] ?? null;
            
            if ($categoryId) {
                $data[] = [
                    'category_id' => $categoryId,
                    'name' => $sub['name'],
                    'description' => $sub['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('subcategories')->insert($data);
    }
}