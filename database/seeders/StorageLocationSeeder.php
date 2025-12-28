<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StorageLocation;

class StorageLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $storageLocations = [
            [
            'name' => 'Almacen Principal',
            'description' => 'Almacen general MPS',
            'code' => 'AL-001',
            ],
        ];

        foreach($storageLocations as $locations)
        {
            StorageLocation::create($locations);
            $this->command->info("✅ Ubicacion:  {$locations['name']}, creada exitosamente");
        }


    }
}