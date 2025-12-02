<?php

namespace App\Http\Controllers;

use App\Models\SubWarehouse;
use Illuminate\Http\Request;
use App\Models\LegalEntity;

class SubWarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request([
            'legal_entity_id' => 'required|exists:legal_entities,id',
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:sub_warehouses,name,NULL,id,legal_entity_id,' . $request->legal_entity_id
            ],
            'description' => 'nullable|string',
            'name.unique' => 'Ya existe un sub-almacen con este nombre',                
        ]);
            SubWarehouse::create($validated);

            return redirect()->back()->with('success', 'Sub-almacen creado exitosamente');

    }

    /**
     * Display the specified resource.
     */
    public function show(SubWarehouse $subWarehouse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubWarehouse $subWarehouse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubWarehouse $subWarehouse)
    {
        //
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
}
