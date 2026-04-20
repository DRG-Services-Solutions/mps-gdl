<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductType;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productType = [
            [
                'name' => 'Consumible',
                'description' => 'Productos que se consumen rápidamente',
            ],
            [
                'name' => 'Instrumental',
                'description' => 'Productos de tipo instrumental quirurgico',
            ],
            [
                'name' => 'Set',
                'description' => 'Cajas o kits que contienen varios productos',
            ],
            [
                'name' => 'Equipo',
                'description' => 'Productos de tipo equipo médico',
            ],
            [
                'name' => 'Pieza Instrumental',
                'description' => 'Productos de tipo pieza instrumental',
            ],
        ];

        foreach ($productType as $type)
        {
            ProductType::create($type);
            $this->command->info("✅ Tipo de Producto:  {$type['name']} creado exitosamente");
        }
    }
}
