<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier; 
use App\Models\Category;
use App\Models\MedicalSpecialty;
use App\Models\Subcategory;  
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // ==========================================================
    // INDEX 
    // ==========================================================
    public function index(): View
    {
        $products = Product::with([
            'supplier', 
            'category',
            'subcategory', 
            'medicalSpecialty',

        ])->latest()->paginate(10);
        
        return view('products.index', compact('products'));
    }

    // ==========================================================
    // CREATE
    // ==========================================================
    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get(); 
        $categories = Category::all ();
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $subcategories = Subcategory::all(); 
        
        return view('products.create', compact('suppliers', 'categories', 'specialties', 'subcategories'));
    }

    // ==========================================================
    // STORE 
    // ==========================================================
    public function store(Request $request): RedirectResponse
    {
    $validated = $request->validate([
        // Relaciones
        'supplier_id' => 'nullable|exists:suppliers,id', 
        'category_id' => 'nullable|exists:product_categories,id',
        'specialty_id' => 'nullable|exists:medical_specialties,id',
        'subcategory_id' => 'nullable|exists:subcategories,id',
        
        // Información del catálogo
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:255|unique:products,code',
        'description' => 'nullable|string',
        'requires_sterilization' => 'nullable|boolean',
        'requires_refrigeration' => 'nullable|boolean',
       
         // Tipo de trazabilidad (define QUÉ tipo usará, no identificadores específicos)
        'tracking_type' => 'required|in:code,rfid,serial',
        
        // Información de inventario general
        'minimum_stock' => 'nullable|integer|min:0',
        
        // Estado del producto en catálogo
        'status' => 'nullable|in:active,inactive,',
    ]);
    
    // Valores por defecto
    $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;
    $validated['status'] = $validated['status'] ?? 'active';
    
    // Crear producto en catálogo (sin EPCs ni seriales individuales)
    $product = Product::create($validated);
    
    return redirect()->route('products.index')
        ->with('success', 'Producto agregado al catálogo correctamente. Ahora puede registrar entradas de inventario.');
}

    // ==========================================================
    // SHOW
    // ==========================================================
    public function show(Product $product): View
    {
        $product->load([
            'supplier', 
            'category', 
            'subcategory', 
            'medicalSpecialty',
            'stocks'
        ]);
        
        return view('products.show', compact('product'));
    }

    // ==========================================================
    // EDIT 
    // ==========================================================
    public function edit(Product $product): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        $categories = Category::orderBy('name')->get(); 
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get();

        return view('products.edit', compact('product', 'suppliers', 'categories', 'specialties', 'subcategories'));
    }

    // ==========================================================
    // UPDATE 
    // ==========================================================
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            // Relaciones
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category_id' => 'nullable|exists:product_categories,id', 
            'specialty_id' => 'nullable|exists:medical_specialties,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            
            // Identidad
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:255', Rule::unique('products', 'code')->ignore($product->id)],
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'serial_number')->ignore($product->id)
            ],
            'description' => 'nullable|string',
            
            // RFID y Características
            'rfid_enabled' => 'nullable|boolean',
            'rfid_tag_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'rfid_tag_id')->ignore($product->id)
            ],
           
           
            
            // Stock y Costos
            'minimum_stock' => 'nullable|integer|min:0',
            'current_stock' => 'nullable|integer|min:0',
            'storage_location' => 'nullable|string|max:255',
            
            // Lote y Caducidad
            'expiration_date' => 'nullable|date',
            'lot_number' => 'nullable|string|max:255',
            
            
            // Estado y Tracking
            'status' => 'required|in:active,inactive,maintenance,discontinued',
            'tracking_type' => 'required|in:code,rfid,serial',
        ]);

        // Manejo de Checkboxes
        $validated['rfid_enabled'] = $request->boolean('rfid_enabled');
        $validated['requires_sterilization'] = $request->boolean('requires_sterilization');
        $validated['requires_refrigeration'] = $request->boolean('requires_refrigeration');
       
        
        $product->update($validated);
        
        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    // ==========================================================
    // DESTROY (Eliminación)
    // ==========================================================
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete(); // SoftDelete
        
        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    // ==========================================================
    // RESTORE (Restaurar) - AGREGADO
    // ==========================================================
    public function restore($id): RedirectResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        
        return redirect()->route('products.index')
            ->with('success', 'Producto restaurado correctamente.');
    }

    // ==========================================================
    // MÉTODOS ADICIONALES ÚTILES
    // ==========================================================
    
    /**
     * Productos con stock bajo
     */
    public function lowStock(): View
    {
        $products = Product::with(['supplier', 'category'])
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('status', 'active')
            ->orderBy('current_stock', 'asc')
            ->paginate(10);
        
        return view('products.low-stock', compact('products'));
    }

    /**
     * Productos próximos a vencer
     */
    public function expiringSoon(): View
    {
        $products = Product::with(['supplier', 'category'])
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<=', now()->addDays(30))
            ->where('status', 'active')
            ->orderBy('expiration_date', 'asc')
            ->paginate(10);
        
        return view('products.expiring-soon', compact('products'));
    }

    /**
     * Actualizar stock de un producto
     */
    public function updateStock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'operation' => 'required|in:add,subtract,set'
        ]);

        $currentStock = $product->current_stock;

        switch ($validated['operation']) {
            case 'add':
                $newStock = $currentStock + $validated['quantity'];
                break;
            case 'subtract':
                $newStock = max(0, $currentStock - $validated['quantity']);
                break;
            case 'set':
                $newStock = max(0, $validated['quantity']);
                break;
        }

        $product->update(['current_stock' => $newStock]);

        return redirect()->back()
            ->with('success', 'Stock actualizado correctamente.');
    }

    /**
     * Buscar producto por RFID
     */
    public function searchByRfid(Request $request): View
    {
        $request->validate([
            'rfid_tag_id' => 'required|string'
        ]);

        $product = Product::with(['supplier', 'category', 'medicalSpecialty'])
            ->where('rfid_tag_id', $request->rfid_tag_id)
            ->where('rfid_enabled', true)
            ->first();

        return view('products.rfid-result', compact('product'));
    }
}