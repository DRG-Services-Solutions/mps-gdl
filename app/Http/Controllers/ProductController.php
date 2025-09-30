<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Manufacturer; 
use App\Models\Category;     
use App\Models\MedicalSpecialty;
use App\Models\Subcategory;  
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    // ==========================================================
    // INDEX (Lectura/Listado)
    // ==========================================================
    public function index(): View
    {

        $products = Product::with([
            'manufacturer', 
            'category', 
            'subcategory', 
            'medicalSpecialty'
        ])->latest()->paginate(10);
        
        return view('products.index', compact('products'));
    }

    // ==========================================================
    // CREATE (Formulario)
    // ==========================================================
    public function create(): View
    {
        $manufacturers = Manufacturer::orderBy('name')->get(); 
        $categories = Category::orderBy('name')->get();      
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $subcategories = Subcategory::all();                 
        return view('products.create', compact('manufacturers', 'categories', 'specialties', 'subcategories'));
    }

    // ==========================================================
    // STORE (Guardado)
    // ==========================================================
    public function store(Request $request): RedirectResponse
    {
        
        $validated = $request->validate([
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'specialty_id' => 'nullable|exists:medical_specialties,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products',
            
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            
            'rfid_enabled' => 'nullable|boolean',
            'rfid_tag_id' => 'nullable|string|unique:products,rfid_tag_id',
            'requires_sterilization' => 'nullable|boolean',
            'is_consumable' => 'nullable|boolean',
            'is_single_use' => 'nullable|boolean',
            
            'unit_cost' => 'nullable|numeric',
            'minimum_stock' => 'required|integer',
            'current_stock' => 'required|integer',
            'storage_location' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date',
            'lot_number' => 'nullable|string|max:255',
            'specifications' => 'nullable|string',
        ]);
        
        $validated['rfid_enabled'] = $request->has('rfid_enabled');
        $validated['is_consumable'] = $request->has('is_consumable');
        $validated['requires_sterilization'] = $request->has('requires_sterilization');
        $validated['is_single_use'] = $request->has('is_single_use');

        //dd($validated);
        Product::create($validated);
        return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
    }

    // ==========================================================
    // EDIT (Formulario)
    // ==========================================================
    public function edit(Product $product): View
    {
        $manufacturers = Manufacturer::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $subcategories = Subcategory::all();

        return view('products.edit', compact('product', 'manufacturers', 'categories', 'specialties', 'subcategories'));
    }

    // ==========================================================
    // UPDATE (Actualización)
    // ==========================================================
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            // CORRECCIÓN 3 (Aplicada): Sincronizar nombres de campos
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'category_id' => 'nullable|exists:categories,id',
            'specialty_id' => 'nullable|exists:medical_specialties,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,code,' . $product->id, 
            // Eliminamos 'manufacturer' (string)
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            
            'rfid_enabled' => 'nullable|boolean',
            'rfid_tag_id' => 'nullable|string|unique:products,rfid_tag_id,' . $product->id,
            'requires_sterilization' => 'nullable|boolean',
            'is_consumable' => 'nullable|boolean',
            'is_single_use' => 'nullable|boolean',
            
            'unit_cost' => 'nullable|numeric',
            'minimum_stock' => 'required|integer',
            'current_stock' => 'required|integer',
            'storage_location' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date',
            'lot_number' => 'nullable|string|max:255',
            'specifications' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance,retired',
        ]);

        // Manejo de Checkboxes
        $validated['rfid_enabled'] = $request->has('rfid_enabled');
        $validated['is_consumable'] = $request->has('is_consumable');
        $validated['requires_sterilization'] = $request->has('requires_sterilization');
        $validated['is_single_use'] = $request->has('is_single_use');
        
        $product->update($validated);
        return redirect()->route('products.index')->with('success', 'Producto actualizado correctamente.');
    }

    // ==========================================================
    // DESTROY (Eliminación)
    // ==========================================================
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Producto eliminado correctamente.');
    }
}