<?php

namespace App\Http\Controllers;

use App\Models\PreAssembledPackage;
use App\Models\SurgicalChecklist;
use App\Models\StorageLocation;
use App\Models\Product;
use App\Models\PackageContent;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use App\Helpers\ProductSearchHelper;
use App\Models\ChecklistItem;
class PreAssembledPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PreAssembledPackage::with([
            'surgeryChecklist',
            'storageLocation',
            'contents.productUnit.product'
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('package_epc', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('checklist_id')) {
            $query->where('surgery_checklist_id', $request->checklist_id);
        }

        $packages = $query->latest()->paginate(15);

        // Estadísticas - TODOS los contadores
        $availableCount = PreAssembledPackage::where('status', 'available')->count();
        $inPreparationCount = PreAssembledPackage::where('status', 'in_preparation')->count();
        $inSurgeryCount = PreAssembledPackage::where('status', 'in_surgery')->count();
        $maintenanceCount = PreAssembledPackage::where('status', 'maintenance')->count();

        $checklists = SurgicalChecklist::where('status', 'active')
            ->orderBy('surgery_type')
            ->get();

        return view('pre-assembled.index', compact(
            'packages',
            'availableCount',
            'inPreparationCount',
            'inSurgeryCount',
            'maintenanceCount',
            'checklists'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $checklists = SurgicalChecklist::where('status', 'active')
            ->orderBy('surgery_type')
            ->get();

        $storageLocations = StorageLocation::orderBy('code')->get();

        return view('pre-assembled.create', compact('checklists', 'storageLocations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:pre_assembled_packages,code',
            'name' => 'required|string|max:255',
            'surgery_checklist_id' => 'required|exists:surgical_checklists,id',
            'package_epc' => 'nullable|string|max:255|unique:pre_assembled_packages,package_epc',
            'storage_location_id' => 'required|exists:storage_locations,id',
            'status' => 'required|in:available,in_preparation,in_surgery,maintenance',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        $package = PreAssembledPackage::create($validated);

        return redirect()
            ->route('pre-assembled.show', $package)
            ->with('success', 'Paquete pre-armado creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PreAssembledPackage $preAssembled)
    {
        $preAssembled->load([
            'surgeryChecklist.items.product',
            'surgeryChecklist',
            'storageLocation',
            'contents.product',
            'contents.productUnit',
        ]);

        $availableProducts = Product::select('id', 'code', 'name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('pre-assembled.show', compact('preAssembled', 'availableProducts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PreAssembledPackage $preAssembled)
    {
        $checklists = SurgicalChecklist::where('status', 'active')
            ->orderBy('code')
            ->get();

        $storageLocations = StorageLocation::orderBy('code')->get();

        return view('pre-assembled.edit', compact('preAssembled', 'checklists', 'storageLocations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:pre_assembled_packages,code,' . $preAssembled->id,
            'name' => 'required|string|max:255',
            'surgery_checklist_id' => 'required|exists:surgical_checklists,id',
            'package_epc' => 'nullable|string|max:255|unique:pre_assembled_packages,package_epc,' . $preAssembled->id,
            'storage_location_id' => 'required|exists:storage_locations,id',
            'status' => 'required|in:available,in_preparation,in_surgery,maintenance',
            'notes' => 'nullable|string',
        ]);

        $preAssembled->update($validated);

        return redirect()
            ->route('pre-assembled.show', $preAssembled)
            ->with('success', 'Paquete pre-armado actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PreAssembledPackage $preAssembled)
    {
        if ($preAssembled->scheduledSurgeries()->exists()) {
            return back()->with('error', 'No se puede eliminar un paquete que está asignado a cirugías.');
        }

        $preAssembled->delete();

        return redirect()
            ->route('pre-assembled.index')
            ->with('success', 'Paquete pre-armado eliminado exitosamente.');
    }

    /**
     * Agregar producto al paquete
     */
 public function addProduct(Request $request, PreAssembledPackage $preAssembled)
{
    \Log::info('[PACKAGE] ===== INICIO addProduct =====', [
        'package_id' => $preAssembled->id,
        'package_name' => $preAssembled->name,
    ]);

    // Validar input
    $validated = $request->validate([
        'search_input' => 'required|string|max:255',
    ]);

    $input = trim($validated['search_input']);

    \Log::info('[PACKAGE] Input recibido', [
        'input' => $input,
        'length' => strlen($input),
    ]);

    // PASO 1: Identificar tipo de búsqueda
    $type = ProductSearchHelper::identifySearchType($input);

    // PASO 2: Buscar el ProductUnit
    $productUnit = ProductSearchHelper::searchProductUnit($input, $type);

    // PASO 3: Validar que se encontró
    if (!$productUnit) {
        \Log::warning('[PACKAGE]  No se encontró producto', [
            'input' => $input,
            'type' => $type,
        ]);

        return back()->with('error', 'No se encontró ningún producto disponible con: ' . $input);
    }

    // PASO 4: Validar disponibilidad
    if (!$productUnit->isAvailable()) {
        \Log::warning('[PACKAGE]  ProductUnit no disponible', [
            'product_unit_id' => $productUnit->id,
            'status' => $productUnit->status,
        ]);

        return back()->with('error', 'Este producto no está disponible (Estado: ' . $productUnit->status_label . ')');
    }

    // PASO 5: Validar que no esté ya en el paquete
    $alreadyInPackage = PackageContent::where('pre_assembled_package_id', $preAssembled->id)
        ->where('product_unit_id', $productUnit->id)
        ->exists();

    if ($alreadyInPackage) {
        \Log::warning('[PACKAGE] ⚠️ ProductUnit ya está en el paquete', [
            'product_unit_id' => $productUnit->id,
        ]);

        return back()->with('error', 'Este producto ya está en el paquete.');
    }

    // PASO 6: Crear el PackageContent
    \Log::info('[PACKAGE] ✅ Creando PackageContent', [
        'package_id' => $preAssembled->id,
        'product_id' => $productUnit->product_id,
        'product_unit_id' => $productUnit->id,
    ]);

    try {
        PackageContent::create([
            'pre_assembled_package_id' => $preAssembled->id,
            'product_id' => $productUnit->product_id,
            'product_unit_id' => $productUnit->id,
            'quantity' => 1,
            'added_at' => now(),
        ]);

        // PASO 7: Cambiar estado del ProductUnit
        $productUnit->update(['status' => 'reserved']);

        \Log::info('[PACKAGE] ✅ Producto agregado exitosamente', [
            'product_unit_id' => $productUnit->id,
            'product_name' => $productUnit->product->name,
            'search_type' => $type,
        ]);

        \Log::info('[PACKAGE] ===== FIN addProduct - ÉXITO =====');

        return back()->with('success', 'Producto agregado al paquete correctamente.');

    } catch (\Exception $e) {
        \Log::error('[PACKAGE] ❌ Error al crear PackageContent', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return back()->with('error', 'Error al agregar producto: ' . $e->getMessage());
    }
}

    /**
     * Remover producto del paquete
     */
    public function removeProduct(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Obtener items antes de eliminar
        $items = PackageContent::where('pre_assembled_package_id', $preAssembled->id)
            ->where('product_id', $validated['product_id'])
            ->get();

        // CRÍTICO: Liberar ProductUnits si tienen EPC
        foreach ($items as $item) {
            if ($item->product_unit_id) {
                ProductUnit::where('id', $item->product_unit_id)
                    ->update(['status' => 'available']);
            }
        }

        // Eliminar items
        $deleted = $items->count();
        PackageContent::where('pre_assembled_package_id', $preAssembled->id)
            ->where('product_id', $validated['product_id'])
            ->delete();

        if ($deleted > 0) {
            return back()->with('success', "Se eliminaron {$deleted} unidad(es) del producto del paquete.");
        }

        return back()->with('error', 'No se encontró ese producto en el paquete.');
    }

    /**
     * Actualizar estado del paquete
     */
    public function updateStatus(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,in_preparation,in_surgery,maintenance',
        ]);

        $preAssembled->update(['status' => $validated['status']]);

        return back()->with('success', 'Estado actualizado exitosamente.');
    }

 public function rfidCompare(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'epcs' => 'required|array',
            'epcs.*' => 'required|string|max:255',
        ]);

        $preAssembled->load([
            'surgeryChecklist.items.product',
            'contents.product',
            'contents.productUnit',
        ]);

        $checklist = $preAssembled->surgeryChecklist;
        $epcsScanned = collect($validated['epcs'])->unique();

        // 1. Buscar ProductUnits por EPCs
        $scannedUnits = ProductUnit::with('product')
            ->whereIn('epc', $epcsScanned)
            ->get()
            ->keyBy('epc');

        $unknownEpcs = $epcsScanned->diff($scannedUnits->keys());

        // 2. Mapa de productos escaneados
        $scannedProductMap = [];
        foreach ($scannedUnits as $epc => $unit) {
            $pid = $unit->product_id;
            if (!isset($scannedProductMap[$pid])) {
                $scannedProductMap[$pid] = [
                    'product' => $unit->product,
                    'units' => collect(),
                    'count' => 0,
                ];
            }
            $scannedProductMap[$pid]['units']->push($unit);
            $scannedProductMap[$pid]['count']++;
        }

        // 3. Mapa de productos YA en el paquete
        $packageProductMap = [];
        foreach ($preAssembled->contents as $content) {
            $pid = $content->product_id;
            if (!isset($packageProductMap[$pid])) {
                $packageProductMap[$pid] = ['count' => 0];
            }
            $packageProductMap[$pid]['count'] += $content->quantity;
        }

        // 4. Comparar con Checklist
        $checklistComparison = [];
        $checklistProductIds = [];

        if ($checklist && $checklist->items) {
            foreach ($checklist->items as $item) {
                $pid = $item->product_id;
                $checklistProductIds[] = $pid;

                $requiredQty = $item->quantity;
                $inPackageQty = $packageProductMap[$pid]['count'] ?? 0;
                $scannedQty = $scannedProductMap[$pid]['count'] ?? 0;
                $totalAvailable = $inPackageQty + $scannedQty;

                $status = 'missing';
                if ($totalAvailable >= $requiredQty) {
                    $status = 'complete';
                } elseif ($totalAvailable > 0) {
                    $status = 'partial';
                }

                $checklistComparison[] = [
                    'checklist_item_id' => $item->id,
                    'product_id' => $pid,
                    'product_code' => $item->product->code,
                    'product_name' => $item->product->name,
                    'required_qty' => $requiredQty,
                    'in_package_qty' => $inPackageQty,
                    'scanned_qty' => $scannedQty,
                    'total_available' => $totalAvailable,
                    'missing_qty' => max(0, $requiredQty - $totalAvailable),
                    'status' => $status,
                    'is_mandatory' => $item->is_mandatory ?? true,
                    'scanned_epcs' => isset($scannedProductMap[$pid])
                        ? $scannedProductMap[$pid]['units']->pluck('epc')->values()
                        : [],
                ];
            }
        }

        // 5. Items EXTRA (escaneados pero NO en checklist)
        $extraItems = [];
        foreach ($scannedProductMap as $pid => $data) {
            if (!in_array($pid, $checklistProductIds)) {
                $extraItems[] = [
                    'product_id' => $pid,
                    'product_code' => $data['product']->code,
                    'product_name' => $data['product']->name,
                    'scanned_qty' => $data['count'],
                    'scanned_epcs' => $data['units']->pluck('epc')->values(),
                    'already_in_package' => isset($packageProductMap[$pid]),
                ];
            }
        }

        // 6. Estadísticas
        $totalItems = count($checklistComparison);
        $completeItems = collect($checklistComparison)->where('status', 'complete')->count();
        $partialItems = collect($checklistComparison)->where('status', 'partial')->count();
        $missingItems = collect($checklistComparison)->where('status', 'missing')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'package' => [
                    'id' => $preAssembled->id,
                    'name' => $preAssembled->name,
                ],
                'checklist' => [
                    'id' => $checklist?->id,
                    'name' => $checklist?->surgery_type,
                ],
                'comparison' => $checklistComparison,
                'extra_items' => $extraItems,
                'unknown_epcs' => $unknownEpcs->values(),
                'stats' => [
                    'total_checklist_items' => $totalItems,
                    'complete' => $completeItems,
                    'partial' => $partialItems,
                    'missing' => $missingItems,
                    'extra' => count($extraItems),
                    'unknown' => $unknownEpcs->count(),
                    'completeness_percentage' => $totalItems > 0
                        ? round(($completeItems / $totalItems) * 100, 1)
                        : 0,
                    'total_epcs_scanned' => $epcsScanned->count(),
                ],
            ],
        ]);
    }

    /**
     * Agregar producto vía RFID con validación de checklist
     */
    public function rfidAddProduct(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'epc' => 'required|string|max:255',
            'force' => 'boolean',
        ]);

        $epc = trim($validated['epc']);
        $force = $validated['force'] ?? false;

        // 1. Buscar ProductUnit
        $productUnit = ProductUnit::with('product')->where('epc', $epc)->first();

        if (!$productUnit) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró producto con EPC: ' . $epc,
                'code' => 'NOT_FOUND',
            ], 404);
        }

        // 2. Validar disponibilidad
        if (!$productUnit->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no disponible (Estado: ' . ($productUnit->status_label ?? $productUnit->status) . ')',
                'code' => 'NOT_AVAILABLE',
            ], 422);
        }

        // 3. Verificar duplicado
        $alreadyInPackage = PackageContent::where('pre_assembled_package_id', $preAssembled->id)
            ->where('product_unit_id', $productUnit->id)
            ->exists();

        if ($alreadyInPackage) {
            return response()->json([
                'success' => false,
                'message' => 'Este producto ya está en el paquete.',
                'code' => 'ALREADY_IN_PACKAGE',
                'data' => [
                    'product_name' => $productUnit->product->name,
                    'epc' => $epc,
                ],
            ], 422);
        }

        // 4. Verificar checklist
        $checklist = $preAssembled->surgeryChecklist;
        $isInChecklist = false;

        if ($checklist) {
            $isInChecklist = $checklist->items()
                ->where('product_id', $productUnit->product_id)
                ->exists();
        }

        // 5. Si NO en checklist y no forzado → pedir confirmación
        if (!$isInChecklist && !$force) {
            return response()->json([
                'success' => false,
                'message' => 'Este producto NO está en el checklist.',
                'code' => 'NOT_IN_CHECKLIST',
                'requires_confirmation' => true,
                'data' => [
                    'product_id' => $productUnit->product_id,
                    'product_code' => $productUnit->product->code,
                    'product_name' => $productUnit->product->name,
                    'epc' => $epc,
                ],
            ]);
        }

        // 6. Crear PackageContent
        try {
            PackageContent::create([
                'pre_assembled_package_id' => $preAssembled->id,
                'product_id' => $productUnit->product_id,
                'product_unit_id' => $productUnit->id,
                'quantity' => 1,
                'added_at' => now(),
            ]);

            $productUnit->update(['status' => 'reserved']);

            return response()->json([
                'success' => true,
                'message' => $isInChecklist
                    ? 'Producto del checklist agregado correctamente.'
                    : 'Producto EXTRA agregado al paquete.',
                'data' => [
                    'product_id' => $productUnit->product_id,
                    'product_code' => $productUnit->product->code,
                    'product_name' => $productUnit->product->name,
                    'epc' => $epc,
                    'is_in_checklist' => $isInChecklist,
                    'is_extra' => !$isInChecklist,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('[RFID-PACKAGE] Error', ['error' => $e->getMessage(), 'epc' => $epc]);

            return response()->json([
                'success' => false,
                'message' => 'Error al agregar producto: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Buscar info de un EPC individual
     */
    public function searchEpc(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'epc' => 'required|string|max:255',
        ]);

        $productUnit = ProductUnit::with('product')
            ->where('epc', trim($validated['epc']))
            ->first();

        if (!$productUnit) {
            return response()->json([
                'success' => false,
                'message' => 'EPC no encontrado.',
            ], 404);
        }

        $isInChecklist = false;
        if ($preAssembled->surgeryChecklist) {
            $isInChecklist = $preAssembled->surgeryChecklist->items()
                ->where('product_id', $productUnit->product_id)
                ->exists();
        }

        $isInPackage = PackageContent::where('pre_assembled_package_id', $preAssembled->id)
            ->where('product_unit_id', $productUnit->id)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'product_unit_id' => $productUnit->id,
                'product_id' => $productUnit->product_id,
                'product_code' => $productUnit->product->code,
                'product_name' => $productUnit->product->name,
                'epc' => $validated['epc'],
                'status' => $productUnit->status,
                'is_available' => $productUnit->isAvailable(),
                'is_in_checklist' => $isInChecklist,
                'is_in_package' => $isInPackage,
            ],
        ]);
    }
}