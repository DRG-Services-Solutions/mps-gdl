<?php

namespace App\Http\Controllers;

use App\Models\ProductLayout;
use App\Models\StorageLocation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductLayoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productLayouts = ProductLayout::with('storageLocation')
                                        ->latest()
                                        ->paginate(20);
        return view('product_layouts.index', compact('productLayouts'));                                
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $storageLocations = StorageLocation::orderBy('name')->get();

        return view('product_layouts.create', compact('storageLocations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            //Claves foraneas de la tabla, estas deben existir en sus propias tablas
            'storage_location_id' => 'required|exists:storage_locations,id',
            //estante
            'shelf' => 'required|integer|min:1',

            //nivel
            'level' => 'required|string|max:2',

            //posicion
            'position' => 'required|numeric',
        ]);

        ProductLayout::create($validated);

        return redirect()->route('product_layouts.index')->with('success', 'Layout del producto creado con Exito');

       
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductLayout $productLayout)
    {
        $productLayout->load(['storageLocation', 'product.subcategory']);
        
        return view('product_layouts.show', compact('productLayout'));
    }

    /**
     * Search products for assignment (AJAX endpoint).
     */
    /**
 * Search products for assignment (AJAX endpoint).
 */
    public function searchProducts(Request $request)
    {
        try {
            $query = $request->input('q', '');
            
            // Validación mínima
            if (strlen($query) < 2) {
                return response()->json(['products' => []]);
            }
            
            $products = Product::query()
                ->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('sku', 'LIKE', "%{$query}%");
                    
                    // Si el query es numérico, buscar por ID también
                    if (is_numeric($query)) {
                        $q->orWhere('id', $query);
                    }
                })
                ->with(['productLayouts']) // Cargar relación
                ->limit(20)
                ->get()
                ->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku ?? 'N/A',
                        'has_layout' => $product->productLayouts->isNotEmpty(),
                    ];
                });

            return response()->json([
                'success' => true,
                'products' => $products
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error searching products: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar productos',
                'products' => []
            ], 500);
        }
    }

    /**
     * Assign a product to the layout.
     */
    public function assignProduct(Request $request, ProductLayout $productLayout)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Si ya tiene producto, es un cambio
        $action = $productLayout->hasProduct() ? 'cambiado' : 'asignado';

        $productLayout->assignProduct($product->id);

        return redirect()
            ->route('product_layouts.show', $productLayout)
            ->with('success', "Producto \"{$product->name}\" {$action} correctamente a la ubicación {$productLayout->full_location_code}.");
    }

    /**
     * Remove the product from the layout.
     */
    public function removeProduct(ProductLayout $productLayout)
    {
        if (!$productLayout->hasProduct()) {
            return redirect()
                ->route('product_layouts.show', $productLayout)
                ->with('error', 'Esta ubicación no tiene ningún producto asignado.');
        }

        $productName = $productLayout->product->name;
        $productLayout->removeProduct();

        return redirect()
            ->route('product_layouts.show', $productLayout)
            ->with('success', "Producto \"{$productName}\" removido correctamente de la ubicación.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductLayout $productLayout)
    {
        $storageLocations = StorageLocation::orderBy('name')->get();
        $productLayout->load('storafeLocation');

        return view('product_lauouts.edit', compact('storageLocations', 'productLayout'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductLayout $productLayout)
    {
        $validated = $request->validate([
            //Claves foraneas que deben existir en sus respectivas tablas
            'storage_location_id' => 'required|exists:storage_locations,id',
            'product_id' => 'required|exists:products,id',
            
            //Campos actuales de ubicacion
            'shelf' => 'required|integer|min:1',
            'level' => 'required|string|max:2',
            'position' => 'required|numeric',

        ]);

        $productLayout->update($validated);
        return redirect()->route('product_layouts.index')
                         ->with('success', 'Layout Actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductLayout $productLayout)
    {
        $productLayout->delete();
    }
}
