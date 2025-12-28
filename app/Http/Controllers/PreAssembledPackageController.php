<?php
// app/Http/Controllers/PreAssembledPackageController.php

namespace App\Http\Controllers;

use App\Models\PreAssembledPackage;
use App\Models\SurgicalChecklist;
use App\Models\StorageLocation;
use Illuminate\Http\Request;

class PreAssembledPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Query base
        $query = PreAssembledPackage::with([
            'surgeryChecklist',
            'storageLocation',
            'contents.productUnit.product'
        ]);

        // Filtros
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

        // Obtener paquetes paginados
        $packages = $query->latest()->paginate(15);

        // Calcular estadísticas
        $availableCount = PreAssembledPackage::where('status', 'available')->count();
        $inSurgeryCount = PreAssembledPackage::where('status', 'in_surgery')->count();
        $maintenanceCount = PreAssembledPackage::where('status', 'maintenance')->count();

        // Check lists para filtro
        $checklists = SurgicalChecklist::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('pre-assembled.index', compact(
            'packages',
            'availableCount',
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
        $preAssembled->load([
            'surgeryChecklist',
            'storageLocation',
            'contents.productUnit.product',
            'contents.product',
            'preparations',
            'scheduledSurgeries'
        ]);

        // Productos disponibles para agregar
        $availableProducts = \App\Models\Product::where('status', 'active')
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
        // Verificar que no esté en uso
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
        $validated = $request->validate([
            'product_unit_epc' => 'nullable|string',
            'product_id' => 'nullable|exists:products,id',
        ]);

        // Lógica para agregar producto
        // TODO: Implementar lógica de agregar producto por EPC o ID

        return back()->with('success', 'Producto agregado al paquete.');
    }

    /**
     * Remover producto del paquete
     */
    public function removeProduct(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Lógica para remover producto
        // TODO: Implementar lógica de remover producto

        return back()->with('success', 'Producto removido del paquete.');
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