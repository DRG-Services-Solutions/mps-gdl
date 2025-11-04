<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            // ========================================
            // PROVEEDORES PRINCIPALES - ORTOPEDIA
            // ========================================
            [
                'code' => 'SUP-001',
                'name' => 'Arthrex México',
                'contact_person' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@arthrex.com.mx',
                'phone' => '+52 55 5123 4567',
                'address' => 'Av. Insurgentes Sur 1971, Col. Guadalupe Inn, 01020 Ciudad de México, CDMX',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-002',
                'name' => 'Stryker México',
                'contact_person' => 'Ana Patricia González',
                'email' => 'ana.gonzalez@stryker.com',
                'phone' => '+52 55 9180 2000',
                'address' => 'Prol. Paseo de la Reforma 1015, Santa Fe, 01376 Ciudad de México, CDMX',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-003',
                'name' => 'Zimmer Biomet',
                'contact_person' => 'Roberto Jiménez',
                'email' => 'roberto.jimenez@zimmerbiomet.com',
                'phone' => '+52 81 8363 7000',
                'address' => 'Ave. Lázaro Cárdenas 2400, Valle Oriente, 66269 San Pedro Garza García, N.L.',
                'is_active' => true,
            ],

            // ========================================
            // PROVEEDOR INACTIVO (PARA TESTING)
            // ========================================
            [
                'code' => 'SUP-020',
                'name' => 'Instrumentos Médicos del Bajío (Descontinuado)',
                'contact_person' => 'José Luis Pérez',
                'email' => 'jl.perez@imbajio.com.mx',
                'phone' => '+52 477 123 4567',
                'address' => 'Blvd. Campestre 1234, León, Guanajuato',
                'is_active' => false,
            ],
        ];

        // Insertar con timestamps
        foreach ($suppliers as $supplier) {
            Supplier::create(array_merge($supplier, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('✅ ' . count($suppliers) . ' proveedores creados exitosamente.');
    }
}