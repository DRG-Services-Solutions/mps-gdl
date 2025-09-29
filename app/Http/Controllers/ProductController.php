<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Manufacturer; // AGREGADO: Necesario para el dropdown de Fabricantes
use App\Models\Category;     // CORREGIDO: Usaremos el modelo Category para simplificar
use App\Models\MedicalSpecialty;
use App\Models\Subcategory;  // CORREGIDO: Usaremos el modelo Subcategory para simplificar
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
        // MEJORA 1: Cargamos todas las relaciones necesarias para evitar el problema N+1
        // (manufacturer, category, subcategory, medicalSpecialty)
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
        // MEJORA 2: Usamos los nombres de modelos simplificados
        $manufacturers = Manufacturer::orderBy('name')->get(); // AGREGADO
        $categories = Category::orderBy('name')->get();      // CORREGIDO: Usar Category
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $subcategories = Subcategory::all();                 // CORREGIDO: Usar Subcategory

        return view('products.create', compact('manufacturers', 'categories', 'specialties', 'subcategories'));
    }

    // ==========================================================
    // STORE (Guardado)
    // ==========================================================
    public function store(Request $request): RedirectResponse
    {
        // ELIMINADO: dd($request->all());
        
        $validated = $request->validate([
            // CORRECCIÓN 3: Sincronizar nombres de campos con la migración y la vista Blade
            'manufacturer_id' => 'nullable|exists:manufacturers,id', // AGREGADO
            'category_id' => 'nullable|exists:categories,id', // CORREGIDO
            'specialty_id' => 'nullable|exists:medical_specialties,id', // CORREGIDO
            'subcategory_id' => 'nullable|exists:subcategories,id', // CORREGIDO
            
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,code',
            
            // CORREGIDO: Eliminamos el campo 'manufacturer' (string) redundante
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
            'status' => 'required|in:active,inactive,maintenance,retired',
        ]);

        // Manejar checkbox: si no vienen, se deben marcar como false.
        // NOTA: Cuando se usa $request->validate(), si el campo no es 'required',
        // Laravel no lo incluye en $validated si no está presente. 
        // Usaremos $request->input() para garantizar que los booleanos se manejen correctamente.
        $validated['rfid_enabled'] = $request->has('rfid_enabled');
        $validated['is_consumable'] = $request->has('is_consumable');
        $validated['requires_sterilization'] = $request->has('requires_sterilization');
        $validated['is_single_use'] = $request->has('is_single_use');
        
        // Manejar rfid_tag_id vacío (si no se proporciona, Laravel lo guarda como NULL)
        // Ya no es estrictamente necesario, pero lo mantenemos por claridad:
        // $validated['rfid_tag_id'] = $request->input('rfid_tag_id');
        
        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
    }

    // ==========================================================
    // EDIT (Formulario)
    // ==========================================================
    public function edit(Product $product): View
    {
        // MEJORA 2 (Aplicada): Usamos los nombres de modelos simplificados
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
            'code' => 'required|string|max:255|unique:products,code,' . $product->id, // Ignora el ID actual
            // Eliminamos 'manufacturer' (string)
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            
            'rfid_enabled' => 'nullable|boolean',
            'rfid_tag_id' => 'nullable|string|unique:products,rfid_tag_id,' . $product->id, // Ignora el ID actual
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