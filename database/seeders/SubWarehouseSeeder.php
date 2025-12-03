<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubWarehouse;

class SubWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subwarehouses =[
            
                [
                    'legal_entity_id' => '1',
                    'name' => 'Prestamos Distribuciones',
                    'description' => '',
                    'is_active' => true,
                ],

                [
                    'legal_entity_id' => '1',
                    'name' => 'Foraneos Distribuciones',
                    'description' => '',
                    'is_active' => true,

                ],
                [
                    'legal_entity_id' => '1',
                    'name' => 'Instrumentales Distribuciones',
                    'description' => '',
                    'is_active' => true,
                ],
                [
                    'legal_entity_id' => '1',
                    'name' => 'Insumos Especiales Distribuciones',
                    'description' => '',
                    'is_active' => true,
                ],

                [
                    'legal_entity_id' => '2',
                    'name' => 'Prestamos Mario',
                    'description' => '',
                    'is_active' => true,
                ],

                [
                    'legal_entity_id' => '2',
                    'name' => 'Foraneos Mario',
                    'description' => '',
                    'is_active' => true,

                ],
                [
                    'legal_entity_id' => '2',
                    'name' => 'Instrumentales Mario',
                    'description' => '',
                    'is_active' => true,
                ],
                [
                    'legal_entity_id' => '2',
                    'name' => 'Insumos Especiales Mario',
                    'description' => '',
                    'is_active' => true,
                ],
            ];
                foreach($subwarehouses as $warehouse)
                {
                    SubWarehouse::create($warehouse);
                    $this->command->info("Sub-Almacen: {$warehouse['name']} creado con exito");
                }

                
            
    }
}