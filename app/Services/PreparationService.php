<?php
namespace App\Services;

use App\Models\ScheduledSurgery;
use App\Models\PreAssembledPackage;
use App\Models\SurgeryPreparation;
use App\Models\SurgeryPreparationItem;
use Illuminate\Support\Facades\DB;

class PreparationService
{
    public function createPreparation($surgeryId, $packageId, $userId)
    {
        return DB::transaction(function () use ($surgeryId, $packageId, $userId) {
        // 1. Obtener la cirugía y sus datos de contexto
        $surgery = ScheduledSurgery::with(['hospital', 'doctor', 'checklist.items'])->findOrFail($surgeryId);
        $package = PreAssembledPackage::with('contents')->findOrFail($packageId);

        // 2. Crear la cabecera de la Preparación (La Hoja de Trabajo)
        $preparation = SurgeryPreparation::create([  
            'scheduled_surgery_id' => $surgery->id,
            'pre_assembled_package_id' => $package->id,
            'status' => 'comparing',
            'started_at' => now(),
            'prepared_by' => $userId,
        ]);
        $package-update([
            'preparation_id' => $preparation->id,
            'status' => 'in__preparation',
        ]);
    

        foreach ($surgery->checklist->items as $checkItem) {
            
            // Ejecutamos tu lógica de condicionales (Paso 2 de la sugerencia)
            $evaluation = $checkItem->evaluateConditionals([
                'hospital_id' => $surgery->hospital_id,
                'doctor_id' => $surgery->doctor_id,
                'modality_id' => $surgery->modality_id,
                'legal_entity_id' => $surgery->legal_entity_id,
            ]);

            if ($evaluation['status'] === 'excluded') continue;

            // 4. EL MATCH: Ver cuánto hay de este producto en el paquete (Paso 3 de la sugerencia)
            $quantityInPackage = $package->contents
                ->where('product_id', $checkItem->product_id)
                ->sum('quantity'); 

            $requiredQty = $evaluation['quantity'];
            $missingQty = max(0, $requiredQty - $quantityInPackage);

            // 5. CREAR EL ÍTEM DE TRABAJO FINAL
            SurgeryPreparationItem::create([
                'preparation_id' => $preparation->id,
                'product_id' => $checkItem->product_id,
                'quantity_required' => $requiredQty,
                'is_mandatory' => $checkItem->is_mandatory ?? true, // Usamos la sugerencia de mandatorio
                'quantity_in_package' => min($quantityInPackage, $requiredQty),
                'quantity_missing' => $missingQty,
                'status' => ($missingQty == 0) ? 'complete' : 'pending',
                'storage_location_id' => $checkItem->product->default_location_id ?? null,
            ]);
        }

        // 6. IDENTIFICAR EXCEDENTES (Opcional pero recomendado - Paso 4)
        // Buscamos productos que están en el paquete pero NO en el checklist ajustado
        $this->identifySurplusItems($preparation, $package);            
                return $preparation;
            });
        }
}


