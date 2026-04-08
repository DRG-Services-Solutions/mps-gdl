<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetBuilderController extends Controller
{
    /**
     * Muestra la lista de todos los productos que son Sets (is_composite = true)
     */
    public function index(Request $request)
    {
        $query = Product::where('is_composite', true)
                        ->withCount('components'); // Cuenta cuántas piezas tiene la receta

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $sets = $query->orderBy('name')->paginate(15);

        return view('sets.index', compact('sets'));
    }

    /**
     * Muestra la pantalla de pantalla completa para armar la receta
     */
    public function build(Product $product)
    {
        // Validamos que realmente sea un Set
        if (!$product->is_composite) {
            return redirect()->route('products.index')->with('error', 'Este producto no está marcado como compuesto (Set).');
        }

        // Cargamos los componentes actuales para mandarlos a Alpine.js
        $product->load('components');
        
        $existingComponents = $product->components->map(function ($comp) {
            return [
                'product_id' => $comp->id,
                'code' => $comp->code,
                'name' => $comp->name,
                'quantity' => $comp->pivot->quantity,
                'is_mandatory' => (bool) $comp->pivot->is_mandatory,
            ];
        });

        return view('sets.build', compact('product', 'existingComponents'));
    }

    /**
     * Guarda la receta usando la magia de sync() de Laravel
     */
    public function save(Request $request, Product $product)
    {
        if (!$product->is_composite) {
            return response()->json(['success' => false, 'message' => 'El producto no es un Set.'], 400);
        }

        $request->validate([
            'items' => 'array',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.is_mandatory' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Preparamos el arreglo para el sync()
            $syncData = [];
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $syncData[$item['product_id']] = [
                        'quantity' => $item['quantity'],
                        'is_mandatory' => $item['is_mandatory'] ?? true,
                    ];
                }
            }

            // Sync borra lo que ya no está, actualiza lo que cambió y agrega lo nuevo. ¡Magia pura!
            $product->components()->sync($syncData);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Receta guardada exitosamente.',
                'redirect' => route('sets.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}