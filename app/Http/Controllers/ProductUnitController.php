<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use App\Models\Product;
//use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductUnitController extends Controller
{
    /**
     * Display a listing of product units.
     */
    public function index(Request $request)
    {
        $query = ProductUnit::with(['product', 'currentLocation']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('epc', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%")
                ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }

        $units = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $products = Product::orderBy('name')->get();

        // Respuesta AJAX → solo devuelve el partial
        if ($request->ajax()) {
            return view('product-units._table', compact('units'));
        }

        return view('product-units.index', compact('units', 'products'));
    }

    /**
     * Show the form for creating a new product unit (ENTRADA).
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();
        //$locations = Location::orderBy('name')->get();
        
        return view('product-units.create', compact('products', ));
    }

    /**
     * Store a newly created product unit (REGISTRAR ENTRADA).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:100',
            'batch_number' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date|after:today',
            'manufacture_date' => 'nullable|date|before_or_equal:today',
            'current_location_id' => 'required|exists:locations,id',
            'acquisition_cost' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_invoice' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $unitsCreated = [];

        // Crear múltiples unidades
        for ($i = 0; $i < $validated['quantity']; $i++) {
            $unit = new ProductUnit();
            $unit->product_id = $validated['product_id'];
            
            // Generar EPC o Serial según el tipo de producto
            if ($product->rfid_tracking) {
                $unit->epc = $this->generateEPC();
            } else {
                $unit->serial_number = $this->generateSerial();
            }
            
            $unit->batch_number = $validated['batch_number'] ?? null;
            $unit->expiration_date = $validated['expiration_date'] ?? null;
            $unit->manufacture_date = $validated['manufacture_date'] ?? null;
            $unit->current_location_id = $validated['current_location_id'];
            $unit->acquisition_cost = $validated['acquisition_cost'] ?? null;
            $unit->acquisition_date = now();
            $unit->supplier_id = $validated['supplier_id'] ?? null;
            $unit->supplier_invoice = $validated['supplier_invoice'] ?? null;
            $unit->notes = $validated['notes'] ?? null;
            $unit->status = 'available';
            $unit->created_by = auth()->id();
            
            $unit->save();
            $unitsCreated[] = $unit;
        }

        return redirect()->route('product-units.index')
            ->with('success', "✅ Se registraron {$validated['quantity']} unidades exitosamente")
            ->with('units_created', $unitsCreated);
    }

    /**
     * Display the specified product unit.
     */
    public function show(ProductUnit $productUnit)
    {
        $productUnit->load(['product', 'supplier', 'createdBy', 'movements']);
        
        return view('product-units.show', compact('productUnit'));
    }

    /**
     * Generar código EPC único
     */
    private function generateEPC(): string
    {
        do {
            $epc = 'EPC-' . strtoupper(Str::random(24));
        } while (ProductUnit::where('epc', $epc)->exists());
        
        return $epc;
    }

    /**
     * Generar número de serie único
     */
    private function generateSerial(): string
    {
        do {
            $serial = 'SN-' . strtoupper(Str::random(10));
        } while (ProductUnit::where('serial_number', $serial)->exists());
        
        return $serial;
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');
        
        $products = Product::where('name', 'like', "%{$search}%")
            ->orWhere('code', 'like', "%{$search}%")
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'code']);

        return response()->json($products);
    }
}