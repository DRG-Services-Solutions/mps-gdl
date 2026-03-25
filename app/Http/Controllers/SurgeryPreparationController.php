<?php

namespace App\Http\Controllers;

use App\Models\ScheduledSurgery;
use App\Models\SurgeryPreparation;
use App\Models\PreAssembledPackage;
use App\Models\PackageContent;
use App\Models\ChecklistItem;
use App\Services\PreparationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SurgeryPreparationController extends Controller
{
    protected $preparationService;

    public function __construct(PreparationService $preparationService)
    {
        $this->preparationService = $preparationService;
    }

    // ============================================
    // FASE 1: INICIO Y SELECCIÓN DE PAQUETE
    // ============================================

    /**
     * Iniciar flujo de preparación
     */
    public function start(ScheduledSurgery $surgery)
    {
        Log::info("Iniciando flujo de preparación para Cirugía ID: {$surgery->id}");

        if ($surgery->status !== 'scheduled') {
            Log::warning("Intento de inicio fallido: Estado actual es {$surgery->status}");
            return redirect()->back()->with('error', 'Esta cirugía ya está en proceso o completada.');
        }

        if (!$surgery->checklist_id) {
            return redirect()->back()->with('error', 'Esta cirugía no tiene un checklist asignado.');
        }

        return redirect()->route('surgeries.preparations.selectPackage', $surgery);
    }

    /**
     * Mostrar vista de selección de paquete
     * 
     * FIX N+1: Precalcula completitud y expiración usando colecciones
     * en vez de ejecutar queries por cada paquete.
     */
    public function selectPackage(ScheduledSurgery $surgery)
    {
        Log::info("Accediendo a vista de selección de paquete para Cirugía ID: {$surgery->id}");
        
        $surgery->load(['checklist', 'preparation', 'hospitalModalityConfig.modality']);

        // Si ya tiene una preparación activa, redirigir al paso correspondiente
        $existingPreparation = $surgery->preparation;
        if ($existingPreparation && !in_array($existingPreparation->status, ['cancelled'])) {
            $redirectRoute = match($existingPreparation->status) {
                'picking'    => 'surgeries.preparations.picking',
                'comparing'  => 'surgeries.preparations.compare',
                'complete', 'verified' => 'surgeries.preparations.compare',
                default      => 'surgeries.preparations.compare',
            };

            return redirect()->route($redirectRoute, $surgery)
                ->with('info', "Esta cirugía ya tiene una preparación en estado: {$existingPreparation->status}. Continuando desde donde quedó.");
        }

        try {
            // UNA sola consulta para los items del checklist
            $checklistItems = ChecklistItem::where('checklist_id', $surgery->checklist_id)->get();
            $checklistProductIds = $checklistItems->pluck('product_id')->unique();
            $totalItems = $checklistItems->count();

            // Eager load paquetes CON contents.productUnit para evitar N+1
            $availablePackages = PreAssembledPackage::available()
                ->with(['contents.product', 'contents.productUnit', 'storageLocation'])
                ->get()
                ->map(function ($package) use ($checklistProductIds, $totalItems) {
                    // Completitud: sin queries, pura colección
                    if ($totalItems > 0) {
                        $packageProductIds = $package->contents->pluck('product_id')->unique();
                        $package->completeness = round(
                            ($checklistProductIds->intersect($packageProductIds)->count() / $totalItems) * 100, 2
                        );
                    } else {
                        $package->completeness = 0;
                    }

                    // Expiración: sin queries, usa la relación eager loaded
                    $package->has_expired = $package->contents->contains(function ($content) {
                        $unit = $content->productUnit;
                        return $unit 
                            && $unit->expiration_date !== null 
                            && $unit->expiration_date->lt(now());
                    });

                    return $package;
                })
                ->sortByDesc('completeness');

            return view('surgeries.preparations.select-package', compact('surgery', 'availablePackages'));

        } catch (\Exception $e) {
            Log::error("Error en selectPackage: " . $e->getMessage());
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'Error al cargar paquetes disponibles.');
        }
    }

    /**
     * Asignar paquete y crear preparación
     * 
     * FIX: Ahora acepta package_id nullable para "preparar desde cero"
     */
    public function assignPackage(Request $request, ScheduledSurgery $surgery)
    {
        // FIX: nullable para permitir preparación sin paquete
        $validated = $request->validate([
            'package_id' => 'nullable|exists:pre_assembled_packages,id'
        ]);

        $packageId = $validated['package_id'] ?: null;

        Log::info("Asignando paquete " . ($packageId ?? 'NINGUNO (desde cero)') . " a Cirugía: {$surgery->id}");

        try {
            // FIX: Validar disponibilidad del paquete antes de asignar
            if ($packageId) {
                $package = PreAssembledPackage::findOrFail($packageId);
                
                if ($package->status !== 'available') {
                    return redirect()->back()
                        ->with('error', "Este paquete no está disponible (Estado: {$package->status}).");
                }
            }

            $preparation = $this->preparationService->createPreparation(
                $surgery->id, 
                $packageId, 
                auth()->id()
            );

            Log::info("Preparación creada exitosamente. ID: {$preparation->id}");

            return redirect()->route('surgeries.preparations.compare', $surgery)
                ->with('success', $packageId 
                    ? 'Paquete asignado. Revisa las diferencias entre checklist y paquete.'
                    : 'Preparación iniciada desde cero. Todos los productos deben ser escaneados.');

        } catch (\Exception $e) {
            Log::error("Error al asignar paquete: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al crear preparación: ' . $e->getMessage());
        }
    }

    // ============================================
    // FASE 2: COMPARACIÓN Y ANÁLISIS
    // ============================================

    /**
     * Mostrar comparación entre checklist y contenido del paquete
     */
    public function compare(ScheduledSurgery $surgery)
    {
        Log::info("Accediendo a comparación para Cirugía ID: {$surgery->id}");

        $surgery->load([
            'preparation.preAssembledPackage.storageLocation',
            'preparation.items.product',
            'preparation.items.checklistItem.conditionals' => fn($q) => $q->with(['doctor', 'hospital', 'modality', 'targetProduct']),
            'preparation.items.storageLocation',
        ]);

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'No hay preparación activa para esta cirugía.');
        }

        $summary = $this->preparationService->getPreparationSummary($preparation->id);

        $itemsComplete = $preparation->items->filter(function($item) {
            return $item->quantity_missing <= 0;
        });
        
        $itemsPending = $preparation->items->filter(function($item) {
            return $item->quantity_missing > 0;
        });

        // Contenido del paquete (solo si existe)
        $packageContents = collect();
        $packageExtras = collect();

        if ($preparation->pre_assembled_package_id) {
            $packageContents = PackageContent::where('pre_assembled_package_id', $preparation->pre_assembled_package_id)
                ->with('product')
                ->get()
                ->groupBy('product_id')
                ->map(function($items) {
                    return [
                        'product' => $items->first()->product,
                        'total_quantity' => $items->sum('quantity'),
                        'units' => $items->pluck('product_unit_id')->filter()->values(),
                    ];
                });

            // Detectar productos en el paquete que NO están en la preparación
            $prepProductIds = $preparation->items->pluck('product_id')->unique()->toArray();

            $packageExtras = $packageContents->filter(function($content, $productId) use ($prepProductIds) {
                return !in_array($productId, $prepProductIds);
            });
        }
        $excludedByConditionals = $this->getExcludedByConditionals($surgery);


        return view('surgeries.preparations.compare', compact(
            'surgery',
            'preparation',
            'summary',
            'itemsComplete',
            'itemsPending',
            'packageContents',
            'packageExtras',
            'excludedByConditionals'
        ));
    }

    // ============================================
    // FASE 3: SURTIDO (PICKING)
    // ============================================

    /**
     * Vista de surtido de productos faltantes
     */
    public function picking(ScheduledSurgery $surgery)
    {
        Log::info("Accediendo a picking para Cirugía ID: {$surgery->id}");

        $surgery->load([
                'preparation.items.product',
                'preparation.items.storageLocation',
                'preparation.items.checklistItem.conditionals' => fn($q) => $q->with(['doctor', 'hospital', 'modality', 'targetProduct']),
                'preparation.preAssembledPackage.surgeryChecklist.items.conditionals',
                'preparation.preAssembledPackage.storageLocation',
        ]);
        
        $preparation = $surgery->preparation;

        if (!$preparation) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'No se encontró una preparación activa para esta cirugía.');
        }

        if (in_array($preparation->status, ['scheduled', 'comparing'])) {
            $preparation->update(['status' => 'picking']);
        }

        // Obtener resumen usando el servicio
        $summary = $this->preparationService->getPreparationSummary($preparation->id);
        
        $this->preparationService->reevaluateAllConditionals($preparation);

        $pendingItems = $preparation->items->where('quantity_missing', '>', 0)->values();

        // Obtener productos excluidos/reemplazados por condicionales
        $excludedByConditionals = $this->getExcludedByConditionals($surgery);

        return view('surgeries.preparations.picking', compact(
            'surgery',
            'preparation',
            'pendingItems',
            'summary',
            'excludedByConditionals'
        ));
    }

    /**
     * Registrar producto escaneado vía RFID
     */
    public function scanProduct(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'epc' => 'required|string|max:255',
        ]);

        Log::info("Escaneando EPC: {$validated['epc']} para Cirugía ID: {$surgery->id}");

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json([
                'success' => false,
                'message' => 'No hay preparación activa para esta cirugía.'
            ], 404);
        }

        try {
            $result = $this->preparationService->pickProduct(
                $preparation->id,
                $validated['epc'],
                auth()->id()
            );

            $item = $preparation->items()->find($result['item_id']);

            return response()->json([
                'success' => true,
                'message' => "✓ Producto agregado: {$result['product_name']}",
                'data' => [
                    'item_id' => $result['item_id'],
                    'quantity_missing' => $result['quantity_missing'],
                    'quantity_picked' => $item->quantity_picked,
                    'item_status' => $result['item_status'],
                    'preparation_complete' => $result['preparation_complete'],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error al escanear producto: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ============================================
    // FASE 4: VERIFICACIÓN Y FINALIZACIÓN
    // ============================================

    /**
     * Verificar y completar preparación
     * 
     * FIX PRINCIPAL: Blindaje completo server-side
     */
    public function verify(ScheduledSurgery $surgery)
    {
        Log::info("Verificando preparación para Cirugía ID: {$surgery->id}");

        $preparation = $surgery->preparation;

        // 1. Validar existencia
        if (!$preparation) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'No hay preparación activa.');
        }

        // 2. FIX: Validar estado válido para verificación
        if (!in_array($preparation->status, ['picking', 'complete', 'comparing'])) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', "La preparación está en estado '{$preparation->status}' y no puede ser verificada.");
        }

        // 3. FIX: RECALCULAR server-side con query directa (NO confiar en caché/frontend)
        $mandatoryIncomplete = $preparation->items()
            ->where('is_mandatory', true)
            ->where('quantity_missing', '>', 0)
            ->count();

        if ($mandatoryIncomplete > 0) {
            Log::warning("Intento de verificación con {$mandatoryIncomplete} items obligatorios pendientes");
            return redirect()->route('surgeries.preparations.compare', $surgery)
                ->with('error', "No se puede verificar: {$mandatoryIncomplete} producto(s) obligatorio(s) aún pendiente(s).");
        }

        try {
            DB::beginTransaction();

            // 4. Finalizar preparación usando el servicio (tiene su propia validación)
            $this->preparationService->finishPreparation(
                $preparation->id,
                auth()->id(),
                'Verificación completada exitosamente'
            );

            // 5. FIX: Solo actualizar paquete si existe
            if ($preparation->preAssembledPackage) {
                $preparation->preAssembledPackage->update(['status' => 'in_surgery']);

                // Actualizar product_units del paquete
                DB::table('product_units')
                    ->where('current_package_id', $preparation->pre_assembled_package_id)
                    ->where('current_status', 'reserved')
                    ->update([
                        'current_status' => 'in_use',
                        'current_surgery_id' => $surgery->id,
                        'updated_at' => now(),
                    ]);
            } else {
                // Sin paquete: actualizar units reservadas para esta cirugía
                DB::table('product_units')
                    ->where('current_surgery_id', $surgery->id)
                    ->where('current_status', 'reserved')
                    ->update([
                        'current_status' => 'in_use',
                        'updated_at' => now(),
                    ]);
            }

            // 6. FIX: Actualizar estado de la cirugía
            $surgery->update(['status' => 'prepared']);

            DB::commit();

            Log::info("Preparación verificada y completada. ID: {$preparation->id}");

            // 7. FIX: Redirigir a summary (no a show)
            return redirect()->route('surgeries.preparations.summary', $surgery)
                ->with('success', '✓ Preparación verificada y lista para cirugía.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al verificar preparación: " . $e->getMessage());
            return back()->with('error', 'Error al verificar: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar preparación
     */
    public function cancel(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        Log::info("Cancelando preparación para Cirugía ID: {$surgery->id}");

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json([
                'success' => false,
                'message' => 'No hay preparación activa para cancelar.'
            ]);
        }

        try {
            $this->preparationService->cancelPreparation(
                $preparation->id,
                auth()->id(),
                $validated['reason']
            );

            Log::info("Preparación cancelada. ID: {$preparation->id}");

            return response()->json([
                'success' => true,
                'message' => 'Preparación cancelada correctamente.'
            ]);

        } catch (\Exception $e) {
            Log::error("Error al cancelar preparación: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // CONSULTAS Y REPORTES
    // ============================================

    /**
     * Mostrar resumen completo de preparación
     */
    public function summary(ScheduledSurgery $surgery)
    {
        Log::info("Accediendo a resumen para Cirugía ID: {$surgery->id}");

        $surgery->load([
            'preparation.items.product',
            'preparation.preAssembledPackage.storageLocation',
            'preparation.scheduledSurgery.patient',
            'preparation.preparer',
            'preparation.verifier',
        ]);

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'No hay preparación para mostrar.');
        }

        // FIX: Validar que la preparación esté verificada para mostrar summary
        if (!in_array($preparation->status, ['verified', 'complete'])) {
            return redirect()->route('surgeries.preparations.compare', $surgery)
                ->with('error', 'La preparación aún no ha sido verificada.');
        }

        $summary = $this->preparationService->getPreparationSummary($preparation->id);

        // Productos escaneados (solo si hay paquete)
        $scannedProducts = collect();
        if ($preparation->pre_assembled_package_id) {
            $scannedProducts = DB::table('package_contents')
                ->join('products', 'package_contents.product_id', '=', 'products.id')
                ->join('product_units', 'package_contents.product_unit_id', '=', 'product_units.id')
                ->where('package_contents.pre_assembled_package_id', $preparation->pre_assembled_package_id)
                ->whereNotNull('package_contents.added_at')
                ->select(
                    'products.name',
                    'products.code',
                    'product_units.epc',
                    'package_contents.added_at',
                    'package_contents.added_by'
                )
                ->orderBy('package_contents.added_at', 'desc')
                ->get();
        }

        return view('surgeries.preparations.summary', compact(
            'surgery',
            'preparation',
            'summary',
            'scannedProducts'
        ));
    }

    /**
     * Obtener estado actualizado (AJAX)
     */
    public function status(ScheduledSurgery $surgery)
    {
        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json(['error' => 'No hay preparación activa'], 404);
        }

        try {
            $summary = $this->preparationService->getPreparationSummary($preparation->id);
            return response()->json(['success' => true, 'data' => $summary]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Listar items de preparación (AJAX)
     */
    public function items(ScheduledSurgery $surgery)
    {
        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json(['error' => 'No hay preparación activa'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $preparation->items()->with(['product', 'storageLocation'])->get()
        ]);
    }

    /**
     * Escanear barcode manual
     */
    public function scanBarcode(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        Log::info("[MODO MANUAL] Escaneando barcode: {$validated['barcode']} para Cirugía ID: {$surgery->id}");

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json([
                'success' => false,
                'message' => 'No hay preparación activa para esta cirugía.'
            ], 404);
        }

        try {
            // 1. Buscar el producto por código
            $product = \App\Models\Product::where('code', $validated['barcode'])->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ Producto no encontrado: {$validated['barcode']}"
                ], 404);
            }

            // 2. Verificar si este producto es requerido en la preparación
            $preparationItem = $preparation->items()
                ->where('product_id', $product->id)
                ->where('quantity_missing', '>', 0)
                ->first();

            if (!$preparationItem) {
                return response()->json([
                    'success' => false,
                    'message' => "⚠️ Este producto no es requerido o ya está completo."
                ], 400);
            }

            // 3. Obtener la siguiente unidad disponible (FEFO/FIFO)
            $unit = \App\Models\ProductUnit::nextAvailable($product->id)->first();

            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ No hay unidades disponibles de {$product->name} en el inventario."
                ], 404);
            }

            // 4. Llamar al servicio de picking
            $result = $this->preparationService->pickProduct(
                $preparation->id,
                $unit->epc,
                auth()->id()
            );

            // 5. Obtener el item actualizado para la respuesta
            $item = $preparation->items()->find($result['item_id']);

            return response()->json([
                'success' => true,
                'message' => "✅ {$product->name} agregado (Unidad FEFO)",
                'mode' => 'manual',
                'data' => [
                    'item_id' => $result['item_id'],
                    'product_name' => $result['product_name'],
                    'unit_info' => [
                        'epc' => $unit->epc,
                        'batch' => $unit->batch_number,
                        'expiration' => $unit->expiration_date?->format('Y-m-d'),
                        'location' => $unit->currentLocation?->code,
                    ],
                    'quantity_picked' => $item->quantity_picked,
                    'quantity_missing' => $result['quantity_missing'],
                    'item_status' => $result['item_status'],
                    'preparation_complete' => $result['preparation_complete'],
                    'conditional_actions' => $result['conditional_actions'] ?? [],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error en scanBarcode: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Buscar por EPC (RFID)
     */
    public function searchByEPC(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'epc' => 'required|string|max:255',
        ]);

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json(['success' => false, 'message' => 'No hay preparación activa.'], 404);
        }

        try {
            $unit = \App\Models\ProductUnit::findByEPC($validated['epc']);

            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ Tag RFID no registrado o unidad no disponible"
                ], 404);
            }

            $unit->load(['product', 'currentLocation']);

            $preparationItem = $preparation->items()
                ->where('product_id', $unit->product_id)
                ->where('quantity_missing', '>', 0)
                ->first();

            if (!$preparationItem) {
                return response()->json([
                    'success' => false,
                    'message' => "⚠️ Este producto no está en la lista de pendientes o ya fue completado."
                ], 400);
            }

            $confirmationData = $unit->getConfirmationData();
            $confirmationData['preparation_item_id'] = $preparationItem->id;
            $confirmationData['quantity_missing'] = $preparationItem->quantity_missing;

            return response()->json(['success' => true, 'data' => $confirmationData]);

        } catch (\Exception $e) {
            Log::error("Error al buscar EPC: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => "Error: " . $e->getMessage()], 500);
        }
    }

    /**
     * Confirmar RFID
     */
    public function confirmRFID(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'epc' => 'required|string|max:255',
        ]);

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json(['success' => false, 'message' => 'No hay preparación activa.'], 404);
        }

        try {
            $unit = \App\Models\ProductUnit::findByEPC($validated['epc']);

            if (!$unit) {
                return response()->json(['success' => false, 'message' => " Unidad no disponible"], 404);
            }

            $result = $this->preparationService->pickProduct(
                $preparation->id,
                $unit->epc,
                auth()->id()
            );

            $item = $preparation->items()->find($result['item_id']);

            return response()->json([
                'success' => true,
                'message' => "✅ {$result['product_name']} agregado",
                'mode' => 'rfid',
                'data' => [
                    'item_id' => $result['item_id'],
                    'product_name' => $result['product_name'],
                    'unit_info' => [
                        'epc' => $unit->epc,
                        'batch' => $unit->batch_number,
                        'expiration' => $unit->expiration_date?->format('Y-m-d'),
                    ],
                    'quantity_picked' => $item->quantity_picked,
                    'quantity_missing' => $result['quantity_missing'],
                    'item_status' => $result['item_status'],
                    'preparation_complete' => $result['preparation_complete'],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error al confirmar RFID: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => "Error: " . $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // HELPERS PRIVADOS
    // ═══════════════════════════════════════════════════════════

    /**
     * Obtener productos excluidos o reemplazados por condicionales.
     */
    private function getExcludedByConditionals(ScheduledSurgery $surgery): array
    {
        if (!$surgery->checklist_id) {
            return [];
        }

        $baseItems = ChecklistItem::where('checklist_id', $surgery->checklist_id)
            ->with(['product', 'conditionals.targetProduct', 'conditionals.doctor', 'conditionals.hospital', 'conditionals.modality'])
            ->ordered()
            ->get();

        $excluded = [];

        foreach ($baseItems as $item) {
            $adjustedData = $item->getAdjustedQuantity($surgery);

            if ($adjustedData['final_quantity'] === 0 && $adjustedData['has_conditional']) {
                $conditional = $adjustedData['conditional'];
                $excluded[] = [
                    'product_name' => $item->product->name,
                    'product_code' => $item->product->code,
                    'base_quantity' => $adjustedData['base_quantity'],
                    'action_type' => $conditional?->action_type,
                    'description' => $adjustedData['conditional_description'],
                    'target_product' => $conditional?->targetProduct?->name,
                    'criteria' => $conditional?->getDescription() ?? '',
                    'is_mandatory' => $item->is_mandatory,
                ];
            }
        }

        return $excluded;
    }
}