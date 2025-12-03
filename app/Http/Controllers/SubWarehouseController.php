<?php

namespace App\Http\Controllers;

use App\Models\SubWarehouse;
use App\Models\LegalEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubWarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SubWarehouse::with('legalEntity');

        // Filtro por Legal Entity
        if ($request->filled('legal_entity_id')) {
            $query->where('legal_entity_id', $request->legal_entity_id);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Búsqueda por nombre
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $subWarehouses = $query->paginate(15)->withQueryString();
        $legalEntities = LegalEntity::active()->orderBy('name')->get();

        return view('sub-warehouses.index', compact('subWarehouses', 'legalEntities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $legalEntities = LegalEntity::active()->orderBy('name')->get();
        
        return view('sub-warehouses.create', compact('legalEntities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'legal_entity_id' => 'required|exists:legal_entities,id',
            'name' => [
                'required',
                'string',
                'max:255',
                // Validar que el nombre sea único dentro de la misma legal entity
                'unique:sub_warehouses,name,NULL,id,legal_entity_id,' . $request->legal_entity_id
            ],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ], [
            'name.unique' => 'Ya existe un sub-almacén con este nombre para la razón social seleccionada.',
        ]);

        try {
            $subWarehouse = SubWarehouse::create([
                'legal_entity_id' => $validated['legal_entity_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return redirect()
                ->route('sub-warehouses.show', $subWarehouse)
                ->with('success', 'Sub-almacén creado exitosamente.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el sub-almacén: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SubWarehouse $subWarehouse)
    {
        $subWarehouse->load([
            'legalEntity',
            'productUnits.product',
            'inventoryMovements.product',
            'purchaseOrders.supplier'
        ]);

        // Estadísticas
        $stats = [
            'total_units' => $subWarehouse->getTotalUnits(),
            'total_value' => $subWarehouse->getTotalValue(),
            'unique_products' => $subWarehouse->productUnits()->distinct('product_id')->count(),
            'purchase_orders_count' => $subWarehouse->purchaseOrders()->count(),
        ];

        // Productos agrupados
        $productsByStatus = $subWarehouse->productUnits()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('sub-warehouses.show', compact('subWarehouse', 'stats', 'productsByStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubWarehouse $subWarehouse)
    {
        $legalEntities = LegalEntity::active()->orderBy('name')->get();
        
        return view('sub-warehouses.edit', compact('subWarehouse', 'legalEntities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubWarehouse $subWarehouse)
    {
        $validated = $request->validate([
            'legal_entity_id' => 'required|exists:legal_entities,id',
            'name' => [
                'required',
                'string',
                'max:255',
                // Validar que el nombre sea único dentro de la misma legal entity (excepto el actual)
                'unique:sub_warehouses,name,' . $subWarehouse->id . ',id,legal_entity_id,' . $request->legal_entity_id
            ],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ], [
            'name.unique' => 'Ya existe un sub-almacén con este nombre para la razón social seleccionada.',
        ]);

        try {
            $subWarehouse->update([
                'legal_entity_id' => $validated['legal_entity_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? $subWarehouse->is_active,
            ]);

            return redirect()
                ->route('sub-warehouses.show', $subWarehouse)
                ->with('success', 'Sub-almacén actualizado exitosamente.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar el sub-almacén: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the specified resource.
     */
    public function toggleStatus(SubWarehouse $subWarehouse)
    {
        try {
            $subWarehouse->update([
                'is_active' => !$subWarehouse->is_active
            ]);

            $status = $subWarehouse->is_active ? 'activado' : 'desactivado';

            return redirect()
                ->back()
                ->with('success', "Sub-almacén {$status} exitosamente.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubWarehouse $subWarehouse)
    {
        // Verificar que no tenga productos asignados
        if ($subWarehouse->productUnits()->count() > 0) {
            return redirect()
                ->back()
                ->with('error', 'No se puede eliminar este sub-almacén porque tiene productos asignados.');
        }

        // Verificar que no tenga órdenes de compra
        if ($subWarehouse->purchaseOrders()->count() > 0) {
            return redirect()
                ->back()
                ->with('error', 'No se puede eliminar este sub-almacén porque tiene órdenes de compra asociadas.');
        }

        try {
            $name = $subWarehouse->name;
            $subWarehouse->delete();

            return redirect()
                ->route('sub-warehouses.index')
                ->with('success', "Sub-almacén '{$name}' eliminado exitosamente.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar el sub-almacén: ' . $e->getMessage());
        }
    }
}