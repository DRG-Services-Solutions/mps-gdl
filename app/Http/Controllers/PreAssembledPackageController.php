<?php

namespace App\Http\Controllers;

use App\Models\PreAssembledPackage;
use App\Models\SurgicalChecklist;
use App\Models\StorageLocation;
use App\Models\Product;
use App\Models\PackageContent;
use App\Models\ProductUnit;
use Illuminate\Http\Request;

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
            ->orderBy('name')
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
            ->orderBy('name')
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
    // FORZAR recarga fresca de contents
    $preAssembled->load([
        'surgeryChecklist',
        'storageLocation',
        'contents.product',
        'contents.productUnit',
        'preparations',
        'scheduledSurgeries'
    ]);

    // Optimizado: solo columnas necesarias
    $availableProducts = Product::select('id', 'code', 'name')
        ->where('status', 'active')
        ->orderBy('name')
        ->get();

    return view('pre-assembled.show', [
        'package' => $preAssembled,
        'preAssembled' => $preAssembled,
        'availableProducts' => $availableProducts
    ]);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PreAssembledPackage $preAssembled)
    {
        $checklists = SurgicalChecklist::where('status', 'active')
            ->orderBy('name')
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
    // LOG 1: Inicio
    \Log::info('===== INICIO addProduct =====', [
        'package_id' => $preAssembled->id,
        'package_name' => $preAssembled->name,
        'request_data' => $request->all(),
    ]);

    $validated = $request->validate([
        'product_unit_epc' => 'nullable|string',
        'product_id' => 'nullable|exists:products,id',
    ]);

    // LOG 2: Después de validación
    \Log::info('Validación OK', [
        'validated' => $validated,
        'tiene_epc' => !empty($validated['product_unit_epc']),
        'tiene_product_id' => !empty($validated['product_id']),
    ]);

    if (empty($validated['product_unit_epc']) && empty($validated['product_id'])) {
        \Log::warning('Error: Ambos campos vacíos');
        return back()->with('error', 'Debes escanear un EPC o seleccionar un producto.');
    }

    $productId = null;
    $productUnitId = null;

    // CASO 1: Usuario escaneó EPC
    if (!empty($validated['product_unit_epc'])) {
        \Log::info('RAMA: Usuario escaneó EPC', [
            'epc' => $validated['product_unit_epc']
        ]);

        $productUnit = ProductUnit::where('epc', $validated['product_unit_epc'])->first();
        
        // LOG 3: Resultado de búsqueda
        \Log::info('Búsqueda ProductUnit', [
            'epc_buscado' => $validated['product_unit_epc'],
            'encontrado' => $productUnit ? 'SÍ' : 'NO',
            'product_unit_id' => $productUnit->id ?? null,
            'status' => $productUnit->status ?? null,
        ]);

        if (!$productUnit) {
            \Log::error('ProductUnit NO encontrado con EPC', [
                'epc' => $validated['product_unit_epc']
            ]);
            return back()->with('error', 'No se encontró ningún producto con ese EPC.');
        }

        if (!$productUnit->isAvailable()) {
            \Log::warning('ProductUnit NO disponible', [
                'product_unit_id' => $productUnit->id,
                'status' => $productUnit->status,
                'status_label' => $productUnit->status_label,
            ]);
            return back()->with('error', 'Este producto no está disponible (Estado: ' . $productUnit->status_label . ')');
        }

        $productId = $productUnit->product_id;
        $productUnitId = $productUnit->id;

        // LOG 4: Antes de cambiar estado
        \Log::info('Cambiando estado ProductUnit a reserved', [
            'product_unit_id' => $productUnit->id,
            'status_anterior' => $productUnit->status,
        ]);

        $productUnit->update(['status' => 'reserved']);

        // LOG 5: Después de cambiar estado
        \Log::info('Estado cambiado', [
            'product_unit_id' => $productUnit->id,
            'status_nuevo' => $productUnit->fresh()->status,
        ]);
    }
    // CASO 2: Usuario seleccionó del dropdown
    else {
        \Log::info('RAMA: Usuario seleccionó del dropdown', [
            'product_id' => $validated['product_id']
        ]);

        $productId = $validated['product_id'];
        $productUnitId = null;
    }

    // LOG 6: Antes de crear PackageContent
    \Log::info('Creando PackageContent', [
        'pre_assembled_package_id' => $preAssembled->id,
        'product_id' => $productId,
        'product_unit_id' => $productUnitId,
        'quantity' => 1,
    ]);

    try {
        $packageContent = PackageContent::create([
            'pre_assembled_package_id' => $preAssembled->id,
            'product_id' => $productId,
            'product_unit_id' => $productUnitId,
            'quantity' => 1,
            'added_at' => now(),
        ]);

        // LOG 7: Después de crear
        \Log::info('PackageContent CREADO exitosamente', [
            'package_content_id' => $packageContent->id,
            'total_items_en_paquete' => PackageContent::where('pre_assembled_package_id', $preAssembled->id)->count(),
        ]);

    } catch (\Exception $e) {
        // LOG 8: Error al crear
        \Log::error('ERROR al crear PackageContent', [
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
            'datos_intentados' => [
                'pre_assembled_package_id' => $preAssembled->id,
                'product_id' => $productId,
                'product_unit_id' => $productUnitId,
            ]
        ]);

        return back()->with('error', 'Error al agregar producto: ' . $e->getMessage());
    }

    \Log::info('===== FIN addProduct - ÉXITO =====');

    return back()->with('success', 'Producto agregado al paquete correctamente.');
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