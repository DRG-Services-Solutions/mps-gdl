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
                'name' => 'Arthrex',
                'contact_person' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@arthrex.com.mx',
                'phone' => '+52 55 5123 4567',
                'address' => 'Av. Insurgentes Sur 1971, Col. Guadalupe Inn, 01020 Ciudad de México, CDMX',
                'rfc' => 'AME021029KX8',
                'razon_social' => 'Arthrex México S.A. de C.V.',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-002',
                'name' => 'Medartis',
                'contact_person' => 'Ana Patricia González',
                'email' => 'ana.gonzalez@medartis.com',
                'phone' => '+52 55 9180 2000',
                'address' => 'Prol. Paseo de la Reforma 1015, Santa Fe, 01376 Ciudad de México, CDMX',
                'rfc' => 'MED070911167',
                'razon_social' => 'MEDARTIS, S.A. DE C.V.',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-003',
                'name' => 'Zimmer Biomet',
                'contact_person' => 'Roberto Jiménez',
                'email' => 'roberto.jimenez@zimmerbiomet.com',
                'phone' => '+52 81 8363 7000',
                'address' => 'Ave. Lázaro Cárdenas 2400, Valle Oriente, 66269 San Pedro Garza García, N.L.',
                'rfc' => 'BMI980105UI3',
                'razon_social' => 'BIOMET MÉXICO, S.A. DE C.V.',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-021',
                'name' => 'Double Medical',
                'contact_person' => 'Jorge Mendez',
                'email' => 'jl.mendez@doublemedical.com.mx',
                'phone' => '+52 477 123 4567',
                'address' => 'Blvd. La Caña 1234, Dirango, Durango',
                'rfc' => '1325C2017SSA',
                'razon_social' => 'GEOSSA EQUIPOS EIMPLEMENTOS, S.A. DE C.V.',
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