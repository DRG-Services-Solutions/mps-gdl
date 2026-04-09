<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;
use Exception;

class PhysicalAssemblyService
{
    /**
     * Ensambla una nueva caja física validando las piezas que el operador escaneó manualmente.
     * * @param Product $setProduct El producto compuesto (La receta maestro)
     * @param int $locationId La ubicación del almacén
     * @param int $userId ID del operador
     * @param array $validatedUnitIds Los IDs de los ProductUnits que el operador escaneó/validó
     * @return ProductUnit La nueva caja física
     */
    public function assembleSetManual(Product $setProduct, int $locationId, int $userId, array $validatedUnitIds): ProductUnit
    {
        if (!$setProduct->is_composite) {
            throw new Exception("El producto no es un Set/Compuesto.");
        }

        // 1. Validar que las unidades escaneadas existan y estén disponibles
        $scannedUnits = ProductUnit::whereIn('id', $validatedUnitIds)
            ->where('status', ProductUnit::STATUS_AVAILABLE)
            ->whereNull('parent_unit_id') // Que no estén ya en otra caja
            ->get();

        if ($scannedUnits->count() !== count($validatedUnitIds)) {
            throw new Exception("Algunas de las piezas escaneadas ya no están disponibles o ya pertenecen a otro Set.");
        }

        // Agrupamos lo que el operador escaneó por product_id para compararlo con la receta
        $scannedGrouped = $scannedUnits->groupBy('product_id');

        // 2. Comparar contra la Receta (BOM)
        $components = $setProduct->components;
        
        foreach ($components as $comp) {
            $requiredQty = $comp->pivot->quantity;
            $isMandatory = $comp->pivot->is_mandatory;
            
            $scannedQtyForThisProduct = isset($scannedGrouped[$comp->id]) ? $scannedGrouped[$comp->id]->count() : 0;

            // Si es obligatorio y el operador escaneó menos de lo debido
            if ($isMandatory && $scannedQtyForThisProduct < $requiredQty) {
                throw new Exception("Faltan piezas obligatorias para: {$comp->name}. Escaneadas: {$scannedQtyForThisProduct} de {$requiredQty}");
            }

            // Si el operador intentó meter de más
            if ($scannedQtyForThisProduct > $requiredQty) {
                throw new Exception("Escaneaste demasiadas piezas de: {$comp->name}. Máximo permitido: {$requiredQty}");
            }
        }

        // 3. Todo está validado. EJECUTAR EL ENSAMBLAJE (Transacción Segura)
        return DB::transaction(function () use ($setProduct, $locationId, $userId, $validatedUnitIds) {
            
            // A) Nace la nueva Caja Física
            $parentUnit = ProductUnit::create([
                'product_id' => $setProduct->id,
                'serial_number' => $this->generateSetSerialNumber($setProduct),
                'status' => ProductUnit::STATUS_AVAILABLE,
                'current_location_id' => $locationId,
                'created_by' => $userId,
            ]);

            // B) Metemos las piezas escaneadas a la caja (Anidamiento)
            if (!empty($validatedUnitIds)) {
                ProductUnit::whereIn('id', $validatedUnitIds)->update([
                    'parent_unit_id' => $parentUnit->id,
                    'updated_by' => $userId,
                    'notes' => DB::raw("CONCAT(COALESCE(notes, ''), ' [Empaquetado en Set: {$parentUnit->serial_number}]')")
                ]);
            }

            return $parentUnit;
        });
    }

    private function generateSetSerialNumber(Product $product): string
    {
        $prefix = $product->code ? strtoupper($product->code) . '-BOX-' : 'SET-BOX-';
        $count = ProductUnit::where('serial_number', 'like', "{$prefix}%")->withTrashed()->count();
        $nextNumber = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $nextNumber; 
    }
}