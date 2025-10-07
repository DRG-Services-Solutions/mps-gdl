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
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // ==========================================================
    // INDEX 
    // ==========================================================
    public function index(): View
    {
        $products = Product::with([
            'manufacturer', 
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
        $manufacturers = Manufacturer::orderBy('name')->get(); 
        $categories = Category::orderBy('name')->get(); 
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get(); 
        
        return view('products.create', compact('manufacturers', 'categories', 'specialties', 'subcategories'));
    }

    // ==========================================================
    // STORE 
    // ==========================================================
    public function store(Request $request): RedirectResponse
    {
    $validated = $request->validate([
        // Relaciones
        'manufacturer_id' => 'nullable|exists:manufacturers,id',
        'category_id' => 'nullable|exists:product_categories,id',
        'specialty_id' => 'nullable|exists:medical_specialties,id',
        'subcategory_id' => 'nullable|exists:subcategories,id',
        
        // Información del catálogo
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:255|unique:products,code',
        'model' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'specifications' => 'nullable|string',
        
        // Tipo de trazabilidad (define QUÉ tipo usará, no identificadores específicos)
        'tracking_type' => 'required|in:stock,rfid,serial,none',
        
        // Características del producto
        'requires_sterilization' => 'nullable|boolean',
        'is_consumable' => 'nullable|boolean',
        'is_single_use' => 'nullable|boolean',
        
        // Información de inventario general
        'unit_cost' => 'nullable|numeric|min:0',
        'minimum_stock' => 'nullable|integer|min:0',
        
        // Estado del producto en catálogo
        'status' => 'nullable|in:active,inactive,discontinued',
    ]);
    
    // Validación de negocio: coherencia entre características
    if ($request->boolean('requires_sterilization') && $validated['tracking_type'] !== 'serial') {
        return back()->withErrors([
            'tracking_type' => 'Los instrumentales que requieren esterilización deben usar tracking por número de serie'
        ])->withInput();
    }
    
    if ($request->boolean('is_single_use') && $validated['tracking_type'] === 'serial') {
        return back()->withErrors([
            'tracking_type' => 'Los productos de un solo uso no deberían usar número de serie'
        ])->withInput();
    }
    
    // Manejo de checkboxes
    $validated['requires_sterilization'] = $request->boolean('requires_sterilization');
    $validated['is_consumable'] = $request->boolean('is_consumable');
    $validated['is_single_use'] = $request->boolean('is_single_use');
    
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
            'manufacturer', 
            'category', 
            'subcategory', 
            'medicalSpecialty',
            'units', 
            'stocks'
        ]);
        
        return view('products.show', compact('product'));
    }

    // ==========================================================
    // EDIT 
    // ==========================================================
    public function edit(Product $product): View
    {
        $manufacturers = Manufacturer::orderBy('name')->get();
        $categories = Category::orderBy('name')->get(); // CORREGIDO
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get();

        return view('products.edit', compact('product', 'manufacturers', 'categories', 'specialties', 'subcategories'));
    }

    // ==========================================================
    // UPDATE 
    // ==========================================================
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            // Relaciones
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'category_id' => 'nullable|exists:product_categories,id', // CORREGIDO: era categories
            'specialty_id' => 'nullable|exists:medical_specialties,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            
            // Identidad
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'code')->ignore($product->id)
            ],
            'model' => 'nullable|string|max:255',
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
            'requires_sterilization' => 'nullable|boolean',
            'is_consumable' => 'nullable|boolean',
            'is_single_use' => 'nullable|boolean',
            
            // Stock y Costos
            'unit_cost' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'current_stock' => 'nullable|integer|min:0',
            'storage_location' => 'nullable|string|max:255',
            
            // Lote y Caducidad
            'expiration_date' => 'nullable|date',
            'lot_number' => 'nullable|string|max:255',
            'specifications' => 'nullable|array', 
            
            // Estado y Tracking
            'status' => 'required|in:active,inactive,maintenance,discontinued',
            'tracking_type' => 'required|in:stock,rfid,both,none',
        ]);

        // Manejo de Checkboxes
        $validated['rfid_enabled'] = $request->boolean('rfid_enabled');
        $validated['is_consumable'] = $request->boolean('is_consumable');
        $validated['requires_sterilization'] = $request->boolean('requires_sterilization');
        $validated['is_single_use'] = $request->boolean('is_single_use');
        
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
        $products = Product::with(['manufacturer', 'category'])
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
        $products = Product::with(['manufacturer', 'category'])
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

        $product = Product::with(['manufacturer', 'category', 'medicalSpecialty'])
            ->where('rfid_tag_id', $request->rfid_tag_id)
            ->where('rfid_enabled', true)
            ->first();

        return view('products.rfid-result', compact('product'));
    }
}