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
     * Crear preparación de cirugía
     * 
     * FIX #1: Ahora acepta $packageId = null para "preparar desde cero"
     */
    public function createPreparation($surgeryId, $packageId, $userId)
    {
        return DB::transaction(function () use ($surgeryId, $packageId, $userId) {
            // 1. Validar y obtener la cirugía
            $surgery = ScheduledSurgery::findOrFail($surgeryId);
            
            // Validar que no tenga ya una preparación activa
            if ($surgery->preparation()->whereNotIn('status', ['cancelled'])->exists()) {
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

            // 3. Actualizar estado de la cirugía
            $surgery->update(['status' => 'in_preparation']);
            
            // 4. Obtener contenido del paquete (si existe)
            $packageContents = collect();

            if ($packageId) {
                $package = PreAssembledPackage::with('contents')->findOrFail($packageId);
                $package->update(['status' => 'in_preparation']);

                $packageContents = $package->contents
                    ->groupBy('product_id')
                    ->map->count();

                Log::info("=== CONTENIDO DEL PAQUETE ===");
                foreach ($packageContents as $productId => $count) {
                    Log::info("Product {$productId}: {$count} unidades físicas");
                }
            } else {
                Log::info("=== PREPARACIÓN DESDE CERO (sin paquete) ===");
            }

            // 5. Obtener items necesarios del checklist
            $neededItems = $surgery->getChecklistItemsWithConditionals();

            // 6. Crear items de preparación comparando necesidades vs disponible
            foreach ($neededItems as $data) {
                $checklistItem = $data['item'];
                $productId = $checklistItem->product_id;
                $requiredQty = $data['adjusted_quantity'] ?? $checklistItem->quantity;

                // Obtener cuántas unidades físicas hay en el paquete (0 si no hay paquete)
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

            Log::info("ProductUnit encontrado: ID {$productUnit->id}, Status actual: {$productUnit->status}");

            if (!$productUnit->isAvailable() && $productUnit->status !== ProductUnit::STATUS_RESERVED) {
            throw new Exception("Esta unidad no está disponible (estado: {$productUnit->status_label})");
            }

            $preparation = SurgeryPreparation::with('items', 'scheduledSurgery')->findOrFail($preparationId);

            if ($preparation->status !== 'picking') {
                throw new Exception('La preparación no está en estado de recolección.');
            }

            if ($preparation->pre_assembled_package_id) {
                $alreadyScanned = PackageContent::where('pre_assembled_package_id', $preparation->pre_assembled_package_id)
                    ->where('product_unit_id', $productUnit->id)
                    ->exists();
            } else {
                // Sin paquete: verificar por la preparación directamente
                $alreadyScanned = DB::table('preparation_scanned_units')
                    ->where('surgery_preparation_id', $preparation->id)
                    ->where('product_unit_id', $productUnit->id)
                    ->exists();
            }

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
            if ($prepItem->fresh()->quantity_missing <= 0) {
                $prepItem->update(['status' => 'complete']);
            }

            Log::info("Item actualizado: Picked={$prepItem->fresh()->quantity_picked}, Missing={$prepItem->fresh()->quantity_missing}");

            // 7. Actualizar ProductUnit
            $productUnit->reserve(
                $userId, 
                $preparation->scheduled_surgery_id, 
                $preparation->pre_assembled_package_id
            );

            // 8. Registrar en contenidos del paquete (auditoría) - solo si hay paquete
            if ($preparation->pre_assembled_package_id) {
                PackageContent::create([
                    'pre_assembled_package_id' => $preparation->pre_assembled_package_id,
                    'product_id' => $productUnit->product_id,
                    'product_unit_id' => $productUnit->id,
                    'quantity' => 1,
                    'added_at' => now(),
                    'added_by' => $userId,
                ]);
            }

            Log::info("PackageContent creado para tracking");

            // 9. Verificar si la preparación está completa
            $this->checkPreparationCompletion($preparation);

            // Refrescar para obtener datos actualizados
            $prepItem->refresh();

            return [
                'success' => true,
                'product_name' => $productUnit->product->name,
                'item_id' => $prepItem->id,
                'quantity_missing' => $prepItem->quantity_missing,
                'quantity_picked' => $prepItem->quantity_picked,
                'item_status' => $prepItem->status,
                'preparation_complete' => $preparation->fresh()->status === 'complete',
            ];
        });
    }

    /**
     * Verificar si la preparación está completa
     * 
     * FIX #4: Usar quantity_missing en lugar de solo status para mayor precisión
     */
    protected function checkPreparationCompletion(SurgeryPreparation $preparation)
    {
        $pendingMandatory = $preparation->items()
            ->where('is_mandatory', true)
            ->where('quantity_missing', '>', 0)
            ->exists();

        if (!$pendingMandatory) {
            // Solo marcar como complete si NO hay mandatorios pendientes
            Log::info("✅ Todos los items obligatorios completos. Marcando preparación como complete.");
            
            $preparation->update([
                'status' => 'complete',
                'completed_at' => now(),
            ]);

            $preparation->scheduledSurgery->update(['status' => 'ready']);
        }
    }

    /**
     * Finalizar preparación manualmente (llamado desde verify)
     * 
     * FIX #5: Doble validación server-side de items obligatorios
     */
    public function finishPreparation($preparationId, $userId, $notes = null)
    {
        return DB::transaction(function () use ($preparationId, $userId, $notes) {
            $preparation = SurgeryPreparation::with('items')->findOrFail($preparationId);

            // Validar estado: solo se puede finalizar desde picking o complete
            if (!in_array($preparation->status, ['picking', 'complete', 'comparing'])) {
                throw new Exception("No se puede finalizar una preparación en estado: {$preparation->status}");
            }

            // BLINDAJE CRÍTICO: Recalcular items obligatorios pendientes
            $pendingMandatory = $preparation->items()
                ->where('is_mandatory', true)
                ->where('quantity_missing', '>', 0)
                ->get();

            if ($pendingMandatory->isNotEmpty()) {
                $details = $pendingMandatory->map(function($item) {
                    return "- {$item->product->name}: faltan {$item->quantity_missing} de {$item->quantity_required}";
                })->implode("\n");
                
                throw new Exception("No se puede finalizar. Items obligatorios pendientes:\n{$details}");
            }

            $preparation->update([
                'status' => 'verified',  // FIX #6: Usar 'verified' en vez de 'complete' para distinguir
                'verified_by' => $userId,
                'completed_at' => now(),
                'completion_notes' => $notes,
            ]);

            $preparation->scheduledSurgery->update(['status' => 'ready']);

            Log::info("Preparación {$preparationId} verificada por usuario {$userId}");

            return $preparation;
        });
    }

    /**
     * Cancelar preparación
     * 
     * FIX #7: Manejar caso sin paquete pre-armado
     */
    public function cancelPreparation($preparationId, $userId, $reason)
    {
        return DB::transaction(function () use ($preparationId, $userId, $reason) {
            $preparation = SurgeryPreparation::findOrFail($preparationId);

            // Validar que se pueda cancelar
            if (in_array($preparation->status, ['verified', 'cancelled'])) {
                throw new Exception("No se puede cancelar una preparación en estado: {$preparation->status}");
            }

            // Liberar ProductUnits reservadas
            if ($preparation->pre_assembled_package_id) {
                ProductUnit::where('current_package_id', $preparation->pre_assembled_package_id)
                    ->where('current_status', 'reserved')
                    ->update([
                        'current_status' => 'available',
                        'current_package_id' => null,
                        'current_surgery_id' => null,
                        'reserved_at' => null,
                        'reserved_by' => null,
                        'updated_at' => now(),
                    ]);
            } else {
                // Sin paquete: liberar por surgery_id
                ProductUnit::where('current_surgery_id', $preparation->scheduled_surgery_id)
                    ->where('current_status', 'reserved')
                    ->update([
                        'current_status' => 'available',
                        'current_package_id' => null,
                        'current_surgery_id' => null,
                        'reserved_at' => null,
                        'reserved_by' => null,
                        'updated_at' => now(),
                    ]);
            }

            $preparation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $userId,
                'cancellation_reason' => $reason,
            ]);

            // Restaurar estado de la cirugía
            $preparation->scheduledSurgery->update(['status' => 'scheduled']);
            
            // Restaurar paquete solo si existe
            if ($preparation->preAssembledPackage) {
                $preparation->preAssembledPackage->update(['status' => 'available']);
            }

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

        $totalQuantityRequired = $items->sum('quantity_required');
        $totalQuantityInPackage = $items->sum('quantity_in_package');
        $totalQuantityPicked = $items->sum('quantity_picked');
        $totalQuantitySatisfied = $totalQuantityInPackage + $totalQuantityPicked;

        $completionPercentage = $totalQuantityRequired > 0
            ? round(($totalQuantitySatisfied / $totalQuantityRequired) * 100, 2)
            : 0;

        return [
            'preparation' => $preparation,
            'total_items' => $items->count(),
            'completed_items' => $items->where('status', 'complete')->count(),
            'pending_items' => $items->where('status', 'pending')->count(),
            'in_package_items' => $items->where('status', 'in_package')->count(),
            
            // FIX #8: mandatory_pending calculado con quantity_missing (no con status)
            'mandatory_pending' => $items->where('is_mandatory', true)
                                    ->where('quantity_missing', '>', 0)
                                    ->count(),
            
            'total_quantity_required' => $totalQuantityRequired,
            'total_quantity_in_package' => $totalQuantityInPackage,
            'total_quantity_picked' => $totalQuantityPicked,
            'total_quantity_satisfied' => $totalQuantitySatisfied,
            'total_quantity_missing' => $items->sum('quantity_missing'),
            
            'completion_percentage' => $completionPercentage,
        ];
    }
}