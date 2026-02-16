<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SubWarehouse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $subWarehouseId = $request->get('sub_warehouse_id');
        $search = $request->get('search');

        $query = Product::query();

        // ---------------------------------------------------------
        // 1. FILTRO: Solo productos con existencias (Stock > 0)
        // ---------------------------------------------------------
        $query->whereHas('inventorySummaries', function ($q) use ($subWarehouseId) {
            // Filtramos dentro de la relación:
            $q->where('quantity_on_hand', '>', 0);
            
            // Si hay filtro de almacén, solo consideramos stock de ese almacén
            if ($subWarehouseId) {
                $q->where('sub_warehouse_id', $subWarehouseId);
            }
        });

        // ---------------------------------------------------------
        // 2. ORDENAMIENTO FEFO (First Expired, First Out)
        // ---------------------------------------------------------
        // Creamos una columna virtual 'next_expiration' que contiene
        // la fecha del lote MÁS PRÓXIMO a vencer que tenga stock.
        $subQuery = \App\Models\InventorySummary::select('expiration_date')
            ->whereColumn('product_id', 'products.id') // Relación
            ->where('quantity_on_hand', '>', 0)        // Solo lotes con stock
            ->whereNotNull('expiration_date')          // Ignoramos los que no caducan
            ->orderBy('expiration_date', 'asc')        // El más viejo primero
            ->limit(1);

        if ($subWarehouseId) {
            $subQuery->where('sub_warehouse_id', $subWarehouseId);
        }

        // Inyectamos esa fecha en la consulta principal
        $query->addSelect(['next_expiration' => $subQuery]);

        // Ordenamos: 
        // Primero los que tienen fecha de caducidad (IS NULL da 0 si tiene fecha, 1 si es null)
        // Luego por la fecha ascendente (lo que vence mañana sale antes que lo de pasado mañana)
        $query->orderByRaw('CASE WHEN next_expiration IS NULL THEN 1 ELSE 0 END')
            ->orderBy('next_expiration', 'asc');


        // ---------------------------------------------------------
        // 3. RESTO DE LA LÓGICA (Buscador, Sumas y Carga Previa)
        // ---------------------------------------------------------
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Carga para el Modal (Detalle de lotes)
        $query->with(['inventorySummaries' => function ($q) use ($subWarehouseId) {
            if ($subWarehouseId) $q->where('sub_warehouse_id', $subWarehouseId);
            $q->where('quantity_on_hand', '>', 0); // Opcional: Solo mostrar lotes con stock en el modal también
            $q->orderBy('expiration_date', 'asc');
            $q->with('subWarehouse');
        }]);

        // Sumas totales
        $query->withSum(['inventorySummaries as total_stock' => function ($q) use ($subWarehouseId) {
            if ($subWarehouseId) $q->where('sub_warehouse_id', $subWarehouseId);
        }], 'quantity_on_hand');

        $query->withSum(['inventorySummaries as total_reserved' => function ($q) use ($subWarehouseId) {
            if ($subWarehouseId) $q->where('sub_warehouse_id', $subWarehouseId);
        }], 'quantity_reserved');

        $products = $query->paginate(20)->withQueryString();
        $subWarehouses = SubWarehouse::all();

        return view('inventory.index', compact('products', 'subWarehouses'));
    }
}