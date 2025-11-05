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
            ['category' => 'Instrumental Quirúrgico', 'name' => 'Accesorio', 'description' => 'Lentes, Botas, etc.'],
            ['category' => 'Instrumental Quirúrgico', 'name' => 'Consola', 'description' => 'Consolas varias para sinergy'],
            
            // Material de Curación
            ['category' => 'Consumibles Quirúrgicos', 'name' => 'General', 'description' => 'Suturas esteriles de alta resistena, etc.'],
            ['category' => 'Consumibles Quirúrgicos', 'name' => 'Placa', 'description' => 'Placas de fijacion, placa de tercio de caña, etc.'],
            ['category' => 'Consumibles Quirúrgicos', 'name' => 'Tendon', 'description' => 'Tornillos, tendones, etc'],
            ['category' => 'Consumibles Quirúrgicos', 'name' => 'Tendonesis', 'description' => 'Tornillos, tendones, etc']

            
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