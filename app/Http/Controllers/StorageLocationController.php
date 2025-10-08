<?php

namespace App\Http\Controllers;

use App\Models\StorageLocation;
use Illuminate\Http\Request;

class StorageLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = StorageLocation::latest()->paginate(10);
        return view('storage_locations.index', compact('locations'));    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('storage_locations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:storage_locations,code|max:50',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'required|in:warehouse,reception,quarantine,shipping',
            'is_active' => 'boolean',
        ]);

        StorageLocation::create($validated);

        return redirect()->route('storage_locations.index')
            ->with('success', 'Ubicación de almacenamiento creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StorageLocation $storageLocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StorageLocation $storageLocation)
    {
        return view('storage_locations.edit', compact('storage_location'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StorageLocation $storageLocation)
    {
        $validated = $request->validate([
            'code' => 'required|max:50|unique:storage_locations,code,' . $storage_location->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'required|in:warehouse,reception,quarantine,shipping',
            'is_active' => 'boolean',
        ]);

        $storage_location->update($validated);

        return redirect()->route('storage_locations.index')
            ->with('success', 'Ubicación de almacenamiento actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StorageLocation $storageLocation)
    {
        $storage_location->delete();

        return redirect()->route('storage_locations.index')
            ->with('success', 'Ubicación eliminada correctamente.');
    }
}
