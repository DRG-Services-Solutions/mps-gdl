<?php

namespace App\Http\Controllers;

use App\Models\StorageLocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use app\Models\User;

class StorageLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = StorageLocation::latest()->paginate(10);
        return view('storage_locations.index', compact('locations'));    
    }

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
        //Validacion de los datos que vienen del formulario
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:200',
            'code' => 'nullable|string|max:200',
        ]);       
  
        StorageLocation::create($validatedData);

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
        return view('storage_locations.edit', compact('storageLocation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StorageLocation $storageLocation)
    {
        
        $request->validate([
            'name' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:200',
            'code' => 'nullable|string|max:200',
        ]);

        $safeData->$request->safe();
        $storageLocation->update($safeData->all());

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
