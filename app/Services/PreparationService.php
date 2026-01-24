<?php

namespace App\Services;

use App\Models\ScheduledSurgery;
use App\Models\PreAssembledPackage;
use App\Models\SurgeryPreparation;
use App\Models\SurgeryPreparationItem;
use App\Models\ProductUnit;
use App\Models\PackageContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PreparationService
{
    /**
     * Crear preparación de cirugía basada en un paquete pre-armado
     */
    public function createPreparation($surgeryId, $packageId, $userId)
    {
        return DB::transaction(function () use ($surgeryId, $packageId, $userId) {
            // 1. Validar y obtener la cirugía
            $surgery = ScheduledSurgery::findOrFail($surgeryId);
            
            // Validar que no tenga ya una preparación activa
            if ($surgery->preparation()->exists()) {
                throw new Exception('Esta cirugía ya tiene una preparación en proceso.');
            }

            // 2. Crear el registro de preparación
            $preparation = SurgeryPreparation::create([
                'scheduled_surgery_id' => $surgery->id,
                'pre_assembled_package_id' => $packageId,
                'status' => 'picking',
                'prepared_by' => $userId,
                'started_at' => now(),
            ]);

            // 3. Actualizar estados
            $surgery->update(['status' => 'in_preparation']);
            
            $package = PreAssembledPackage::with('contents')->findOrFail($packageId);
            $package->update(['status' => 'in_preparation']);

            // 4. ✅ CORRECCIÓN CRÍTICA: Contar unidades físicas, no sumar quantity
            $packageContents = $package->contents
                ->groupBy('product_id')
                ->map->count(); // Cada registro = 1 unidad física

            Log::info("=== CONTENIDO DEL PAQUETE ===");
            Log::info("Unidades por producto:");
            foreach ($packageContents as $productId => $count) {
                Log::info("Product {$productId}: {$count} unidades físicas");
            }

            // 5. Obtener items necesarios del checklist
            $neededItems = $surgery->getChecklistItemsWithConditionals();

            // 6. Crear items de preparación comparando necesidades vs disponible
            foreach ($neededItems as $data) {
                $checklistItem = $data['item'];
                $productId = $checklistItem->product_id;
                $requiredQty = $data['adjusted_quantity'] ?? $checklistItem->quantity;

                // Obtener cuántas unidades físicas hay en el paquete
                $inPackageQty = $packageContents->get($productId, 0);
                $missingQty = max(0, $requiredQty - $inPackageQty);

                $status = $missingQty <= 0 ? 'in_package' : 'pending';

                Log::info("Item: Product {$productId}, Required: {$requiredQty}, InPackage: {$inPackageQty}, Missing: {$missingQty}, Status: {$status}");

                $preparation->items()->create([
                    'product_id' => $productId,
                    'quantity_required' => $requiredQty,
                    'quantity_in_package' => $inPackageQty,
                    'quantity_picked' => 0,
                    'quantity_missing' => $missingQty,
                    'is_mandatory' => $data['is_mandatory'] ?? true,
                    'status' => $status,
                ]);
            }

            return $preparation;
        });
    }

    /**
     * Registrar producto escaneado por RFID
     */
    public function pickProduct($preparationId, $epc, $userId)
    {
        return DB::transaction(function () use ($preparationId, $epc, $userId) {
            $productUnit = ProductUnit::where('epc', $epc)->first();

            if (!$productUnit) {
                throw new Exception('EPC no encontrado en el sistema.');
            }

            Log::info("ProductUnit encontrado: ID {$productUnit->id}, Status actual: {$productUnit->current_status}");

            if (!in_array($productUnit->current_status, ['available', 'in_stock'])) {
                throw new Exception("Esta unidad no está disponible (estado: {$productUnit->current_status}).");
            }

            $preparation = SurgeryPreparation::with('items', 'scheduledSurgery')->findOrFail($preparationId);

            if ($preparation->status !== 'picking') {
                throw new Exception('La preparación no está en estado de recolección.');
            }

            // 4. Validar que no se haya escaneado previamente en esta preparación
            $alreadyScanned = PackageContent::where('pre_assembled_package_id', $preparation->pre_assembled_package_id)
                ->where('product_unit_id', $productUnit->id)
                ->exists();

            if ($alreadyScanned) {
                throw new Exception('Esta unidad ya fue escaneada para esta preparación.');
            }

            // 5. Localizar el ítem pendiente en la preparación
            $prepItem = $preparation->items()
                ->where('product_id', $productUnit->product_id)
                ->where('quantity_missing', '>', 0)
                ->first();

            if (!$prepItem) {
                throw new Exception('Este producto no es requerido o ya está completo.');
            }

            // 6. Actualizar cantidades del ítem
            $prepItem->increment('quantity_picked');
            $prepItem->decrement('quantity_missing');
            
            // Actualizar estado si se completó
            if ($prepItem->quantity_missing <= 0) {
                $prepItem->update(['status' => 'complete']);
            }

            Log::info("Item actualizado: Picked={$prepItem->quantity_picked}, Missing={$prepItem->quantity_missing}");

            // 7. ✅ ACTUALIZAR CON EL ENUM CORRECTO: 'reserved'
            $productUnit->update([
                'current_status' => 'reserved', // ✅ Cambiado de 'in_preparation' a 'reserved'
                'current_package_id' => $preparation->pre_assembled_package_id,
                'current_surgery_id' => $preparation->scheduled_surgery_id,
                'reserved_at' => now(),
                'reserved_by' => $userId,
            ]);

            Log::info("ProductUnit actualizado: Status=reserved, Package={$preparation->pre_assembled_package_id}, Surgery={$preparation->scheduled_surgery_id}");

            // 8. Registrar en contenidos del paquete (auditoría)
            PackageContent::create([
                'pre_assembled_package_id' => $preparation->pre_assembled_package_id,
                'product_id' => $productUnit->product_id,
                'product_unit_id' => $productUnit->id,
                'quantity' => 1,
                'added_at' => now(),
                'added_by' => $userId,
            ]);

            Log::info("PackageContent creado para tracking");

            // 9. Verificar si la preparación está completa
            $this->checkPreparationCompletion($preparation);

            return [
                'success' => true,
                'product_name' => $productUnit->product->name,
                'item_id' => $prepItem->id,
                'quantity_missing' => $prepItem->fresh()->quantity_missing,
                'quantity_picked' => $prepItem->fresh()->quantity_picked,
                'item_status' => $prepItem->status,
                'preparation_complete' => $preparation->fresh()->status === 'complete',
            ];
        });
    }

    /**
     * Verificar si la preparación está completa
     */
    protected function checkPreparationCompletion(SurgeryPreparation $preparation)
    {
        // Verificar items obligatorios
        $pendingMandatory = $preparation->items()
            ->where('is_mandatory', true)
            ->where('status', '!=', 'complete')
            ->where('status', '!=', 'in_package')
            ->exists();

        if (!$pendingMandatory) {
            $preparation->update([
                'status' => 'complete',
                'completed_at' => now(),
            ]);

            // Actualizar cirugía
            $preparation->scheduledSurgery->update(['status' => 'ready']);
        }
    }

    /**
     * Finalizar preparación manualmente
     */
    public function finishPreparation($preparationId, $userId, $notes = null)
    {
        return DB::transaction(function () use ($preparationId, $userId, $notes) {
            $preparation = SurgeryPreparation::with('items')->findOrFail($preparationId);

            // Validar que no haya items obligatorios pendientes
            $pendingMandatory = $preparation->items()
                ->where('is_mandatory', true)
                ->where('quantity_missing', '>', 0)
                ->get();

            if ($pendingMandatory->isNotEmpty()) {
                throw new Exception('No se puede finalizar. Hay items obligatorios pendientes.');
            }

            $preparation->update([
                'status' => 'complete',
                'completed_at' => now(),
                'completion_notes' => $notes,
            ]);

            $preparation->scheduledSurgery->update(['status' => 'ready']);

            return $preparation;
        });
    }

    /**
     * Cancelar preparación
     */
    public function cancelPreparation($preparationId, $userId, $reason)
    {
        return DB::transaction(function () use ($preparationId, $userId, $reason) {
            $preparation = SurgeryPreparation::findOrFail($preparationId);

            // ✅ Liberar unidades físicas que se habían asignado (usar 'reserved')
            ProductUnit::where('current_package_id', $preparation->pre_assembled_package_id)
                ->where('current_status', 'reserved') // ✅ Cambiado de 'in_preparation'
                ->update([
                    'current_status' => 'available',
                    'current_package_id' => null,
                    'current_surgery_id' => null,
                    'reserved_at' => null,
                    'reserved_by' => null,
                    'updated_at' => now(),
                ]);

            $preparation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $userId,
                'cancellation_reason' => $reason,
            ]);

            $preparation->scheduledSurgery->update(['status' => 'scheduled']);
            $preparation->preAssembledPackage->update(['status' => 'available']);

            return $preparation;
        });
    }

    /**
     * Obtener resumen de la preparación
     */
    public function getPreparationSummary($preparationId)
    {
        $preparation = SurgeryPreparation::with([
            'items.product',
            'scheduledSurgery',
            'preAssembledPackage'
        ])->findOrFail($preparationId);

        $items = $preparation->items;

        // ✅ CALCULAR BASADO EN CANTIDADES, NO EN ITEMS
        $totalQuantityRequired = $items->sum('quantity_required');
        $totalQuantityInPackage = $items->sum('quantity_in_package');
        $totalQuantityPicked = $items->sum('quantity_picked');
        $totalQuantitySatisfied = $totalQuantityInPackage + $totalQuantityPicked;

        // Calcular porcentaje basado en unidades reales
        $completionPercentage = $totalQuantityRequired > 0
            ? round(($totalQuantitySatisfied / $totalQuantityRequired) * 100, 2)
            : 0;

        Log::info("=== CÁLCULO DE COMPLETITUD ===");
        Log::info("Total Required: {$totalQuantityRequired}");
        Log::info("Total In Package: {$totalQuantityInPackage}");
        Log::info("Total Picked: {$totalQuantityPicked}");
        Log::info("Total Satisfied: {$totalQuantitySatisfied}");
        Log::info("Completion %: {$completionPercentage}");

        return [
            'preparation' => $preparation,
            'total_items' => $items->count(),
            'completed_items' => $items->where('status', 'complete')->count(),
            'pending_items' => $items->where('status', 'pending')->count(),
            'in_package_items' => $items->where('status', 'in_package')->count(),
            'mandatory_pending' => $items->where('is_mandatory', true)
                                    ->where('quantity_missing', '>', 0)
                                    ->count(),
            
            // ✅ NUEVO: Información basada en cantidades
            'total_quantity_required' => $totalQuantityRequired,
            'total_quantity_in_package' => $totalQuantityInPackage,
            'total_quantity_picked' => $totalQuantityPicked,
            'total_quantity_satisfied' => $totalQuantitySatisfied,
            'total_quantity_missing' => $items->sum('quantity_missing'),
            
            // ✅ CORREGIDO: Porcentaje basado en cantidades reales
            'completion_percentage' => $completionPercentage,
        ];
    }
}