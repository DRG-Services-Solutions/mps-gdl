<?php

namespace App\Http\Controllers;

use App\Models\StockUnit;
use App\Models\Item;

class StockUnitController extends Controller
{
    public function show(StockUnit $stockUnit)
    {
        // Cargamos la pieza física, quién es su "Padre Lógico" (Item), y su Receta Exacta
        $stockUnit->load(['item', 'requiredItems']);

        // Buscamos el catálogo disponible para agregar a esta torre
        // Excluimos la categoría de 'tower' para no meter una torre dentro de otra torre
        $availableItems = Item::where('is_active', true)
            ->whereNotIn('type', ['tower', 'equipment']) // Ajusta según tus tipos
            ->orderBy('type')
            ->orderBy('name', 'asc')
            ->get(['id', 'code', 'name', 'type']);

        return view('stock-units.show', compact('stockUnit', 'availableItems'));
    }
}