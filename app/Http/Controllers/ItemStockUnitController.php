<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemStockUnitController extends Controller
{
    public function store(Request $request, Item $item)
    {
        $validated = $request->validate([
            // Validamos que el serial/DPM sea único pero SOLO para este mismo modelo (Item)
            'serial_number' => [
                'required', 
                'string', 
                Rule::unique('stock_units')->where(function ($query) use ($item) {
                    return $query->where('item_id', $item->id);
                })
            ],
        ]);

        // Creamos la unidad física. Por defecto nacerá con status 'sterile' (o el default de tu BD)
        $stockUnit = $item->stockUnits()->create([
            'serial_number' => $validated['serial_number'],
            'total_uses'    => 0,
        ]);

        return back()->with('success', "Unidad física registrada exitosamente. NS/DPM: {$stockUnit->serial_number}");
    }

    public function destroy(Item $item, StockUnit $stockUnit)
    {
        // Validar que la unidad pertenezca al catálogo correcto
        if ($stockUnit->item_id !== $item->id) {
            abort(403);
        }

        // Regla WMS: No se puede eliminar si está en quirófano o en lavado
        if (in_array($stockUnit->status, ['in_surgery', 'in_process'])) {
            return back()->with('error', 'No puedes eliminar una unidad física que está actualmente en operación.');
        }

        $stockUnit->delete();
        return back()->with('success', 'Unidad física dada de baja del inventario.');
    }
}