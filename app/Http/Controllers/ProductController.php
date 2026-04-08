<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use App\Models\Product;
use App\Models\Supplier; 
use App\Models\Category;
use App\Models\MedicalSpecialty;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use App\Models\Instrument;
use App\Models\InstrumentKit;


class ProductController extends Controller
{
    
   public function index(Request $request): View
    {
        $consumibles = Product::where('product_type_id', 1)->count();
        $instrumentales = Product::where('product_type_id', 2)->count();
        $query = Product::with([
            'supplier', 
            'category',
            'productType',
        ]);
        
        // ========================================
        // FILTRO: Búsqueda por nombre o código
        // ========================================
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
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
        if ($request->filled('product_type_id')) {
            $query->where('product_type_id', $request->product_type_id);
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
        $suppliers = Supplier::orderBy('name')->get();
        $product_types = ProductType::orderBy('name')->get();

        //Conteos totales para mostrar en tarjetas
        $trackingCounts = [
        'total'  => Product::count(),
        'code'   => Product::where('tracking_type', 'code')->count(),
        'rfid'   => Product::where('tracking_type', 'rfid')->count(),
        'serial' => Product::where('tracking_type', 'serial')->count(),
    ];

        
        return view('products.index', compact('products', 'suppliers', 'product_types', 'trackingCounts', 'consumibles', 'instrumentales'));
    }

    // ==========================================================
    // CREATE
    // ==========================================================
    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get(); 
        $categories = Category::orderBy('name')->get();
        $product_types = ProductType::orderBy('name')->get();
        
        
        return view('products.create', compact('suppliers', 'categories', 'product_types'));
    }

    // STORE (VALIDACIÓN Y CREACIÓN DE PRODUCTO)
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Relaciones (FKs)
            'supplier_id' => 'nullable|exists:suppliers,id', 
            'category_id' => 'nullable|exists:product_categories,id',
            'product_type_id' => 'required|exists:product_types,id', 
            
            // Información básica del catálogo
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,code',
            'description' => 'nullable|string',
            
            // Arquitectura de Composición y Reglas Médicas
            'is_composite' => 'nullable|boolean',
            'has_expiration_date' => 'nullable|boolean',
            'requires_sterilization' => 'nullable|boolean',
            'requires_refrigeration' => 'nullable|boolean',
            'requires_temperature' => 'nullable|boolean',
           
            // Tipo de trazabilidad
            'tracking_type' => 'required|in:code,rfid,serial,lote', 
            
            // Precios del catálogo
            'list_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            
        ]);
       
        // Valores por defecto numéricos
        $validated['list_price'] = $validated['list_price'] ?? 0;
        $validated['cost_price'] = $validated['cost_price'] ?? 0;
        
        // Casting estricto de booleanos
        $validated['is_composite'] = $request->boolean('is_composite');
        $validated['has_expiration_date'] = $request->boolean('has_expiration_date');
        $validated['requires_sterilization'] = $request->boolean('requires_sterilization');
        $validated['requires_refrigeration'] = $request->boolean('requires_refrigeration');
        $validated['requires_temperature'] = $request->boolean('requires_temperature');
        
        // Crear producto en el Catálogo Maestro
        $product = Product::create($validated);
        
        return redirect()->route('products.index')
            ->with('success', 'Producto agregado al catálogo maestro correctamente.');
    }

    // ==========================================================
    // SHOW
    // ==========================================================
    public function show(Product $product): View
    {
        $product->load([
            'supplier', 
            'category', 
           
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
        $product_types = ProductType::orderBy('name')->get();

        return view('products.edit', compact('product', 'suppliers', 'categories', 'product_types'));
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
            'product_type_id' => 'required|exists:product_types,id', 
            
            // Información básica del catálogo
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:255', Rule::unique('products', 'code')->ignore($product->id)],
            'description' => 'nullable|string',
            
            // Arquitectura de Composición y Reglas Médicas
            'is_composite' => 'nullable|boolean',
            'has_expiration_date' => 'nullable|boolean',
            'requires_sterilization' => 'nullable|boolean',
            'requires_refrigeration' => 'nullable|boolean',
            'requires_temperature' => 'nullable|boolean',
           
            // Tipo de trazabilidad
            'tracking_type' => 'required|in:code,rfid,serial,lote', 
            
            // Precios del catálogo
            'list_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            
        ]);
       
        // Valores por defecto numéricos
        $validated['list_price'] = $validated['list_price'] ?? 0;
        $validated['cost_price'] = $validated['cost_price'] ?? 0;
        
        // Casting estricto de booleanos
        $validated['is_composite'] = $request->boolean('is_composite');
        $validated['has_expiration_date'] = $request->boolean('has_expiration_date');
        $validated['requires_sterilization'] = $request->boolean('requires_sterilization');
        $validated['requires_refrigeration'] = $request->boolean('requires_refrigeration');
        $validated['requires_temperature'] = $request->boolean('requires_temperature');
        
        //Actualizar producto en el Catálogo Maestro
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
        $query = Product::with(['supplier', 'category', 'specialty']);

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
        $query = ProductUnit::with(['product', 'legalEntity', 'subWarehouse'])
            ->where('status', 'available');
        
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                })
                ->orWhere('epc', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }
        
        $products = $query->limit(20)->get();
        
        return response()->json($products->map(function($pu) {
            return [
                'id' => $pu->id,
                'name' => $pu->product->name,
                'code' => $pu->product->code,
                'epc' => $pu->epc,
                'serial_number' => $pu->serial_number,
                'available_quantity' => $pu->quantity ?? 1, // ← AGREGAR ESTA LÍNEA
                'legal_entity' => $pu->legalEntity->name ?? null,
                'sub_warehouse_name' => $pu->subWarehouse->name ?? 'N/A',  // ← CAMBIAR A sub_warehouse_name
                
            ];
        }));
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
        $products = Product::with(['supplier', 'category', 'specialty'])->get();
        
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

    public function select2(Request $request)
    {
        $search = $request->search;
        
        // ¡Todo está en la misma tabla ahora!
        $results = Product::with('productType')
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(function($p) {
                // Si is_composite es true, le ponemos el icono de Kit/Set
                $icono = $p->is_composite ? '🧳 [SET/KIT]' : ($p->product_type_id == 2 ? '✂️ [Instrumental]' : '📦 [Insumo]');
                
                return [
                    'id' => $p->id,
                    'text' => "{$icono} {$p->code} — {$p->name}",
                    'price' => $p->list_price,
                    'is_composite' => $p->is_composite
                ];
            });

        return response()->json($results);
    }

}