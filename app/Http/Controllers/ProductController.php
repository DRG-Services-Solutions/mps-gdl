<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
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
   public function index(Request $request): View
{
    // Query base con relaciones
    $query = Product::with([
        'supplier', 
        'category',
        'subcategory', 
        'specialty', 
    ]);
    
    // ========================================
    // FILTRO: Búsqueda por nombre o código
    // ========================================
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
    
    // ========================================
    // FILTRO: Proveedor
    // ========================================
    if ($request->filled('supplier_id')) {
        $query->where('supplier_id', $request->supplier_id);
    }
    
    // ========================================
    // FILTRO: Categoría
    // ========================================
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }
    
    // ========================================
    // FILTRO: Tipo de Tracking
    // ========================================
    if ($request->filled('tracking_type')) {
        $query->where('tracking_type', $request->tracking_type);
    }
    
    // ========================================
    // FILTRO: Estado
    // ========================================
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    // Obtener productos paginados
    $products = $query->latest()->paginate(10)->withQueryString();
    
    // Obtener datos para los filtros (select options)
    $suppliers = \App\Models\Supplier::orderBy('name')->get();
    $categories = \App\Models\Category::orderBy('name')->get();
    
    return view('products.index', compact('products', 'suppliers', 'categories'));
}

    // ==========================================================
    // CREATE
    // ==========================================================
    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get(); 
        $categories = Category::orderBy('name')->get(); // Agregado orderBy
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
            
            // Relaciones (FKs)
            'supplier_id' => 'nullable|exists:suppliers,id', 
            'category_id' => 'nullable|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'specialty_id' => 'nullable|exists:medical_specialties,id',
            
            // Información básica del catálogo
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,code',
            'description' => 'nullable|string',
            
            // Características booleanas
            'requires_sterilization' => 'nullable|boolean',
            'requires_refrigeration' => 'nullable|boolean',
            'requires_temperature' => 'nullable|boolean',
           
            // Tipo de trazabilidad
            'tracking_type' => 'required|in:code,rfid,serial',
            
            // Información de inventario
            'minimum_stock' => 'nullable|integer|min:0',
            'list_price' => 'nullable|numeric|min:0',
            
            // Estado del producto en catálogo
            'status' => 'nullable|in:active,inactive,discontinued',
        ]);
       
        
        // Valores por defecto para campos opcionales
        $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;
        $validated['list_price'] = $validated['list_price'] ?? 0;
        $validated['status'] = $validated['status'] ?? 'active';
        
        // Asegurar que los booleanos tengan valores correctos
        $validated['requires_sterilization'] = $request->boolean('requires_sterilization');
        $validated['requires_refrigeration'] = $request->boolean('requires_refrigeration');
        $validated['requires_temperature'] = $request->boolean('requires_temperature');
        
        // Crear producto en catálogo
        $product = Product::create($validated);
        
        return redirect()->route('products.index')
            ->with('success', 'Producto agregado al catálogo correctamente.');
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
            'specialty', // Cambiado de medicalSpecialty a specialty
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
        $subcategories = Subcategory::all();

        return view('products.edit', compact('product', 'suppliers', 'categories', 'specialties', 'subcategories'));
    }

    // ==========================================================
    // UPDATE 
    // ==========================================================
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            // Relaciones (FKs)
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category_id' => 'nullable|exists:product_categories,id', 
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'specialty_id' => 'nullable|exists:medical_specialties,id',
            
            // Información básica
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:255', Rule::unique('products', 'code')->ignore($product->id)],
            'description' => 'nullable|string',
            
            // Características booleanas
            'requires_sterilization' => 'nullable|boolean',
            'requires_refrigeration' => 'nullable|boolean',
            'requires_temperature' => 'nullable|boolean',
            
            // Tipo de trazabilidad
            'tracking_type' => 'required|in:code,rfid,serial',
            
            // Información de inventario
            'minimum_stock' => 'nullable|integer|min:0',
            'list_price' => 'nullable|numeric|min:0',
            
            // Estado
            'status' => 'required|in:active,inactive,discontinued',
        ]);

        // Asegurar que los booleanos tengan valores correctos
        $validated['requires_sterilization'] = $request->boolean('requires_sterilization');
        $validated['requires_refrigeration'] = $request->boolean('requires_refrigeration');
        $validated['requires_temperature'] = $request->boolean('requires_temperature');
        
        $product->update($validated);
        
        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    // ==========================================================
    // DESTROY (Eliminación suave)
    // ==========================================================
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete(); // SoftDelete
        
        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    // ==========================================================
    // RESTORE (Restaurar producto eliminado)
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
     * Productos con stock bajo (alerta de reorden)
     */
    public function lowStock(): View
    {
        $products = Product::with(['supplier', 'category'])
            ->where('status', 'active')
            ->where('minimum_stock', '>', 0) // Solo productos con stock mínimo configurado
            ->orderBy('minimum_stock', 'asc')
            ->paginate(10);
        
        return view('products.low-stock', compact('products'));
    }

    /**
     * Búsqueda de productos
     */
    public function search(Request $request): View
    {
        $query = Product::with(['supplier', 'category', 'subcategory', 'specialty']);

        // Búsqueda por nombre o código
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtro por proveedor
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filtro por tipo de tracking
        if ($request->filled('tracking_type')) {
            $query->where('tracking_type', $request->tracking_type);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate(10);
        
        // Para mantener los filtros en la paginación
        $products->appends($request->all());

        return view('products.index', compact('products'));
    }

    public function searchApi(Request $request)
    {
        $query = ProductUnit::with('product')
            ->where('status', 'available');
        
        // Búsqueda por texto
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%");
                })
                ->orWhere('epc', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }
        
        // Filtro por categoría (opcional)
        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        
        // Filtro por proveedor (opcional)
        if ($request->filled('supplier_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }
        
        $products = $query->limit(20)->get();
        
        return response()->json($products->map(function($pu) {
            return [
                'id' => $pu->id,
                'name' => $pu->product->name,
                'code' => $pu->product->code,
                'description' => $pu->product->description,
                'epc' => $pu->epc,
                'serial_number' => $pu->serial_number,
                'text' => $pu->product->name . ' (' . $pu->product->code . ') - ' . ($pu->epc ?? $pu->serial_number ?? 'N/A'),
            ];
        }));
    }


    /**
     * Productos que requieren esterilización
     */
    public function requiresSterilization(): View
    {
        $products = Product::with(['supplier', 'category'])
            ->where('requires_sterilization', true)
            ->where('status', 'active')
            ->orderBy('name')
            ->paginate(10);
        
        return view('products.requires-sterilization', compact('products'));
    }

    /**
     * Productos que requieren refrigeración
     */
    public function requiresRefrigeration(): View
    {
        $products = Product::with(['supplier', 'category'])
            ->where('requires_refrigeration', true)
            ->where('status', 'active')
            ->orderBy('name')
            ->paginate(10);
        
        return view('products.requires-refrigeration', compact('products'));
    }

    /**
     * Productos que requieren control de temperatura
     */
    public function requiresTemperature(): View
    {
        $products = Product::with(['supplier', 'category'])
            ->where('requires_temperature', true)
            ->where('status', 'active')
            ->orderBy('name')
            ->paginate(10);
        
        return view('products.requires-temperature', compact('products'));
    }

    /**
     * Estadísticas generales del catálogo
     */
    public function statistics(): View
    {
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'inactive' => Product::where('status', 'inactive')->count(),
            'discontinued' => Product::where('status', 'discontinued')->count(),
            'tracking_code' => Product::where('tracking_type', 'code')->count(),
            'tracking_rfid' => Product::where('tracking_type', 'rfid')->count(),
            'tracking_serial' => Product::where('tracking_type', 'serial')->count(),
            'requires_sterilization' => Product::where('requires_sterilization', true)->count(),
            'requires_refrigeration' => Product::where('requires_refrigeration', true)->count(),
            'requires_temperature' => Product::where('requires_temperature', true)->count(),
            'by_category' => Product::with('category')
                ->selectRaw('category_id, count(*) as total')
                ->groupBy('category_id')
                ->get(),
            'by_supplier' => Product::with('supplier')
                ->selectRaw('supplier_id, count(*) as total')
                ->groupBy('supplier_id')
                ->get(),
        ];
        
        return view('products.statistics', compact('stats'));
    }

    /**
     * Exportar catálogo a CSV
     */
    public function exportCsv()
    {
        $products = Product::with(['supplier', 'category', 'subcategory', 'specialty'])->get();
        
        $filename = 'catalogo_productos_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($file, [
                'ID',
                'Código',
                'Nombre',
                'Descripción',
                'Proveedor',
                'Categoría',
                'Subcategoría',
                'Especialidad',
                'Tipo Tracking',
                'Requiere Esterilización',
                'Requiere Refrigeración',
                'Requiere Control Temperatura',
                'Stock Mínimo',
                'Precio Lista',
                'Estado',
                'Fecha Creación',
            ]);

            // Datos
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->code,
                    $product->name,
                    $product->description,
                    $product->supplier?->name ?? 'N/A',
                    $product->category?->name ?? 'N/A',
                    $product->subcategory?->name ?? 'N/A',
                    $product->specialty?->name ?? 'N/A',
                    $product->tracking_type,
                    $product->requires_sterilization ? 'Sí' : 'No',
                    $product->requires_refrigeration ? 'Sí' : 'No',
                    $product->requires_temperature ? 'Sí' : 'No',
                    $product->minimum_stock,
                    $product->list_price,
                    $product->status,
                    $product->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}