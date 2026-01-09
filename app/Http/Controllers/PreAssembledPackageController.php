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
}