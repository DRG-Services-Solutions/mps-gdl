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
}