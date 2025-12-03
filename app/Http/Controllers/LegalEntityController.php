<?php

namespace App\Http\Controllers;

use App\Models\LegalEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LegalEntityController extends Controller
{
    /**
     * Display a listing of legal entities.
     */
    public function index()
    {
        $legalEntities = LegalEntity::withCount(['productUnits', 'purchaseOrders'])
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function ($entity) {
                $entity->total_inventory_value = $entity->getTotalInventoryValue();
                $entity->total_units = $entity->getTotalUnitsCount();
                return $entity;
            });

        return view('legal-entities.index', compact('legalEntities'));
    }

    /**
     * Show the form for creating a new legal entity.
     */
    public function create()
    {
        return view('legal-entities.create');
    }

    /**
     * Store a newly created legal entity in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'razon_social' => 'required|string|max:255',
            'rfc' => 'required|string|unique:legal_entities,rfc',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        LegalEntity::create($validated);

        return redirect()->route('legal-entities.index')
            ->with('success', 'Razón social creada exitosamente.');
    }

    /**
     * Display the specified legal entity.
     */
    public function show(LegalEntity $legalEntity)
    {
        // Cargar relaciones necesarias
        $legalEntity->load([
            'subWarehouses', // ✅ AGREGAR ESTA LÍNEA
            'productUnits' => function($query) {
                $query->with(['product', 'storageLocation'])
                    ->latest()
                    ->limit(15);
            },
            'purchaseOrders' => function($query) {
                $query->with('supplier')
                    ->latest()
                    ->limit(10);
            },
            'inventoryMovements' => function($query) {
                $query->with('product')
                    ->latest()
                    ->limit(20);
            }
        ]);

        // Calcular estadísticas
        $totalInventoryValue = $legalEntity->getTotalInventoryValue();
        $totalUnits = $legalEntity->getTotalUnitsCount();

        return view('legal-entities.show', compact('legalEntity', 'totalInventoryValue', 'totalUnits'));
    }


    /**
     * Show the form for editing the specified legal entity.
     */
    public function edit(LegalEntity $legalEntity)
    {
        return view('legal-entities.edit', compact('legalEntity'));
    }

    /**
     * Update the specified legal entity in storage.
     */
    public function update(Request $request, LegalEntity $legalEntity)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'razon_social' => 'required|string|max:255',
            'rfc' => 'required|string|size:13|unique:legal_entities,rfc,' . $legalEntity->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $legalEntity->update($validated);

        return redirect()->route('legal-entities.index')
            ->with('success', 'Razón social actualizada exitosamente.');
    }

    /**
     * Remove the specified legal entity from storage.
     */
    public function destroy(LegalEntity $legalEntity)
    {
        try {
            // Verificar que no tenga órdenes de compra o inventario asociado
            if ($legalEntity->purchaseOrders()->count() > 0 || $legalEntity->productUnits()->count() > 0) {
                return redirect()->route('legal-entities.index')
                    ->with('error', 'No se puede eliminar la razón social porque tiene órdenes de compra o inventario asociado.');
            }

            $legalEntity->delete();

            return redirect()->route('legal-entities.index')
                ->with('success', 'Razón social eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('legal-entities.index')
                ->with('error', 'Error al eliminar la razón social.');
        }
    }

    /**
     * Toggle the active status of a legal entity.
     */
    public function toggleStatus(LegalEntity $legalEntity)
    {
        $legalEntity->update([
            'is_active' => !$legalEntity->is_active
        ]);

        $status = $legalEntity->is_active ? 'activada' : 'desactivada';

        return redirect()->route('legal-entities.index')
            ->with('success', "Razón social {$status} exitosamente.");
    }
}