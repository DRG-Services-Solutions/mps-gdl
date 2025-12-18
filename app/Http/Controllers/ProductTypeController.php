<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productTypes = ProductType::with('products')->get();
        return view('product_types.index', compact('productTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product_types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:product_types,name|max:255',
            'description' => 'nullable|string',
        ]);
        ProductType::create($validated);
        return redirect()->route('product_types.index')->with('success', 'Tipo de Producto Creado Exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductType $productType)
    {
        return view('product_types.show', compact('productType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductType $productType)
    {
        return view('product_types.edit', compact('productType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductType $productType)
    {
        $validated = $request->validate([
        'name' => ['required',Rule::unique('product_types')->ignore($productType->id),'max:255'],  
        'description' => 'nullable|string',
        ]);

        $productType->update($validated);
        return redirect()->route('product_types.index')->with('success', 'Tipo de Producto Actualizado Exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductType $productType)
    {
        if ($productType->products()->count() > 0) {
            return redirect()->route('product_types.index')->with('error', 'No se puede eliminar el Tipo de Producto porque tiene productos asociados.');
        }
        
        $productType->delete();
        return redirect()->route('product_types.index')->with('success', 'Tipo de Producto Eliminado Exitosamente.');
    }
}
