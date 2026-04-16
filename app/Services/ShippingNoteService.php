<?php

namespace App\Services;

use App\Models\ShippingNote;
use App\Models\ShippingNotePackage;
use App\Models\ShippingNoteKit;
use App\Models\ShippingNoteItem;
use App\Models\ShippingNoteRentalConcept;
use App\Models\ScheduledSurgery;
use App\Models\PreAssembledPackage;
use App\Models\SurgicalKit;
use App\Models\ProductUnit;
use App\Models\ChecklistItem;
use App\Models\ChecklistConditional;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShippingNoteService
{
   
    /**
     * Crear una remisión a partir de una cirugía programada.
     * Evalúa el checklist con condicionales y genera el borrador.
     *
     * @param ScheduledSurgery $surgery
     * @param int $billingLegalEntityId Razón social que factura
     * @param string|null $notes
     * @return ShippingNote
     */
    public function createFromSurgery(
        ScheduledSurgery $surgery,
        int $billingLegalEntityId,
        ?string $notes = null
    ): ShippingNote {
        // Validar que la cirugía puede generar remisión
        $this->validateSurgeryForShipping($surgery);

        return DB::transaction(function () use ($surgery, $billingLegalEntityId, $notes) {

            // 1. Evaluar checklist con condicionales (solo items con qty > 0)
            $evaluatedItems = $surgery->getChecklistItemsWithConditionals();

            // 2. Capturar también items excluidos/reemplazados para auditoría
            $excludedItems = $this->getExcludedItems($surgery);

            // 3. Construir snapshot completo (incluye excluidos)
            $checklistSnapshot = $this->buildChecklistSnapshot($evaluatedItems, $excludedItems);

            Log::info("Checklist evaluado para cirugía {$surgery->code}", [
                'total_items' => $evaluatedItems->count(),
                'with_conditionals' => $evaluatedItems->where('has_conditional', true)->count(),
                'excluded_items' => $excludedItems->count(),
            ]);

            // 4. Crear la remisión
            $shippingNote = ShippingNote::create([
                'scheduled_surgery_id' => $surgery->id,
                'hospital_id' => $surgery->hospital_id,
                'doctor_id' => $surgery->doctor_id,
                'surgical_checklist_id' => $surgery->checklist_id,
                'hospital_modality_config_id' => $surgery->hospital_modality_config_id,
                'surgery_type' => $surgery->checklist->surgery_type ?? 'No especificada',
                'surgery_date' => $surgery->surgery_datetime->toDateString(),
                'billing_legal_entity_id' => $billingLegalEntityId,
                'checklist_evaluation' => $checklistSnapshot,
                'status' => 'draft',
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            // 5. Crear items de la remisión desde el checklist evaluado
            //    (solo productos con cantidad > 0, los excluidos no generan items)
            $this->createItemsFromChecklist($shippingNote, $evaluatedItems);

            // 6. Recalcular totales financieros
            $shippingNote->recalculateTotals();

            Log::info("Remisión {$shippingNote->shipping_number} creada desde cirugía {$surgery->code}", [
                'items_created' => $shippingNote->items()->count(),
            ]);

            return $shippingNote;
        });
    }

    /**
     * Crear remisión con items editados por el usuario.
     * El usuario modifica precios, cantidades y modos de cobro antes de crear.
     */
    public function createFromSurgeryWithItems(
        ScheduledSurgery $surgery,
        int $billingLegalEntityId,
        array $userItems,
        float $taxRate = 0.16,
        ?string $notes = null
    ): ShippingNote {
        $this->validateSurgeryForShipping($surgery);

        return DB::transaction(function () use ($surgery, $billingLegalEntityId, $userItems, $taxRate, $notes) {

            // 1. Evaluar checklist (para snapshot de auditoría)
            $evaluatedItems = $surgery->getChecklistItemsWithConditionals();
            $excludedItems = $this->getExcludedItems($surgery);
            $checklistSnapshot = $this->buildChecklistSnapshot($evaluatedItems, $excludedItems);

            // 2. Crear la remisión
            $shippingNote = ShippingNote::create([
                'scheduled_surgery_id' => $surgery->id,
                'hospital_id' => $surgery->hospital_id,
                'doctor_id' => $surgery->doctor_id,
                'surgical_checklist_id' => $surgery->checklist_id,
                'hospital_modality_config_id' => $surgery->hospital_modality_config_id,
                'surgery_type' => $surgery->checklist->surgery_type ?? 'No especificada',
                'surgery_date' => $surgery->surgery_datetime->toDateString(),
                'billing_legal_entity_id' => $billingLegalEntityId,
                'checklist_evaluation' => $checklistSnapshot,
                'tax_rate' => $taxRate,
                'status' => 'draft',
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            // 3. Crear items con los valores editados por el usuario
            foreach ($userItems as $itemData) {
                $qty = (int) ($itemData['quantity'] ?? 0);
                if ($qty <= 0) continue; // Saltar items con cantidad 0

                $unitPrice = (float) ($itemData['unit_price'] ?? 0);
                $billingMode = $itemData['billing_mode'] ?? 'sale';
                $exclude = filter_var($itemData['exclude_from_invoice'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $source = $itemData['source'] ?? 'base';
                $origin = in_array($source, ['additional', 'conditional']) ? 'conditional' : 'standalone';

                ShippingNoteItem::create([
                    'shipping_note_id' => $shippingNote->id,
                    'item_origin' => $origin,
                    'product_id' => $itemData['product_id'],
                    'checklist_item_id' => $itemData['checklist_item_id'] ?? null,
                    'checklist_conditional_id' => $itemData['conditional_id'] ?? null,
                    'conditional_description' => $itemData['conditional_description'] ?? null,
                    'quantity_required' => $qty,
                    'billing_mode' => $billingMode,
                    'exclude_from_invoice' => $exclude,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $qty,
                    'status' => 'pending',
                ]);
            }

            // 4. Recalcular totales
            $shippingNote->recalculateTotals();

            Log::info("Remisión {$shippingNote->shipping_number} creada con items editados", [
                'items_created' => $shippingNote->items()->count(),
                'subtotal' => $shippingNote->subtotal,
            ]);

            return $shippingNote;
        });
    }

    // ═══════════════════════════════════════════════════════════
    // ASIGNAR PAQUETE PRE-ARMADO
    // ═══════════════════════════════════════════════════════════

    /**
     * Asignar un paquete pre-armado a la remisión.
     * Compara su contenido contra el checklist evaluado y crea los items.
     *
     * @param ShippingNote $shippingNote
     * @param PreAssembledPackage $package
     * @return ShippingNotePackage
     */
    public function assignPackage(
        ShippingNote $shippingNote,
        PreAssembledPackage $package
    ): ShippingNotePackage {
        $this->validateCanEdit($shippingNote);

        // Validar que el paquete esté disponible
        if ($package->status !== 'available') {
            throw new \Exception(
                "El paquete {$package->code} no está disponible (estado: {$package->status})."
            );
        }

        // Validar que no esté ya asignado a esta remisión
        $alreadyAssigned = $shippingNote->packages()
            ->where('pre_assembled_package_id', $package->id)
            ->exists();

        if ($alreadyAssigned) {
            throw new \Exception("El paquete {$package->code} ya está asignado a esta remisión.");
        }

        $result = DB::transaction(function () use ($shippingNote, $package) {

            // 1. Crear registro del paquete en la remisión
            $notePackage = ShippingNotePackage::create([
                'shipping_note_id' => $shippingNote->id,
                'pre_assembled_package_id' => $package->id,
                'surgical_checklist_id' => $shippingNote->surgical_checklist_id,
                'status' => 'assigned',
            ]);

            // 2. Obtener evaluación del checklist
            $evaluatedItems = $shippingNote->checklist_evaluation ?? [];

            // 3. Generar comparación y snapshot
            $notePackage->generateComparisonSnapshot($evaluatedItems);

            // 4. Crear items de la remisión desde el contenido del paquete
            $this->createItemsFromPackage($shippingNote, $notePackage, $package, $evaluatedItems);

            // 5. Reservar el paquete
            $package->update(['status' => 'in_use']);

            Log::info("Paquete {$package->code} asignado a remisión {$shippingNote->shipping_number}", [
                'items_created' => $notePackage->items()->count(),
                'completeness' => $notePackage->getCompletenessPercentage() . '%',
            ]);

            return $notePackage->fresh();
        });

        // Recalcular totales financieros
        $shippingNote->recalculateTotals();

        return $result;
    }

    /**
     * Remover un paquete de la remisión
     */
    public function removePackage(ShippingNote $shippingNote, ShippingNotePackage $notePackage): void
    {
        $this->validateCanEdit($shippingNote);

        DB::transaction(function () use ($shippingNote, $notePackage) {
            // Liberar el paquete físico
            $notePackage->preAssembledPackage->update(['status' => 'available']);

            // Eliminar items asociados a este paquete
            $shippingNote->items()
                ->where('shipping_note_package_id', $notePackage->id)
                ->delete();

            // Eliminar el registro del paquete
            $notePackage->delete();

            Log::info("Paquete removido de remisión {$shippingNote->shipping_number}");
        });

        // Recalcular totales financieros
        $shippingNote->recalculateTotals();
    }

    // ═══════════════════════════════════════════════════════════
    // ASIGNAR KIT QUIRÚRGICO (INSTRUMENTAL)
    // ═══════════════════════════════════════════════════════════

    /**
     * Asignar un kit de instrumental a la remisión.
     *
     * @param ShippingNote $shippingNote
     * @param SurgicalKit $kit
     * @param float $rentalPrice Precio de renta del kit
     * @param bool $excludeFromInvoice
     * @return ShippingNoteKit
     */
    public function assignKit(
        ShippingNote $shippingNote,
        SurgicalKit $kit,
        float $rentalPrice = 0,
        bool $excludeFromInvoice = false
    ): ShippingNoteKit {
        $this->validateCanEdit($shippingNote);

        // Validar que el kit esté activo
        if (!$kit->is_active) {
            throw new \Exception("El kit {$kit->code} no está activo.");
        }

        // Validar que no esté duplicado
        $alreadyAssigned = $shippingNote->kits()
            ->where('surgical_kit_id', $kit->id)
            ->exists();

        if ($alreadyAssigned) {
            throw new \Exception("El kit {$kit->code} ya está asignado a esta remisión.");
        }

        $result = DB::transaction(function () use ($shippingNote, $kit, $rentalPrice, $excludeFromInvoice) {

            // 1. Crear registro del kit en la remisión
            $noteKit = ShippingNoteKit::create([
                'shipping_note_id' => $shippingNote->id,
                'surgical_kit_id' => $kit->id,
                'rental_price' => $rentalPrice,
                'exclude_from_invoice' => $excludeFromInvoice,
                'status' => 'assigned',
            ]);

            // 2. Crear items individuales del kit (product_units disponibles)
            $this->createItemsFromKit($shippingNote, $noteKit, $kit);

            Log::info("Kit {$kit->code} asignado a remisión {$shippingNote->shipping_number}", [
                'rental_price' => $rentalPrice,
                'items_created' => $noteKit->items()->count(),
            ]);

            return $noteKit->fresh();
        });

        // Recalcular totales financieros
        $shippingNote->recalculateTotals();

        return $result;
    }

    /**
     * Remover un kit de la remisión
     */
    public function removeKit(ShippingNote $shippingNote, ShippingNoteKit $noteKit): void
    {
        $this->validateCanEdit($shippingNote);

        DB::transaction(function () use ($shippingNote, $noteKit) {
            // Eliminar items asociados a este kit
            $shippingNote->items()
                ->where('shipping_note_kit_id', $noteKit->id)
                ->delete();

            $noteKit->delete();

            Log::info("Kit removido de remisión {$shippingNote->shipping_number}");
        });

        // Recalcular totales financieros
        $shippingNote->recalculateTotals();
    }

    // ═══════════════════════════════════════════════════════════
    // AGREGAR ITEMS INDIVIDUALES (STANDALONE)
    // ═══════════════════════════════════════════════════════════

    /**
     * Agregar un producto individual a la remisión (fuera de paquete/kit)
     */
    public function addStandaloneItem(
        ShippingNote $shippingNote,
        int $productId,
        int $quantity,
        string $billingMode = 'sale',
        float $unitPrice = 0,
        ?int $productUnitId = null,
        bool $excludeFromInvoice = false,
        bool $isUrgency = false,
        ?string $urgencyReason = null
    ): ShippingNoteItem {
        $this->validateCanEdit($shippingNote);

        $data = [
            'shipping_note_id' => $shippingNote->id,
            'shipping_note_package_id' => null,
            'shipping_note_kit_id' => null,
            'item_origin' => 'standalone',
            'product_id' => $productId,
            'product_unit_id' => $productUnitId,
            'quantity_required' => $quantity,
            'billing_mode' => $billingMode,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'exclude_from_invoice' => $excludeFromInvoice,
            'is_urgency' => $isUrgency,
            'urgency_reason' => $urgencyReason,
            'status' => 'pending',
        ];

        // Si hay product_unit, tomar su origen
        if ($productUnitId) {
            $productUnit = ProductUnit::findOrFail($productUnitId);
            $data['source_legal_entity_id'] = $productUnit->legal_entity_id;
            $data['source_sub_warehouse_id'] = $productUnit->sub_warehouse_id;
        }

        $item = ShippingNoteItem::create($data);

        // Recalcular totales financieros
        $shippingNote->recalculateTotals();

        Log::info("Item standalone agregado a remisión {$shippingNote->shipping_number}", [
            'product_id' => $productId,
            'quantity' => $quantity,
            'is_urgency' => $isUrgency,
        ]);

        return $item;
    }

    /**
     * Remover un item individual de la remisión
     */
    public function removeItem(ShippingNote $shippingNote, ShippingNoteItem $item): void
    {
        $this->validateCanEdit($shippingNote);

        if ($item->shipping_note_id !== $shippingNote->id) {
            throw new \Exception('El item no pertenece a esta remisión.');
        }

        $item->delete();

        // Recalcular totales financieros
        $shippingNote->recalculateTotals();

        Log::info("Item {$item->id} removido de remisión {$shippingNote->shipping_number}");
    }

    /**
     * Actualizar un item (cantidad, precio, billing_mode, urgencia)
     * Solo en estado draft
     */
    public function updateItem(
        ShippingNote $shippingNote,
        ShippingNoteItem $item,
        array $data
    ): ShippingNoteItem {
        $this->validateCanEdit($shippingNote);

        if ($item->shipping_note_id !== $shippingNote->id) {
            throw new \Exception('El item no pertenece a esta remisión.');
        }

        $allowedFields = [
            'quantity_required',
            'billing_mode',
            'unit_price',
            'exclude_from_invoice',
            'product_unit_id',
            'is_urgency',
            'urgency_reason',
        ];

        $updateData = array_intersect_key($data, array_flip($allowedFields));

        // Recalcular total si cambiaron cantidad o precio
        if (isset($updateData['quantity_required']) || isset($updateData['unit_price'])) {
            $qty = $updateData['quantity_required'] ?? $item->quantity_required;
            $price = $updateData['unit_price'] ?? $item->unit_price;
            $updateData['total_price'] = $qty * $price;
        }

        // Si se asignó product_unit, actualizar origen
        if (isset($updateData['product_unit_id']) && $updateData['product_unit_id']) {
            $productUnit = ProductUnit::findOrFail($updateData['product_unit_id']);
            $updateData['source_legal_entity_id'] = $productUnit->legal_entity_id;
            $updateData['source_sub_warehouse_id'] = $productUnit->sub_warehouse_id;
        }

        $item->update($updateData);

        // Recalcular totales financieros
        $shippingNote->recalculateTotals();

        return $item->fresh();
    }

    /**
     * Actualizar tasa de IVA de la remisión
     */
    public function updateTaxRate(ShippingNote $shippingNote, float $taxRate): void
    {
        $this->validateCanEdit($shippingNote);

        $shippingNote->update(['tax_rate' => $taxRate]);
        $shippingNote->recalculateTotals();
    }

    // ═══════════════════════════════════════════════════════════
    // CONCEPTOS DE RENTA
    // ═══════════════════════════════════════════════════════════

    /**
     * Agregar concepto de renta
     */
    public function addRentalConcept(
        ShippingNote $shippingNote,
        string $concept,
        int $quantity,
        float $unitPrice,
        bool $excludeFromInvoice = false,
        ?string $notes = null
    ): ShippingNoteRentalConcept {
        $this->validateCanEdit($shippingNote);

        $result = ShippingNoteRentalConcept::create([
            'shipping_note_id' => $shippingNote->id,
            'concept' => $concept,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'exclude_from_invoice' => $excludeFromInvoice,
            'notes' => $notes,
        ]);

        $shippingNote->recalculateTotals();

        return $result;
    }

    /**
     * Remover concepto de renta
     */
    public function removeRentalConcept(ShippingNote $shippingNote, ShippingNoteRentalConcept $concept): void
    {
        $this->validateCanEdit($shippingNote);

        if ($concept->shipping_note_id !== $shippingNote->id) {
            throw new \Exception('El concepto no pertenece a esta remisión.');
        }

        $concept->delete();

        $shippingNote->recalculateTotals();
    }

    // ═══════════════════════════════════════════════════════════
    // FLUJO DE RETORNO
    // ═══════════════════════════════════════════════════════════

    /**
     * Registrar retorno de cirugía.
     * Recibe un array indicando qué items regresaron y cuáles no.
     *
     * @param ShippingNote $shippingNote
     * @param array $returnData Array de ['item_id' => int, 'returned' => bool, 'quantity_returned' => int|null]
     * @return array Resumen del retorno
     */
    public function registerReturn(ShippingNote $shippingNote, array $returnData): array
    {
        if (!$shippingNote->canRegisterReturn()) {
            throw new \Exception('No se puede registrar retorno en el estado actual de la remisión.');
        }

        $summary = [
            'total_items' => 0,
            'returned' => 0,
            'used' => 0,
            'errors' => [],
        ];

        DB::transaction(function () use ($shippingNote, $returnData, &$summary) {

            foreach ($returnData as $entry) {
                $item = ShippingNoteItem::find($entry['item_id']);

                if (!$item || $item->shipping_note_id !== $shippingNote->id) {
                    $summary['errors'][] = "Item ID {$entry['item_id']} no encontrado en esta remisión.";
                    continue;
                }

                if (!in_array($item->status, ['sent', 'in_surgery'])) {
                    $summary['errors'][] = "Item ID {$entry['item_id']} no está en estado enviado/cirugía.";
                    continue;
                }

                $summary['total_items']++;

                if ($entry['returned'] ?? false) {
                    $qtyReturned = $entry['quantity_returned'] ?? $item->quantity_sent;
                    $item->markAsReturned($qtyReturned);
                    $summary['returned']++;
                } else {
                    $item->markAsUsed();
                    $summary['used']++;
                }
            }

            // Evaluar retorno de kits
            foreach ($shippingNote->kits as $kit) {
                $kit->evaluateReturn();
            }

            // Evaluar retorno de paquetes
            foreach ($shippingNote->packages as $package) {
                $allReturned = $package->items()
                    ->whereIn('status', ['sent', 'in_surgery'])
                    ->doesntExist();

                if ($allReturned) {
                    $package->update(['status' => 'reviewed']);
                } else {
                    $package->update(['status' => 'returned']);
                }
            }

            // Recalcular totales de todos los items
            foreach ($shippingNote->items as $item) {
                $item->recalculateTotal();
            }

            // Actualizar estado de la remisión
            $shippingNote->update([
                'status' => 'returned',
                'returned_at' => now(),
            ]);
        });

        Log::info("Retorno registrado para remisión {$shippingNote->shipping_number}", $summary);

        return $summary;
    }

    /**
     * Completar remisión después de revisar el retorno (returned → completed)
     */
    public function complete(ShippingNote $shippingNote): void
    {
        if ($shippingNote->status !== 'returned') {
            throw new \Exception('La remisión debe estar en estado "retornada" para completarse.');
        }

        // Liberar paquetes pre-armados
        foreach ($shippingNote->packages as $notePackage) {
            $package = $notePackage->preAssembledPackage;
            $package->markAsUsed();
            $package->update(['status' => 'available']);
        }

        $shippingNote->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Log::info("Remisión {$shippingNote->shipping_number} completada");
    }

    // ═══════════════════════════════════════════════════════════
    // RE-EVALUAR CHECKLIST
    // ═══════════════════════════════════════════════════════════

    /**
     * Re-evaluar el checklist y actualizar la remisión.
     * Útil cuando cambian datos de la cirugía (doctor, hospital, etc.)
     */
    public function reevaluateChecklist(ShippingNote $shippingNote): array
    {
        $this->validateCanEdit($shippingNote);

        $surgery = $shippingNote->scheduledSurgery;
        $evaluatedItems = $surgery->getChecklistItemsWithConditionals();
        $excludedItems = $this->getExcludedItems($surgery);
        $snapshot = $this->buildChecklistSnapshot($evaluatedItems, $excludedItems);

        $shippingNote->update(['checklist_evaluation' => $snapshot]);

        Log::info("Checklist re-evaluado para remisión {$shippingNote->shipping_number}");

        return $snapshot;
    }

    // ═══════════════════════════════════════════════════════════
    // RESUMEN / PREVIEW
    // ═══════════════════════════════════════════════════════════

    /**
     * Obtener preview completo de la remisión para la vista
     */
    public function getFullPreview(ShippingNote $shippingNote): array
    {
        $shippingNote->load([
            'hospital',
            'doctor',
            'surgicalChecklist',
            'billingLegalEntity',
            'packages.preAssembledPackage',
            'packages.items.product',
            'items.product.productType',
            'items.productUnit',
            'items.sourceLegalEntity',
            'rentalConcepts',
        ]);

        // Separar items por tipo de producto: consumibles vs instrumental
        $instrumentalTypes = ['equipo', 'instrumental', 'set', 'caja', 'charola'];

        $consumableItems = $shippingNote->items->filter(function ($item) use ($instrumentalTypes) {
            $type = strtolower(trim($item->product->productType->name ?? ''));
            return !in_array($type, $instrumentalTypes);
        });

        $instrumentalItems = $shippingNote->items->filter(function ($item) use ($instrumentalTypes) {
            $type = strtolower(trim($item->product->productType->name ?? ''));
            return in_array($type, $instrumentalTypes);
        });

        // Agrupar instrumental por producto (nombre + tipo)
        $instrumentalGrouped = $instrumentalItems->groupBy('product_id')->map(function ($items) {
            $first = $items->first();
            return [
                'product_id' => $first->product_id,
                'product_name' => $first->product->name ?? 'N/A',
                'product_code' => $first->product->code ?? '',
                'product_type' => $first->product->productType->name ?? 'Instrumental',
                'is_composite' => $first->product->is_composite ?? false,
                'quantity' => $items->sum('quantity_required'),
                'total_price' => $items->sum('total_price'),
                'items_count' => $items->count(),
                'status' => $first->status,
                'exclude_from_invoice' => $first->exclude_from_invoice,
            ];
        })->values();

        return [
            'shipping_note' => $shippingNote,
            'checklist_evaluation' => $shippingNote->checklist_evaluation ?? [],
            'packages' => $shippingNote->packages->map(fn($pkg) => [
                'id' => $pkg->id,
                'package' => $pkg->preAssembledPackage,
                'completeness' => $pkg->getCompletenessPercentage(),
                'missing_items' => $pkg->getMissingItems(),
                'items_count' => $pkg->getTotalItems(),
                'status' => $pkg->status,
            ]),
            'instrumental' => $instrumentalGrouped,
            'consumable_items' => $consumableItems,
            'instrumental_items' => $instrumentalItems,
            'items_by_origin' => [
                'package' => $consumableItems->where('item_origin', 'package'),
                'standalone' => $consumableItems->where('item_origin', 'standalone'),
                'conditional' => $consumableItems->where('item_origin', 'conditional'),
            ],
            'rental_concepts' => $shippingNote->rentalConcepts,
            'totals' => $shippingNote->getTotals(),
            'stats' => [
                'total_items' => $consumableItems->count(),
                'total_instrumental' => $instrumentalGrouped->count(),
                'items_by_origin' => $shippingNote->getItemsByOrigin(),
                'sent_items' => $shippingNote->getSentItems(),
                'returned_items' => $shippingNote->getReturnedItems(),
                'used_items' => $shippingNote->getUsedItems(),
            ],
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS PRIVADOS: CREACIÓN DE ITEMS
    // ═══════════════════════════════════════════════════════════

    /**
     * Crear items de la remisión directamente desde el checklist evaluado.
     * Se ejecuta al crear la remisión para que tenga items desde el inicio,
     * sin necesidad de asignar un paquete primero.
     * 
     * Los items se crean como 'standalone' sin product_unit asignado (pendiente de picking/RFID).
     * Si después se asigna un paquete, se pueden cruzar/actualizar.
     */
    private function createItemsFromChecklist(
        ShippingNote $shippingNote,
        \Illuminate\Support\Collection $evaluatedItems
    ): void {
        foreach ($evaluatedItems as $data) {
            $checklistItem = $data['item'] ?? null;
            $conditional = $data['conditional'] ?? null;
            $productId = $data['product_id'];
            $quantity = $data['adjusted_quantity'];

            // No crear items con cantidad 0 (excluidos)
            if ($quantity <= 0) {
                continue;
            }

            // Obtener precio de lista del producto
            $product = $checklistItem?->product;
            $unitPrice = (float) ($product?->list_price ?? 0);

            // Determinar origen
            $origin = ($data['source'] ?? 'base') === 'additional' ? 'conditional' : 'standalone';

            // Determinar billing mode (condicionales con exclude_from_invoice → no_charge)
            $excludeFromInvoice = $conditional?->exclude_from_invoice ?? false;
            $billingMode = $excludeFromInvoice ? 'no_charge' : 'sale';

            ShippingNoteItem::create([
                'shipping_note_id' => $shippingNote->id,
                'shipping_note_package_id' => null,
                'shipping_note_kit_id' => null,
                'item_origin' => $origin,
                'product_id' => $productId,
                'product_unit_id' => null, // Se asigna después por RFID/picking
                'checklist_item_id' => $checklistItem?->id,
                'checklist_conditional_id' => $conditional?->id ?? null,
                'conditional_description' => $data['conditional_description'] ?? null,
                'quantity_required' => $quantity,
                'billing_mode' => $billingMode,
                'exclude_from_invoice' => $excludeFromInvoice,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $quantity,
                'status' => 'pending',
            ]);
        }

        Log::info("Items creados desde checklist para remisión {$shippingNote->shipping_number}", [
            'total_items' => $shippingNote->items()->count(),
        ]);
    }

    /**
     * Crear items de la remisión desde el contenido de un paquete pre-armado.
     * Cruza el contenido real del paquete con la evaluación del checklist.
     */
    private function createItemsFromPackage(
        ShippingNote $shippingNote,
        ShippingNotePackage $notePackage,
        PreAssembledPackage $package,
        array $evaluatedItems
    ): void {
        // Obtener contenido real del paquete (agrupado por producto)
        $packageContents = $package->contents()
            ->with(['product', 'productUnit'])
            ->get();

        // Indexar evaluación por product_id para búsqueda rápida
        $evaluationByProduct = collect($evaluatedItems)->keyBy('product_id');

        // Crear un item por cada unidad física en el paquete
        foreach ($packageContents as $content) {
            $evalItem = $evaluationByProduct->get($content->product_id);

            $conditionalId = null;
            $conditionalDescription = null;
            $excludeFromInvoice = false;

            if ($evalItem && isset($evalItem['has_conditional']) && $evalItem['has_conditional']) {
                $conditionalId = $evalItem['conditional_id'] ?? null;
                $conditionalDescription = $evalItem['conditional_description'] ?? null;
                $excludeFromInvoice = $evalItem['exclude_from_invoice'] ?? false;
            }

            ShippingNoteItem::create([
                'shipping_note_id' => $shippingNote->id,
                'shipping_note_package_id' => $notePackage->id,
                'shipping_note_kit_id' => null,
                'item_origin' => 'package',
                'product_id' => $content->product_id,
                'product_unit_id' => $content->product_unit_id,
                'checklist_item_id' => $evalItem['checklist_item_id'] ?? null,
                'checklist_conditional_id' => $conditionalId,
                'conditional_description' => $conditionalDescription,
                'source_legal_entity_id' => $content->productUnit->legal_entity_id ?? null,
                'source_sub_warehouse_id' => $content->productUnit->sub_warehouse_id ?? null,
                'quantity_required' => 1, // Cada content es una unidad física
                'billing_mode' => 'sale', // Consumibles → venta
                'exclude_from_invoice' => $excludeFromInvoice,
                'unit_price' => $content->product->list_price ?? 0,
                'total_price' => $content->product->list_price ?? 0,
                'status' => 'pending',
            ]);
        }

        // Buscar productos del checklist que NO están en el paquete (faltantes)
        $packageProductIds = $packageContents->pluck('product_id')->unique();

        foreach ($evaluatedItems as $evalItem) {
            $productId = $evalItem['product_id'];

            // Si ya está en el paquete, saltar
            if ($packageProductIds->contains($productId)) {
                continue;
            }

            // Crear item como "condicional" o sin product_unit (pendiente de asignar)
            $origin = ($evalItem['source'] ?? 'base') === 'additional' ? 'conditional' : 'package';

            ShippingNoteItem::create([
                'shipping_note_id' => $shippingNote->id,
                'shipping_note_package_id' => $notePackage->id,
                'shipping_note_kit_id' => null,
                'item_origin' => $origin,
                'product_id' => $productId,
                'product_unit_id' => null, // Pendiente de asignar
                'checklist_item_id' => $evalItem['checklist_item_id'] ?? null,
                'checklist_conditional_id' => $evalItem['conditional_id'] ?? null,
                'conditional_description' => $evalItem['conditional_description'] ?? null,
                'quantity_required' => $evalItem['adjusted_quantity'] ?? $evalItem['final_quantity'] ?? 1,
                'billing_mode' => 'sale',
                'exclude_from_invoice' => $evalItem['exclude_from_invoice'] ?? false,
                'unit_price' => 0,
                'total_price' => 0,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Crear items de la remisión desde un kit de instrumental.
     * Busca ProductUnits disponibles para cada producto del kit.
     */
    private function createItemsFromKit(
        ShippingNote $shippingNote,
        ShippingNoteKit $noteKit,
        SurgicalKit $kit
    ): void {
        foreach ($kit->items as $kitItem) {
            // Buscar product_units disponibles para este producto
            $availableUnits = ProductUnit::where('product_id', $kitItem->product_id)
                ->where('status', 'available')
                ->with(['product'])
                ->limit($kitItem->quantity)
                ->get();

            if ($availableUnits->isEmpty()) {
                // Crear item sin product_unit (se asignará después o es faltante)
                ShippingNoteItem::create([
                    'shipping_note_id' => $shippingNote->id,
                    'shipping_note_package_id' => null,
                    'shipping_note_kit_id' => $noteKit->id,
                    'item_origin' => 'kit',
                    'product_id' => $kitItem->product_id,
                    'product_unit_id' => null,
                    'quantity_required' => $kitItem->quantity,
                    'billing_mode' => 'rental',
                    'unit_price' => 0,
                    'total_price' => 0,
                    'status' => 'pending',
                ]);
                continue;
            }

            // Crear un item por cada unidad disponible (hasta la cantidad requerida)
            foreach ($availableUnits as $unit) {
                ShippingNoteItem::create([
                    'shipping_note_id' => $shippingNote->id,
                    'shipping_note_package_id' => null,
                    'shipping_note_kit_id' => $noteKit->id,
                    'item_origin' => 'kit',
                    'product_id' => $kitItem->product_id,
                    'product_unit_id' => $unit->id,
                    'source_legal_entity_id' => $unit->legal_entity_id,
                    'source_sub_warehouse_id' => $unit->sub_warehouse_id,
                    'quantity_required' => 1,
                    'billing_mode' => 'rental', // Instrumental → renta
                    'unit_price' => 0, // Precio va en el kit, no en el item
                    'total_price' => 0,
                    'status' => 'pending',
                ]);
            }
        }
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS PRIVADOS: VALIDACIÓN
    // ═══════════════════════════════════════════════════════════

    /**
     * Validar que la cirugía puede generar una remisión
     */
    private function validateSurgeryForShipping(ScheduledSurgery $surgery): void
    {
        if (!$surgery->checklist_id) {
            throw new \Exception('La cirugía no tiene checklist asignado.');
        }

        // Verificar que no tenga ya una remisión activa
        $existingNote = ShippingNote::where('scheduled_surgery_id', $surgery->id)
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existingNote) {
            throw new \Exception(
                "La cirugía ya tiene una remisión activa: {$existingNote->shipping_number}"
            );
        }
    }

    /**
     * Validar que la remisión puede ser editada
     */
    private function validateCanEdit(ShippingNote $shippingNote): void
    {
        if (!$shippingNote->canBeEdited()) {
            throw new \Exception(
                "La remisión {$shippingNote->shipping_number} no puede ser editada (estado: {$shippingNote->status})."
            );
        }
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS PRIVADOS: SNAPSHOT
    // ═══════════════════════════════════════════════════════════

    /**
     * Construir snapshot JSON de la evaluación del checklist.
     * Se guarda en shipping_notes.checklist_evaluation para auditoría.
     * Incluye items excluidos/reemplazados para visibilidad completa.
     */
    private function buildChecklistSnapshot(Collection $evaluatedItems, ?Collection $excludedItems = null): array
    {
        $snapshot = $evaluatedItems->map(function ($item) {
            $checklistItem = $item['item'] ?? null;
            $conditional = $item['conditional'] ?? null;

            return [
                'checklist_item_id' => $checklistItem?->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'base_quantity' => $item['base_quantity'],
                'final_quantity' => $item['adjusted_quantity'],
                'has_conditional' => $item['has_conditional'],
                'conditional_id' => $conditional?->id ?? null,
                'conditional_description' => $item['conditional_description'],
                'action_type' => $conditional?->action_type ?? null,
                'exclude_from_invoice' => $conditional?->exclude_from_invoice ?? false,
                'is_mandatory' => $item['is_mandatory'],
                'source' => $item['source'],
            ];
        });

        // Agregar items excluidos/reemplazados para auditoría
        if ($excludedItems && $excludedItems->isNotEmpty()) {
            $excluded = $excludedItems->map(function ($item) {
                return [
                    'checklist_item_id' => $item['checklist_item_id'],
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'base_quantity' => $item['base_quantity'],
                    'final_quantity' => 0,
                    'has_conditional' => true,
                    'conditional_id' => $item['conditional_id'] ?? null,
                    'conditional_description' => $item['conditional_description'],
                    'action_type' => $item['action_type'],
                    'exclude_from_invoice' => true,
                    'is_mandatory' => $item['is_mandatory'],
                    'source' => 'excluded',
                ];
            });
            $snapshot = $snapshot->concat($excluded);
        }

        return $snapshot->values()->toArray();
    }

    /**
     * Obtener items que fueron excluidos o reemplazados por condicionales.
     * Estos no aparecen en getChecklistItemsWithConditionals() porque tienen final_quantity = 0.
     */
    private function getExcludedItems(ScheduledSurgery $surgery): Collection
    {
        if (!$surgery->checklist_id) {
            return collect();
        }

        $baseItems = ChecklistItem::where('checklist_id', $surgery->checklist_id)
            ->with(['product', 'conditionals.targetProduct'])
            ->ordered()
            ->get();

        $excluded = collect();

        foreach ($baseItems as $item) {
            $adjustedData = $item->getAdjustedQuantity($surgery);

            // Solo nos interesan items con final_quantity = 0 que tengan condicional
            if ($adjustedData['final_quantity'] === 0 && $adjustedData['has_conditional']) {
                $conditional = $adjustedData['conditional'];
                $excluded->push([
                    'checklist_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'base_quantity' => $adjustedData['base_quantity'],
                    'conditional_id' => $conditional?->id,
                    'conditional_description' => $adjustedData['conditional_description'],
                    'action_type' => $conditional?->action_type,
                    'is_mandatory' => $item->is_mandatory ?? true,
                ]);
            }
        }

        return $excluded;
    }
}