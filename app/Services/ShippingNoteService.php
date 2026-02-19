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
    // ═══════════════════════════════════════════════════════════
    // CREAR REMISIÓN DESDE CIRUGÍA PROGRAMADA
    // ═══════════════════════════════════════════════════════════

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

            // 1. Evaluar checklist con condicionales
            $evaluatedItems = $surgery->getChecklistItemsWithConditionals();
            $checklistSnapshot = $this->buildChecklistSnapshot($evaluatedItems);

            Log::info("Checklist evaluado para cirugía {$surgery->code}", [
                'total_items' => $evaluatedItems->count(),
                'with_conditionals' => $evaluatedItems->where('has_conditional', true)->count(),
            ]);

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
                'status' => 'draft',
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            Log::info("Remisión {$shippingNote->shipping_number} creada desde cirugía {$surgery->code}");

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

        return DB::transaction(function () use ($shippingNote, $package) {

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

        return DB::transaction(function () use ($shippingNote, $kit, $rentalPrice, $excludeFromInvoice) {

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
        bool $excludeFromInvoice = false
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
            'status' => 'pending',
        ];

        // Si hay product_unit, tomar su origen
        if ($productUnitId) {
            $productUnit = ProductUnit::findOrFail($productUnitId);
            $data['source_legal_entity_id'] = $productUnit->legal_entity_id;
            $data['source_sub_warehouse_id'] = $productUnit->sub_warehouse_id;
        }

        $item = ShippingNoteItem::create($data);

        Log::info("Item standalone agregado a remisión {$shippingNote->shipping_number}", [
            'product_id' => $productId,
            'quantity' => $quantity,
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

        Log::info("Item {$item->id} removido de remisión {$shippingNote->shipping_number}");
    }

    /**
     * Actualizar un item (cantidad, precio, billing_mode)
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

        return $item->fresh();
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

        return ShippingNoteRentalConcept::create([
            'shipping_note_id' => $shippingNote->id,
            'concept' => $concept,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice, // boot lo recalcula, pero por claridad
            'exclude_from_invoice' => $excludeFromInvoice,
            'notes' => $notes,
        ]);
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
        $snapshot = $this->buildChecklistSnapshot($evaluatedItems);

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
            'kits.surgicalKit',
            'kits.items.product',
            'items.product',
            'items.productUnit',
            'items.sourceLegalEntity',
            'rentalConcepts',
        ]);

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
            'kits' => $shippingNote->kits->map(fn($kit) => [
                'id' => $kit->id,
                'kit' => $kit->surgicalKit,
                'rental_price' => $kit->rental_price,
                'exclude_from_invoice' => $kit->exclude_from_invoice,
                'items_count' => $kit->getTotalItems(),
                'status' => $kit->status,
            ]),
            'items_by_origin' => [
                'package' => $shippingNote->items->where('item_origin', 'package'),
                'kit' => $shippingNote->items->where('item_origin', 'kit'),
                'standalone' => $shippingNote->items->where('item_origin', 'standalone'),
                'conditional' => $shippingNote->items->where('item_origin', 'conditional'),
            ],
            'rental_concepts' => $shippingNote->rentalConcepts,
            'totals' => $shippingNote->getTotals(),
            'stats' => [
                'total_items' => $shippingNote->getTotalItems(),
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
     */
    private function buildChecklistSnapshot(Collection $evaluatedItems): array
    {
        return $evaluatedItems->map(function ($item) {
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
        })->values()->toArray();
    }
}