<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Services\PhysicalAssemblyService;
use Illuminate\Http\Request;

class PhysicalAssemblyController extends Controller
{
    protected $assemblyService;

    public function __construct(PhysicalAssemblyService $assemblyService)
    {
        $this->assemblyService = $assemblyService;
    }

    // Esta ruta devolverá resultados para que el operador escanee Serial o Lote
    public function searchUnit(Request $request)
    {
        $query = $request->get('q');
        
        // Buscamos por Serial, EPC o Lote que estén disponibles y sueltos
        $units = ProductUnit::with('product')
            ->where('status', ProductUnit::STATUS_AVAILABLE)
            ->whereNull('parent_unit_id')
            ->where(function($q) use ($query) {
                $q->where('serial_number', 'like', "%{$query}%")
                  ->orWhere('batch_number', 'like', "%{$query}%")
                  ->orWhere('epc', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($units);
    }

    // Procesa el guardado
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'location_id' => 'required|exists:storage_locations,id',
            'validated_unit_ids' => 'required|array',
            'validated_unit_ids.*' => 'exists:product_units,id'
        ]);

        try {
            $newBox = $this->assemblyService->assembleSetManual(
                $product, 
                $request->location_id, 
                auth()->id(),
                $request->validated_unit_ids
            );

            return response()->json([
                'success' => true,
                'message' => "¡Caja armada! Serie: {$newBox->serial_number}",
                'redirect' => route('products.index') // O a la vista de la caja
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function create(Product $product)
    {
        if (!$product->is_composite) {
            return redirect()->route('products.index')->with('error', 'Este producto no es un Set armable.');
        }

        // Cargamos la receta y las ubicaciones (ajusta el modelo StorageLocation según el tuyo)
        $product->load('components');
        $locations = \App\Models\StorageLocation::where('is_active', true)->get();

        // Preparamos la receta para AlpineJS
        $recipe = $product->components->map(function($comp) {
            return [
                'product_id' => $comp->id,
                'code' => $comp->code,
                'name' => $comp->name,
                'required_qty' => $comp->pivot->quantity,
                'is_mandatory' => (bool) $comp->pivot->is_mandatory,
                'scanned_qty' => 0 // Inicia en 0
            ];
        });

        return view('physical-assembly.create', compact('product', 'recipe', 'locations'));
    }
}