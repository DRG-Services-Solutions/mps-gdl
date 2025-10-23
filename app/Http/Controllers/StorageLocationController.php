<?php

namespace App\Http\Controllers;

use App\Models\StorageLocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            
            'area' => 'required|string|max:50',
            'organizer' => 'required|string|max:10',
            'shelf_level' => 'required|integer|min:1',
            'shelf_section' => 'required|integer|min:1',
            'description' => 'nullable|string',
            
           
            'area' => [
                'required',
                'string',
                'max:50',
                Rule::unique('storage_locations')->where(function ($query) use ($request) {
                    return $query
                        ->where('organizer', $request->organizer)
                        ->where('shelf_level', $request->shelf_level)
                        ->where('shelf_section', $request->shelf_section);
                }),
            ],
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
        return view('storage_locations.edit', compact('storageLocation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StorageLocation $storageLocation)
    {
        
        $validated = $request->validate([
            'area' => 'required|string|max:50',
            'organizer' => 'required|string|max:10',
            'shelf_level' => 'required|integer|min:1',
            'shelf_section' => 'required|integer|min:1',
            'description' => 'nullable|string',

           
            'area' => [
                'required',
                'string',
                'max:50',
                Rule::unique('storage_locations')->where(function ($query) use ($request) {
                    return $query
                        ->where('organizer', $request->organizer)
                        ->where('shelf_level', $request->shelf_level)
                        ->where('shelf_section', $request->shelf_section);
                })->ignore($storageLocation->id), 
            ],
        ]);

        $storageLocation->update($validated);

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
