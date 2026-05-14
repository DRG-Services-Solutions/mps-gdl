<?php

namespace App\Http\Controllers;

use App\Models\StockUnit;
use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Validation\Rule;

class StockUnitRecipeController extends Controller
{
    public function store(Request $request, StockUnit $stockUnit)
    {
        $allowedTypes = $stockUnit->item->getAllowedChildTypes();
        if (empty($allowedTypes)) {
            return back()->with('error', 'Por regla de jerarquía, este tipo de elemento no puede contener componentes.');
        }

        $validated = $request->validate([
            'child_item_id' => [
                'required',
                'exists:items,id',
               Rule::unique('stock_unit_recipes', 'item_id')->where(function ($query) use ($stockUnit) {
                    return $query->where('stock_unit_id', $stockUnit->id);
                })
            ],
            'quantity'         => 'required|integer|min:1',
            'requirement_type' => 'required|in:mandatory,optional,conditional',
            'condition_rules'  => 'nullable|json',
            'notes'            => 'nullable|string|max:255',
        ]);

        $child = Item::find($validated['child_item_id']);
        if (!in_array($child->type, $allowedTypes)) {
            return back()->with('error', "La jerarquía estricta no permite agregar un(a) '{$child->type_label}' a un(a) '{$stockUnit->item->type_label}'.");
        }

        $stockUnit->requiredItems()->attach($validated['child_item_id'], [
            'quantity'         => $validated['quantity'],
            'requirement_type' => $validated['requirement_type'],
            'condition_rules'  => $validated['condition_rules'] ?? null,
            'notes'            => $validated['notes'] ?? null
        ]);

        return back()->with('success', 'Componente asignado a la receta de esta unidad.');
    }

    public function destroy(StockUnit $stockUnit, $itemId)
    {
        $stockUnit->requiredItems()->detach($itemId);
        return back()->with('success', 'Componente retirado de la unidad física.');
    }
}