<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subcategories = Subcategory::with('category')->withCount('products')->latest()->paginate(10); 
        return view('subcategories.index', compact('subcategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('subcategories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id', // Debe existir en la tabla categories
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Validación de unicidad compuesta: name debe ser único dentro de category_id
        if (Subcategory::where('category_id', $request->category_id)->where('name', $request->name)->exists()) {
            return back()->withInput()->withErrors(['name' => 'Ya existe una subcategoría con este nombre en la categoría seleccionada.']);
        }

        Subcategory::create($request->all());

        return redirect()->route('subcategories.index')
                         ->with('success', 'Subcategoría creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subcategory $subcategory)
    {
        return view('subcategories.show', compact('subcategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subcategory $subcategory)
    {
        $categories = Category::orderBy('name')->get();
        return view('subcategories.edit', compact('subcategory', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        // Validación de unicidad compuesta, ignorando el registro actual
        $uniqueRule = Subcategory::where('category_id', $request->category_id)
                                 ->where('name', $request->name)
                                 ->where('id', '!=', $subcategory->id)
                                 ->exists();

        if ($uniqueRule) {
            return back()->withInput()->withErrors(['name' => 'Ya existe una subcategoría con este nombre en la categoría seleccionada.']);
        }

        $subcategory->update($request->all());

        return redirect()->route('subcategories.index')
                         ->with('success', 'Subcategoría actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();
        return redirect()->route('subcategories.index')
                         ->with('success', 'Subcategoría eliminada exitosamente.');
    }
}
