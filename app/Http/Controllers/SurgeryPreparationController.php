<?php

namespace App\Http\Controllers;

use App\Models\ScheduledSurgery;
use App\Models\SurgeryPreparation;
use App\Models\PreAssembledPackage;
use App\Models\PackageContent;
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
     * Iniciar flujo de preparación (redirige a selección de paquete)
     */
    public function start(ScheduledSurgery $surgery)
    {
        Log::info("Iniciando flujo de preparación para Cirugía ID: {$surgery->id}");

        if ($surgery->status !== 'scheduled') {
            Log::warning("Intento de inicio fallido: Estado actual es {$surgery->status}");
            return redirect()->back()->with('error', 'Esta cirugía ya está en proceso o completada.');
        }

        // Validar que tenga checklist asignado
        if (!$surgery->checklist_id) {
            return redirect()->back()->with('error', 'Esta cirugía no tiene un checklist asignado.');
        }

        Log::info("Redirigiendo a selección de paquete...");
        return redirect()->route('surgeries.preparations.selectPackage', $surgery);
    }

    /**
     * Mostrar vista de selección de paquete
     */
    public function selectPackage(ScheduledSurgery $surgery)
    {
        Log::info("Accediendo a vista de selección de paquete para Cirugía ID: {$surgery->id}");
        
        $surgery->load('checklist');

        try {
            // Obtener paquetes disponibles con su nivel de completitud
            $availablePackages = PreAssembledPackage::available()
                ->with(['contents.product', 'storageLocation'])
                ->get()
                ->map(function($package) use ($surgery) {
                    $package->completeness = $package->getCompletenessPercentage($surgery->checklist_id);
                    $package->has_expired = $package->hasExpiredProducts();
                    return $package;
                })
                ->sortByDesc('completeness');

            Log::info("Paquetes disponibles encontrados: " . $availablePackages->count());

            return view('surgeries.preparations.select-package', compact('surgery', 'availablePackages'));

        } catch (\Exception $e) {
            Log::error("Error en selectPackage: " . $e->getMessage());
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'Error al cargar paquetes disponibles.');
        }
    }

    /**
     * Asignar paquete y crear preparación
     */
    public function assignPackage(Request $request, ScheduledSurgery $surgery)
    {
        Log::info("Asignando paquete ID: {$request->package_id} a Cirugía: {$surgery->id}");

        $request->validate([
            'package_id' => 'required|exists:pre_assembled_packages,id'
        ]);

        try {
            // Llamar al servicio para crear la preparación
            $preparation = $this->preparationService->createPreparation(
                $surgery->id, 
                $request->package_id, 
                auth()->id()
            );

            Log::info("Preparación creada exitosamente. ID: {$preparation->id}");

            return redirect()->route('surgeries.preparations.compare', $surgery)
                ->with('success', 'Paquete asignado. Revisa las diferencias entre checklist y paquete.');

        } catch (\Exception $e) {
            Log::error("Error al asignar paquete: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al asignar paquete: ' . $e->getMessage());
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
            'preparation.items.storageLocation',
        ]);

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'No hay preparación activa para esta cirugía.');
        }

        // Obtener resumen de la preparación
        $summary = $this->preparationService->getPreparationSummary($preparation->id);

        // Agrupar items por estado
        $itemsComplete = $preparation->items->where('status', 'in_package');
        $itemsPending = $preparation->items->where('status', '!=', 'in_package');

        // ✅ Usar el scope que corrige el agrupamiento
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

        return view('surgeries.preparations.compare', compact(
            'surgery',
            'preparation',
            'summary',
            'itemsComplete',
            'itemsPending',
            'packageContents'
        ));
    }

    // ============================================
    // FASE 3: SURTIDO (PICKING) CON RFID
    // ============================================

    /**
     * Vista de surtido de productos faltantes
     */
    public function picking(ScheduledSurgery $surgery)
    {
        Log::info("Accediendo a picking para Cirugía ID: {$surgery->id}");

        $surgery->load([
            'preparation.items.product',
            'preparation.items.storageLocation'
        ]);
        
        $preparation = $surgery->preparation;

        if (!$preparation) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'No se encontró una preparación activa para esta cirugía.');
        }

        // Actualizar estado a picking si aún no lo está
        if (in_array($preparation->status, ['scheduled', 'comparing'])) {
            $preparation->update(['status' => 'picking']);
        }

        // 🐛 DEBUG: Agregar esto temporalmente
        $allItems = $preparation->items;
        
        Log::info("=== DEBUG PREPARACIÓN ===");
        Log::info("Total items: " . $allItems->count());
        Log::info("Items por estado:");
        Log::info("- in_package: " . $allItems->where('status', 'in_package')->count());
        Log::info("- complete: " . $allItems->where('status', 'complete')->count());
        Log::info("- pending: " . $allItems->where('status', 'pending')->count());
        Log::info("Items con quantity_missing > 0: " . $allItems->where('quantity_missing', '>', 0)->count());
        
        // Detalles de cada item
        foreach ($allItems as $item) {
            Log::info("Item {$item->id}: {$item->product->name} - Status: {$item->status}, Required: {$item->quantity_required}, InPackage: {$item->quantity_in_package}, Picked: {$item->quantity_picked}, Missing: {$item->quantity_missing}");
        }

        // Obtener resumen usando el servicio
        $summary = $this->preparationService->getPreparationSummary($preparation->id);
        
        Log::info("Resumen del servicio:");
        Log::info(json_encode($summary, JSON_PRETTY_PRINT));

        $pendingItems = $preparation->items->where('quantity_missing', '>', 0)->values();

        return view('surgeries.preparations.picking', compact(
            'surgery',
            'preparation',
            'pendingItems',
            'summary'
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
            // ✅ Usar el servicio para registrar el producto
            $result = $this->preparationService->pickProduct(
                $preparation->id,
                $validated['epc'],
                auth()->id()
            );

            Log::info("Producto escaneado exitosamente: {$result['product_name']}");
            
            // ✅ AGREGAR: Obtener el item actualizado para tener quantity_picked
            $item = $preparation->items()->find($result['item_id']);

            return response()->json([
                'success' => true,
                'message' => "✓ Producto agregado: {$result['product_name']}",
                'data' => [
                    'item_id' => $result['item_id'],
                    'quantity_missing' => $result['quantity_missing'],
                    'quantity_picked' => $item->quantity_picked, // ✅ AÑADIDO
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
     */
    public function verify(ScheduledSurgery $surgery)
    {
        Log::info("Verificando preparación para Cirugía ID: {$surgery->id}");

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'No hay preparación activa.');
        }

        // Verificar que todos los items obligatorios estén completos
        $summary = $this->preparationService->getPreparationSummary($preparation->id);

        if ($summary['mandatory_pending'] > 0) {
            return back()->with('error', 
                "La preparación aún tiene {$summary['mandatory_pending']} items obligatorios pendientes."
            );
        }

        try {
            DB::beginTransaction();

            // Finalizar preparación usando el servicio
            $this->preparationService->finishPreparation(
                $preparation->id,
                auth()->id(),
                'Verificación completada exitosamente'
            );

            // ✅ Actualizar estado del paquete a "en cirugía"
            $preparation->preAssembledPackage->update(['status' => 'in_surgery']);

            // ✅ Actualizar product units de 'reserved' a 'in_use'
            DB::table('product_units')
                ->where('current_package_id', $preparation->pre_assembled_package_id)
                ->where('current_status', 'reserved') // ✅ Cambiado
                ->update([
                    'current_status' => 'in_use', // ✅ Cambiado de 'in_surgery' a 'in_use'
                    'current_surgery_id' => $surgery->id,
                    'updated_at' => now(),
                ]);

            DB::commit();

            Log::info("Preparación verificada y completada. ID: {$preparation->id}");

            return redirect()->route('surgeries.show', $surgery)
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
            return back()->with('error', 'No hay preparación activa para cancelar.');
        }

        try {
            // Usar el servicio para cancelar
            $this->preparationService->cancelPreparation(
                $preparation->id,
                auth()->id(),
                $validated['reason']
            );

            Log::info("Preparación cancelada. ID: {$preparation->id}");

            return redirect()->route('surgeries.show', $surgery)
                ->with('success', 'Preparación cancelada correctamente.');

        } catch (\Exception $e) {
            Log::error("Error al cancelar preparación: " . $e->getMessage());
            
            return back()->with('error', 'Error al cancelar: ' . $e->getMessage());
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
            'preparation.scheduledSurgery.patient'
        ]);

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'No hay preparación para mostrar.');
        }

        // Obtener resumen completo
        $summary = $this->preparationService->getPreparationSummary($preparation->id);

        // Agrupar productos escaneados
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

        return view('surgeries.preparations.summary', compact(
            'surgery',
            'preparation',
            'summary',
            'scannedProducts'
        ));
    }

    /**
     * Obtener estado actualizado de la preparación (para AJAX)
     */
    public function status(ScheduledSurgery $surgery)
    {
        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json(['error' => 'No hay preparación activa'], 404);
        }

        try {
            $summary = $this->preparationService->getPreparationSummary($preparation->id);

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar items de preparación (para AJAX)
     */
    public function items(ScheduledSurgery $surgery)
    {
        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json(['error' => 'No hay preparación activa'], 404);
        }

        $items = $preparation->items()
            ->with(['product', 'storageLocation'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function scanBarcode(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        Log::info("📦 [MODO MANUAL] Escaneando barcode: {$validated['barcode']} para Cirugía ID: {$surgery->id}");

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json([
                'success' => false,
                'message' => 'No hay preparación activa para esta cirugía.'
            ], 404);
        }

        try {
            // 1. Buscar producto por código (parsea códigos compuestos automáticamente)
            $product = \App\Models\Product::findByCode($validated['barcode']);

            if (!$product) {
                Log::warning("❌ Producto no encontrado con código: {$validated['barcode']}");
                return response()->json([
                    'success' => false,
                    'message' => "❌ Producto no encontrado: {$validated['barcode']}"
                ], 404);
            }

            Log::info("✅ Producto encontrado: {$product->name} (ID: {$product->id})");

            // 2. Verificar que el producto esté en la lista de pendientes
            $preparationItem = $preparation->items()
                ->where('product_id', $product->id)
                ->where('quantity_missing', '>', 0)
                ->first();

            if (!$preparationItem) {
                return response()->json([
                    'success' => false,
                    'message' => "⚠️ Este producto no está en la lista de pendientes o ya fue completado."
                ], 400);
            }

            // 3. Buscar siguiente unidad disponible con FEFO/FIFO
            $unit = $product->getNextAvailableUnit();

            if (!$unit) {
                // No hay unidades disponibles, mostrar información de otras unidades
                $otherUnits = $product->units()
                    ->whereIn('status', ['in_use', 'reserved', 'in_sterilization'])
                    ->with('currentLocation')
                    ->get();

                $statusInfo = $otherUnits->groupBy('status')->map(function($units, $status) {
                    return [
                        'count' => $units->count(),
                        'status_label' => $units->first()->status_label ?? $status
                    ];
                });

                Log::warning("❌ No hay unidades disponibles. Estados: " . json_encode($statusInfo));

                return response()->json([
                    'success' => false,
                    'message' => "❌ No hay unidades disponibles de {$product->name}",
                    'other_units' => $statusInfo
                ], 404);
            }

            Log::info("🎯 Unidad seleccionada: {$unit->unique_identifier} (FEFO/FIFO)");

            // 4. Reservar unidad inmediatamente
            $unit->reserve(
                auth()->id(),
                $surgery->id,
                $preparation->pre_assembled_package_id
            );

            Log::info("🔒 Unidad reservada exitosamente");

            // 5. Registrar en el picking usando el servicio
            $result = $this->preparationService->pickProduct(
                $preparation->id,
                $unit->epc,
                auth()->id()
            );

            Log::info("✅ Producto agregado al picking exitosamente");

            // 6. Obtener el item actualizado
            $item = $preparation->items()->find($result['item_id']);

            return response()->json([
                'success' => true,
                'message' => "✅ {$product->name} agregado automáticamente",
                'mode' => 'manual',
                'data' => [
                    'item_id' => $result['item_id'],
                    'product_name' => $result['product_name'],
                    'unit_info' => [
                        'epc' => $unit->epc,
                        'batch' => $unit->batch_number,
                        'expiration' => $unit->expiration_date?->format('Y-m-d'),
                        'days_until_expiration' => $unit->days_until_expiration,
                        'location' => $unit->currentLocation?->code,
                    ],
                    'quantity_picked' => $item->quantity_picked,
                    'quantity_missing' => $result['quantity_missing'],
                    'item_status' => $result['item_status'],
                    'preparation_complete' => $result['preparation_complete'],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error al escanear barcode: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            ], 500);
        }
    }


    public function searchByEPC(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'epc' => 'required|string|max:255',
        ]);

        Log::info("📡 [MODO RFID] Buscando EPC: {$validated['epc']} para Cirugía ID: {$surgery->id}");

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json([
                'success' => false,
                'message' => 'No hay preparación activa.'
            ], 404);
        }

        try {
            // 1. Buscar unidad por EPC
            $unit = \App\Models\ProductUnit::findByEPC($validated['epc']);

            if (!$unit) {
                Log::warning("❌ EPC no encontrado o unidad no disponible: {$validated['epc']}");
                return response()->json([
                    'success' => false,
                    'message' => "❌ Tag RFID no registrado o unidad no disponible"
                ], 404);
            }

            // 2. Cargar relaciones necesarias
            $unit->load(['product', 'currentLocation']);

            Log::info("✅ Unidad encontrada: {$unit->product->name} (EPC: {$unit->epc})");

            // 3. Verificar que el producto esté en la lista de pendientes
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

            // 4. Retornar datos para confirmación
            $confirmationData = $unit->getConfirmationData();
            $confirmationData['preparation_item_id'] = $preparationItem->id;
            $confirmationData['quantity_missing'] = $preparationItem->quantity_missing;

            Log::info("📋 Datos de confirmación preparados para modal");

            return response()->json([
                'success' => true,
                'data' => $confirmationData
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error al buscar EPC: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => "Error al buscar tag: " . $e->getMessage()
            ], 500);
        }
    }


    public function confirmRFID(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'epc' => 'required|string|max:255',
        ]);

        Log::info("✅ [MODO RFID] Confirmando EPC: {$validated['epc']} para Cirugía ID: {$surgery->id}");

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return response()->json([
                'success' => false,
                'message' => 'No hay preparación activa.'
            ], 404);
        }

        try {
            // 1. Buscar unidad nuevamente (doble verificación)
            $unit = \App\Models\ProductUnit::findByEPC($validated['epc']);

            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ Unidad no disponible"
                ], 404);
            }

            // 2. Reservar unidad
            $unit->reserve(
                auth()->id(),
                $surgery->id,
                $preparation->pre_assembled_package_id
            );

            Log::info("🔒 Unidad reservada: {$unit->unique_identifier}");

            // 3. Registrar en el picking usando el servicio
            $result = $this->preparationService->pickProduct(
                $preparation->id,
                $unit->epc,
                auth()->id()
            );

            Log::info("✅ Producto agregado al picking (RFID confirmado)");

            // 4. Obtener el item actualizado
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
            Log::error("❌ Error al confirmar RFID: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            ], 500);
        }
    }





}