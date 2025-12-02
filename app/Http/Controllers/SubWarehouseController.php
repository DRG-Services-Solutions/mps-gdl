<?php

namespace App\Http\Controllers;

use App\Models\SubWarehouse;
use App\Models\LegalEntity;
use Illuminate\Http\Request;

class SubWarehouseController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'legal_entity_id' => 'required|exists:legal_entities,id',
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:sub_warehouses,name,NULL,id,legal_entity_id,' . $request->legal_entity_id
            ],
            'description' => 'nullable|string',
        ], [
            'name.unique' => 'Ya existe un sub-almacén con este nombre para esta razón social.',
        ]);

        SubWarehouse::create($validated);

        return redirect()->back()
            ->with('success', 'Sub-almacén creado exitosamente.');
    }

    public function update(Request $request, SubWarehouse $subWarehouse)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:sub_warehouses,name,' . $subWarehouse->id . ',id,legal_entity_id,' . $subWarehouse->legal_entity_id
            ],
            'description' => 'nullable|string',
        ], [
            'name.unique' => 'Ya existe un sub-almacén con este nombre para esta razón social.',
        ]);

        $subWarehouse->update($validated);

        return redirect()->back()
            ->with('success', 'Sub-almacén actualizado exitosamente.');
    }

    public function toggleStatus(SubWarehouse $subWarehouse)
    {
        $subWarehouse->update([
            'is_active' => !$subWarehouse->is_active
        ]);

        $status = $subWarehouse->is_active ? 'activado' : 'desactivado';

        return redirect()->back()
            ->with('success', "Sub-almacén {$status} exitosamente.");
    }

    public function destroy(SubWarehouse $subWarehouse)
    {
        if ($subWarehouse->productUnits()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar el sub-almacén porque tiene productos asignados.');
        }

        $subWarehouse->delete();

        return redirect()->back()
            ->with('success', 'Sub-almacén eliminado exitosamente.');
    }
}