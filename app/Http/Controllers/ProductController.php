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
            'medicalSpecialty'
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
        'manufacturer_id' => 'nullable|exists:manufacturers,id',
        'category_id' => 'nullable|exists:product_categories,id',
        'specialty_id' => 'nullable|exists:medical_specialties,id',
        'subcategory_id' => 'nullable|exists:subcategories,id',
        
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:255|unique:products,code',
        'model' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        
        // Tipo de trazabilidad: RFID o Número de Serie
        'tracking_type' => 'required|in:stock,rfid,both,none',
        'rfid_tag_id' => 'nullable|string|size:24|unique:products,rfid_tag_id',
        'serial_number' => 'nullable|string|max:255|unique:products,serial_number',
        'generate_rfid' => 'nullable|boolean',
        
        'requires_sterilization' => 'nullable|boolean',
        'is_consumable' => 'nullable|boolean',
        'is_single_use' => 'nullable|boolean',
        
        'unit_cost' => 'nullable|numeric|min:0',
        'minimum_stock' => 'nullable|integer|min:0',
        'current_stock' => 'nullable|integer|min:0',
        'storage_location' => 'nullable|string|max:255',
        
        'expiration_date' => 'nullable|date|after_or_equal:today',
        'lot_number' => 'nullable|string|max:255',
        'specifications' => 'nullable|string',
        
        'status' => 'nullable|in:active,inactive,maintenance,discontinued',
    ]);
    
    // Validaciones de negocio
    $requiresSterilization = $request->boolean('requires_sterilization');
    $trackingType = $validated['tracking_type'];
    
    // Si requiere esterilización, DEBE tener número de serie (instrumental reutilizable)
    if ($requiresSterilization && empty($validated['serial_number'])) {
        return back()->withErrors([
            'serial_number' => 'Los instrumentales que requieren esterilización deben tener número de serie de fábrica'
        ])->withInput();
    }
    
    // Si usa tracking RFID, debe tener EPC (consumible/desechable)
    if (in_array($trackingType, ['rfid', 'both'])) {
        if (empty($validated['rfid_tag_id']) && !$request->boolean('generate_rfid')) {
            return back()->withErrors([
                'rfid_tag_id' => 'Debe proporcionar un EPC o marcar "Generar automáticamente"'
            ])->withInput();
        }
    }
    
    // Los productos con RFID NO deben tener serial (son mutuamente excluyentes)
    if (!empty($validated['rfid_tag_id']) && !empty($validated['serial_number'])) {
        return back()->withErrors([
            'tracking_type' => 'Un producto solo puede tener RFID O número de serie, no ambos'
        ])->withInput();
    }
    
    // Manejo de checkboxes
    $validated['rfid_enabled'] = in_array($trackingType, ['rfid', 'both']) && !$requiresSterilization;
    $validated['is_consumable'] = $request->boolean('is_consumable');
    $validated['requires_sterilization'] = $requiresSterilization;
    $validated['is_single_use'] = $request->boolean('is_single_use');
    
    // Valores por defecto
    $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;
    $validated['current_stock'] = $validated['current_stock'] ?? 0;
    $validated['status'] = $validated['status'] ?? 'active';

    // Crear producto
    $productData = array_filter($validated, function($key) {
        return $key !== 'generate_rfid';
    }, ARRAY_FILTER_USE_KEY);
    
    $product = Product::create($productData);
    
    // Generar RFID solo si se solicitó y no tiene serial
    if ($request->boolean('generate_rfid') && 
        empty($validated['rfid_tag_id']) && 
        empty($product->serial_number)) {
        
        $epc = EPCGenerator::generate($product->id, $product->category_id ?? 0);
        $product->update([
            'rfid_tag_id' => $epc,
            'rfid_enabled' => true
        ]);
    }

    return redirect()->route('products.index')
        ->with('success', 'Producto creado correctamente.');
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