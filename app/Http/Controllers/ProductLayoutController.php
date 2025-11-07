<?php

namespace App\Http\Controllers;

use App\Models\ProductLayout;
use App\Models\StorageLocation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductLayoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productLayouts = ProductLayout::with('storageLocation')
                                        ->latest()
                                        ->paginate(20);
        return view('product_layouts.index', compact('productLayouts'));                                
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $storageLocations = StorageLocation::orderBy('name')->get();

        return view('product_layouts.create', compact('storageLocations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            //Claves foraneas de la tabla, estas deben existir en sus propias tablas
            'storage_location_id' => 'required|exists:storage_locations,id',
            //estante
            'shelf' => 'required|integer|min:1',

            //nivel
            'level' => 'required|string|max:2',

            //posicion
            'position' => 'required|numeric',
        ]);

        ProductLayout::create($validated);

        return redirect()->route('product_layouts.index')->with('success', 'Layout del producto creado con Exito');

       
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductLayout $productLayout)
    {
        //load() carga la relacion 'storageLocation' definida en el modelo
        $productLayout->load('storageLocation');

        return view('product_layouts.show', compact('productLayout'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductLayout $productLayout)
    {
        $storageLocations = StorageLocation::orderBy('name')->get();
        $productLayout->load('storafeLocation');

        return view('product_lauouts.edit', compact('storageLocations', 'productLayout'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductLayout $productLayout)
    {
        $validated = $request->validate([
            //Claves foraneas que deben existir en sus respectivas tablas
            'storage_location_id' => 'required|exists:storage_locations,id',
            'product_id' => 'required|exists:products,id',
            
            //Campos actuales de ubicacion
            'shelf' => 'required|integer|min:1',
            'level' => 'required|string|max:2',
            'position' => 'required|numeric',

        ]);

        $productLayout->update($validated);
        return redirect()->route('product_layouts.index')
                         ->with('success', 'Layout Actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductLayout $productLayout)
    {
        $productLayout->delete();
    }
}
