<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\MedicalSpecialty;
use App\Models\Supplier;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan datos relacionados
        if (Subcategory::count() === 0) {
            $this->command->warn('⚠️  No hay subcategorías. Ejecuta SubcategorySeeder primero.');
            return;
        }

        if (Supplier::count() === 0) {
            $this->command->warn('⚠️  No hay proveedores. Ejecuta SupplierSeeder primero.');
            return;
        }

        $this->command->info('🏥 Creando productos médicos...');

        // Limpiar tabla
   

        // Crear o buscar proveedores específicos
        $arthrex = Supplier::firstOrCreate(
            ['name' => 'Arthrex'],
            [
                'contact_name' => 'Representante Arthrex',
                'email' => 'ventas@arthrex.com',
                'phone' => '555-0001',
                'address' => 'Ave. Principales #1234, CDMX',
                'status' => 'active',
            ]
        );

        $medartis = Supplier::firstOrCreate(
            ['name' => 'Medartis'],
            [
                'contact_name' => 'Representante Medartis',
                'email' => 'ventas@medartis.com',
                'phone' => '555-0002',
                'address' => 'Ave. Principales #5678, CDMX',
                'status' => 'active',
            ]
        );

        // Productos
        $productos = $this->getProductosData($arthrex->id, $medartis->id);

        $createdCount = 0;

        foreach ($productos as $productoData) {
            // Obtener subcategoría
            $subcategory = Subcategory::where('name', 'LIKE', "%{$productoData['subcategory']}%")->first();
            
            if (!$subcategory) {
                $subcategory = Subcategory::inRandomOrder()->first();
            }

            // Obtener especialidad
            $specialty = null;
            if (isset($productoData['specialty'])) {
                $specialty = MedicalSpecialty::where('name', 'LIKE', "%{$productoData['specialty']}%")->first();
            }
            
            if (!$specialty) {
                $specialty = MedicalSpecialty::inRandomOrder()->first();
            }

            Product::create([
                'name' => $productoData['name'],
                'code' => $this->generateProductCode($productoData['name']),
                'description' => $productoData['description'],
                'subcategory_id' => $subcategory->id,
                'specialty_id' => $specialty?->id,
                'supplier_id' => $productoData['supplier_id'],
                'tracking_type' => $productoData['tracking_type'],
                'requires_sterilization' => $productoData['requires_sterilization'],
                'requires_refrigeration' => $productoData['requires_refrigeration'] ?? false,
                'requires_temperature' => $productoData['requires_temperature'] ?? false,
                'minimum_stock' => $productoData['minimum_stock'],
                'list_price' => $productoData['list_price'],
                'status' => 'active',
            ]);

            $createdCount++;
        }

        $this->command->info("✅ Se crearon {$createdCount} productos exitosamente.");
        $this->showStatistics();
    }

    /**
     * Obtener datos de productos
     */
    private function getProductosData(int $arthrexId, int $medartisId): array
    {
        return [
            // ========================================
            // PRODUCTOS ARTHREX
            // ========================================
            [
                'name' => 'Arthrex Bio-Corkscrew FT 7mm x 23mm',

                'description' => 'Ancla bioabsorbible para reconstrucción de ligamentos, diseñada para fijación de tejido blando al hueso. Compatible con sistema FiberTape.',
                'subcategory' => 'Implantes',
                'specialty' => 'Ortopedia',
                'supplier_id' => $arthrexId,
                'tracking_type' => 'rfid',
                'requires_sterilization' => true,
                'minimum_stock' => 5,
                'list_price' => 8500.00,
            ],
            [
                'name' => 'Arthrex SwiveLock 4.75mm',
                'description' => 'Ancla de interferencia sin nudos para fijación de tejidos blandos. Sistema de bloqueo giratorio para optimizar la tensión del injerto.',
                'subcategory' => 'Implantes',
                'specialty' => 'Ortopedia',
                'supplier_id' => $arthrexId,
                'tracking_type' => 'rfid',
                'requires_sterilization' => true,
                'minimum_stock' => 8,
                'list_price' => 7200.00,
            ],
            [
                'name' => 'Arthrex FiberTape 2mm',
                'description' => 'Cinta de sutura ultra resistente de polietileno para reparación de ligamentos y tendones. Mayor resistencia que suturas convencionales.',
                'subcategory' => 'Sutura',
                'specialty' => 'Ortopedia',
                'supplier_id' => $arthrexId,
                'tracking_type' => 'code',
                'requires_sterilization' => true,
                'minimum_stock' => 20,
                'list_price' => 450.00,
            ],

            // ========================================
            // PRODUCTOS MEDARTIS
            // ========================================
            [
                'name' => 'Medartis TriLock 2.0mm Placa Recta 4 Orificios',
                'description' => 'Placa de bloqueo triangular para cirugía de mano y pie. Sistema de triple bloqueo para máxima estabilidad angular.',
                'subcategory' => 'Implantes',
                'specialty' => 'Ortopedia',
                'supplier_id' => $medartisId,
                'tracking_type' => 'serial',
                'requires_sterilization' => true,
                'minimum_stock' => 10,
                'list_price' => 6800.00,
            ],
            [
                'name' => 'Medartis CMF 1.5mm Sistema Mandibular',
                'description' => 'Sistema de placas y tornillos para cirugía craneomaxilofacial. Titanio grado médico con perfil bajo para mínima palpabilidad.',
                'subcategory' => 'Implantes',
                'specialty' => 'Cirugía Maxilofacial',
                'supplier_id' => $medartisId,
                'tracking_type' => 'serial',
                'requires_sterilization' => true,
                'minimum_stock' => 6,
                'list_price' => 12500.00,
            ],
            [
                'name' => 'Medartis Aptus Tornillo Cortical 2.7mm x 16mm',
                'description' => 'Tornillo de bloqueo autoperforante para sistema Aptus. Diseño de rosca optimizado para inserción en hueso cortical.',
                'subcategory' => 'Implantes',
                'specialty' => 'Ortopedia',
                'supplier_id' => $medartisId,
                'tracking_type' => 'serial',
                'requires_sterilization' => true,
                'minimum_stock' => 50,
                'list_price' => 280.00,
            ],
        ];
    }

    /**
     * Generar código de producto
     */
    private function generateProductCode(string $name): string
    {
        // Tomar las primeras 3 letras del nombre + random
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        $suffix = substr(str_shuffle('0123456789ABCDEF'), 0, 6);
        
        return $prefix . '-' . $suffix;
    }

    /**
     * Mostrar estadísticas
     */
    private function showStatistics(): void
    {
        $this->command->newLine();
        $this->command->info('📊 ESTADÍSTICAS DE PRODUCTOS:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        $total = Product::count();
        
        $this->command->info("   📦 Total de productos: {$total}");
        
        // Por proveedor
        $bySupplier = Product::with('supplier')
            ->get()
            ->groupBy('supplier.name')
            ->map(fn($products) => $products->count());
        
        $this->command->newLine();
        $this->command->info('🏭 POR PROVEEDOR:');
        foreach ($bySupplier as $supplier => $count) {
            $this->command->info("   • {$supplier}: {$count} productos");
        }
        
        // Por tipo de tracking
        $byTracking = Product::selectRaw('tracking_type, COUNT(*) as count')
            ->groupBy('tracking_type')
            ->pluck('count', 'tracking_type');
        
        $this->command->newLine();
        $this->command->info('🔍 POR TIPO DE RASTREO:');
        $this->command->info("   📡 RFID: " . ($byTracking['rfid'] ?? 0));
        $this->command->info("   #️⃣  Serial: " . ($byTracking['serial'] ?? 0));
        $this->command->info("   🏷️  Code: " . ($byTracking['code'] ?? 0));
        
        $sterilization = Product::where('requires_sterilization', true)->count();
        
        $this->command->newLine();
        $this->command->info('🔬 REQUISITOS:');
        $this->command->info("   ♨️  Requieren esterilización: {$sterilization}");
        
        $this->command->newLine();
    }
}