<?php

namespace App\Services;

use App\Models\ScheduledSurgery;
use App\Models\PreAssembledPackage;
use App\Models\SurgeryPreparation;
use App\Models\SurgeryPreparationItem;
use App\Models\ProductUnit;
use App\Models\PackageContent;
use App\Models\ChecklistItem;
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
                    'checklist_item_id'   => $checklistItem->id,
                    'quantity_required' => $requiredQty,
                    'quantity_in_package' => $inPackageQty,
                    'quantity_picked' => 0,
                    'quantity_missing' => $missingQty,
                    'is_mandatory' => $data['is_mandatory'] ?? true,
                    'status' => $status,
                ]);
            }

            // 7. Procesar condicionales que generan productos adicionales
            //    (dependencias y reemplazos) para que aparezcan desde el inicio
            $this->processConditionalProducts($preparation, $surgery, $packageContents);

            return $preparation;
        });
    }

    /**
     * Procesar condicionales que generan productos adicionales al crear la preparación.
     * 
     * getChecklistItemsWithConditionals() filtra items con final_quantity = 0
     * (exclude, replace) y no agrega los target_product de dependencias.
     * Este método recorre TODOS los items base del checklist para detectar:
     *   - add_dependency → crea prep item para el producto dependencia
     *   - replace → crea prep item para el producto reemplazo
     *   - exclude → marca visibilidad (no crea item, qty = 0)
     */
    protected function processConditionalProducts(
        SurgeryPreparation $preparation,
        ScheduledSurgery $surgery,
        $packageContents
    ): void {
        $baseItems = ChecklistItem::where('checklist_id', $surgery->checklist_id)
            ->with(['product', 'conditionals.targetProduct'])
            ->ordered()
            ->get();

        $createdProductIds = $preparation->items()->pluck('product_id')->toArray();

        foreach ($baseItems as $item) {
            $adjustedData = $item->getAdjustedQuantity($surgery);
            $conditional = $adjustedData['conditional'] ?? null;

            if (!$conditional) continue;

            switch ($conditional->action_type) {

                case 'add_dependency':
                    // El producto base ya está en la preparación.
                    // Crear el producto DEPENDENCIA (target_product) si no existe.
                    if (!$conditional->target_product_id) break;
                    if (in_array($conditional->target_product_id, $createdProductIds)) break;

                    $depQty = $conditional->dependency_quantity ?? 1;
                    $inPkg = $packageContents instanceof \Illuminate\Support\Collection
                        ? $packageContents->get($conditional->target_product_id, 0)
                        : 0;
                    $missing = max(0, $depQty - $inPkg);

                    $preparation->items()->create([
                        'product_id'          => $conditional->target_product_id,
                        'checklist_item_id'   => $item->id,
                        'quantity_required'    => $depQty,
                        'quantity_in_package'  => $inPkg,
                        'quantity_picked'      => 0,
                        'quantity_missing'     => $missing,
                        'is_mandatory'         => false,
                        'status'              => $missing <= 0 ? 'in_package' : 'pending',
                        'notes'               => "Dependencia de: {$item->product->name}",
                    ]);

                    $createdProductIds[] = $conditional->target_product_id;

                    Log::info("Dependencia creada al inicio: {$conditional->targetProduct?->name} ×{$depQty} (desde {$item->product->name})");
                    break;

                case 'replace':
                    // El producto original fue excluido (qty=0, no está en preparación).
                    // Crear el producto REEMPLAZO (target_product) si no existe.
                    if (!$conditional->target_product_id) break;
                    if (in_array($conditional->target_product_id, $createdProductIds)) break;

                    $replaceQty = $adjustedData['base_quantity']; // misma cantidad que el original
                    $inPkg = $packageContents instanceof \Illuminate\Support\Collection
                        ? $packageContents->get($conditional->target_product_id, 0)
                        : 0;
                    $missing = max(0, $replaceQty - $inPkg);

                    $preparation->items()->create([
                        'product_id'          => $conditional->target_product_id,
                        'checklist_item_id'   => $item->id,
                        'quantity_required'    => $replaceQty,
                        'quantity_in_package'  => $inPkg,
                        'quantity_picked'      => 0,
                        'quantity_missing'     => $missing,
                        'is_mandatory'         => $item->is_mandatory ?? true,
                        'status'              => $missing <= 0 ? 'in_package' : 'pending',
                        'notes'               => "Reemplazo de: {$item->product->name}",
                    ]);

                    $createdProductIds[] = $conditional->target_product_id;

                    Log::info("Reemplazo creado al inicio: {$conditional->targetProduct?->name} ×{$replaceQty} (reemplaza a {$item->product->name})");
                    break;
            }
        }
    }

    /**
     * Registrar producto escaneado por RFID
     */
    public function pickProduct($preparationId, $code, $userId)
    {
        return DB::transaction(function () use ($preparationId, $code, $userId) {

            Log::info("SCAN INPUT", ['code' => $code]);

            $productUnit = ProductUnit::where('epc', $code)->first();

            if ($productUnit) {
                return $this->pickByEpc($preparationId, $productUnit, $userId);
            }

            $product = \App\Models\Product::where('barcode', $code)
                ->orWhere('sku', $code)
                ->first();

            if (!$product) {
                throw new Exception('Código no reconocido.');
            }

            return $this->pickByQuantity($preparationId, $product, $userId);
        });
    }

    private function pickByEpc($preparationId, ProductUnit $productUnit, $userId)
    {
        Log::info("Picking por EPC", [
            'unit_id' => $productUnit->id,
            'status' => $productUnit->status
        ]);

        if (!$productUnit->isAvailable()) {
            throw new Exception("Esta unidad no está disponible (estado: {$productUnit->status_label})");
        }

        $preparation = SurgeryPreparation::with('items', 'scheduledSurgery')->findOrFail($preparationId);

        $prepItem = $preparation->items()
            ->where('product_id', $productUnit->product_id)
            ->where('quantity_missing', '>', 0)
            ->first();

        if (!$prepItem) {
            throw new Exception('Este producto no es requerido o ya está completo.');
        }

        // actualizar cantidades
        $prepItem->increment('quantity_picked');
        $prepItem->decrement('quantity_missing');

        if ($prepItem->fresh()->quantity_missing <= 0) {
            $prepItem->update(['status' => 'complete']);
        }

        // reservar unidad
        $productUnit->reserve(
            $userId,
            $preparation->scheduled_surgery_id,
            $preparation->pre_assembled_package_id
        );

        return [
            'success' => true,
            'product_name' => $productUnit->product->name,
            'item_id' => $prepItem->id,
            'quantity_missing' => $prepItem->quantity_missing,
        ];
    }

    private function pickByQuantity($preparationId, \App\Models\Product $product, $userId)
    {
        Log::info("Picking por cantidad", [
            'product_id' => $product->id,
            'tracking_type' => $product->tracking_type
        ]);

        $preparation = SurgeryPreparation::with('items')->findOrFail($preparationId);

        $prepItem = $preparation->items()
            ->where('product_id', $product->id)
            ->where('quantity_missing', '>', 0)
            ->first();

        if (!$prepItem) {
            throw new Exception('Producto no requerido o ya completo.');
        }

        // 🔥 solo cantidades (NO ProductUnit)
        $prepItem->increment('quantity_picked');
        $prepItem->decrement('quantity_missing');

        if ($prepItem->fresh()->quantity_missing <= 0) {
            $prepItem->update(['status' => 'complete']);
        }

        // registrar auditoría (opcional pero recomendado)
        DB::table('preparation_scanned_units')->insert([
            'surgery_preparation_id' => $preparation->id,
            'product_id' => $product->id,
            'scanned_by' => $userId,
            'scanned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'success' => true,
            'product_name' => $product->name,
            'item_id' => $prepItem->id,
            'quantity_missing' => $prepItem->quantity_missing,
        ];
    }

    protected function evaluateItemConditionals(SurgeryPreparation $preparation, SurgeryPreparationItem $item): array
{
    $actions = [];
    $applicable = $item->getApplicableConditional();
    if (!$applicable) return $actions;

    Log::info("Condicional aplicable para item {$item->id}: {$applicable->action_type}");

    switch ($applicable->action_type) {

        case 'adjust_quantity':
            $targetQty = $applicable->quantity_override;
            if ($targetQty !== null && $item->quantity_required !== $targetQty) {
                $diff      = $targetQty - $item->quantity_required;
                $newMissing = max(0, $item->quantity_missing + $diff);
                $item->update([
                    'quantity_required' => $targetQty,
                    'quantity_missing'  => $newMissing,
                    'status'            => $newMissing <= 0 ? 'complete' : 'pending',
                ]);
                $actions[] = [
                    'type'    => 'quantity_adjusted',
                    'item_id' => $item->id,
                    'message' => "Cantidad ajustada a {$targetQty} por condicional.",
                ];
            }
            break;

        case 'add_dependency':
            if ($applicable->target_product_id) {
                $action = $this->ensureDependencyExists($preparation, $applicable);
                if ($action) $actions[] = $action;
            }
            break;

        case 'exclude':
            $item->update([
                'quantity_required' => 0,
                'quantity_missing'  => 0,
                'status'            => 'complete',
            ]);
            $actions[] = [
                'type'    => 'excluded',
                'item_id' => $item->id,
                'message' => 'Producto excluido por condicional.',
            ];
            break;

        case 'replace':
            $action = $this->applyReplacementConditional($preparation, $item, $applicable);
            if ($action) $actions[] = $action;
            break;
    }

    return $actions;
}

protected function ensureDependencyExists(SurgeryPreparation $preparation, \App\Models\ChecklistConditional $conditional): ?array
{
    $targetProductId = $conditional->target_product_id;
    $requiredQty     = $conditional->dependency_quantity ?? 1;
    $productName     = $conditional->targetProduct?->name ?? "Producto ID {$targetProductId}";

    $existing = $preparation->items()->where('product_id', $targetProductId)->first();

    if ($existing) {
        if ($existing->quantity_required >= $requiredQty) return null;

        $diff = $requiredQty - $existing->quantity_required;
        $existing->update([
            'quantity_required' => $requiredQty,
            'quantity_missing'  => $existing->quantity_missing + $diff,
            'status'            => 'pending',
        ]);

        return [
            'type'         => 'dependency_updated',
            'product_name' => $productName,
            'message'      => "⚠️ Dependencia actualizada: {$requiredQty}x {$productName}",
        ];
    }

    $preparation->items()->create([
        'product_id'          => $targetProductId,
        'quantity_required'   => $requiredQty,
        'quantity_in_package' => 0,
        'quantity_picked'     => 0,
        'quantity_missing'    => $requiredQty,
        'is_mandatory'        => false,
        'status'              => 'pending',
    ]);

    return [
        'type'         => 'dependency_added',
        'product_name' => $productName,
        'message'      => "Dependencia agregada: {$requiredQty}x {$productName}",
    ];
}

    protected function applyReplacementConditional(SurgeryPreparation $preparation, SurgeryPreparationItem $item, \App\Models\ChecklistConditional $conditional): ?array
        {
            if (!$conditional->target_product_id) return null;

            $replacementName = $conditional->targetProduct?->name ?? "ID {$conditional->target_product_id}";

            if ($preparation->items()->where('product_id', $conditional->target_product_id)->exists()) return null;

            $preparation->items()->create([
                'product_id'          => $conditional->target_product_id,
                'quantity_required'   => $item->quantity_required,
                'quantity_in_package' => 0,
                'quantity_picked'     => 0,
                'quantity_missing'    => $item->quantity_required,
                'is_mandatory'        => $item->is_mandatory,
                'status'              => 'pending',
            ]);

            return [
                'type'         => 'replaced',
                'product_name' => $replacementName,
                'message'      => "🔄 Reemplazado por: {$replacementName}",
            ];
        }

    public function reevaluateAllConditionals(SurgeryPreparation $preparation): array
        {
            $allActions = [];

            $completedItems = $preparation->items()
                ->whereIn('status', ['complete', 'in_package'])
                ->with([
                    'preparation.scheduledSurgery.hospitalModalityConfig',
                    'preparation.scheduledSurgery.hospital',
                ])
                ->get();

            foreach ($completedItems as $item) {
                $actions = $this->evaluateItemConditionals($preparation, $item);
                if (!empty($actions)) $allActions = array_merge($allActions, $actions);
            }

            return $allActions;
        }

    /**
     * Verificar si la preparación está completa
     * 
     */
    protected function checkPreparationCompletion(SurgeryPreparation $preparation)
    {
        $pendingMandatory = $preparation->items()
            ->where('is_mandatory', true)
            ->where('quantity_missing', '>', 0)
            ->exists();

        if (!$pendingMandatory) {
            // Solo marcar como complete si NO hay mandatorios pendientes
            Log::info("Todos los items obligatorios completos. Marcando preparación como complete.");
            
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