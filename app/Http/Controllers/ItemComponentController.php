<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemComponentController extends Controller
{
    public function store(Request $request, Item $item)
    {
        // 1. Validamos por jerarquía si el Item padre puede contener otros elementos
        $allowedTypes = $item->getAllowedChildTypes();
        if (empty($allowedTypes)) {
            return back()->with('error', 'Por regla de jerarquía, este tipo de elemento no puede contener componentes.');
        }

        // 2. Validamos los datos de entrada
        $validated = $request->validate([
            'child_item_id' => 'required|exists:items,id|different:' . $item->id, // No puede contenerse a sí mismo
            'quantity'      => 'required|integer|min:1',
            'is_mandatory'  => 'boolean',
            'notes'         => 'nullable|string|max:255'
        ]);

        $child = Item::find($validated['child_item_id']);
        if (!in_array($child->type, $allowedTypes)) {
            return back()->with('error', "La jerarquía estricta no permite agregar un(a) '{$child->type_label}' a un(a) '{$item->type_label}'.");
        }

        // 3. Verificamos si el instrumento ya existe en la receta
        if ($item->components()->where('child_item_id', $validated['child_item_id'])->exists()) {
            return back()->with('error', 'Este instrumento ya forma parte de la receta. Edita su cantidad en lugar de duplicarlo.');
        }

        // 4. Guardamos la relación en la tabla pivote (item_components)
        $item->components()->attach($validated['child_item_id'], [
            'quantity'     => $validated['quantity'],
            'is_mandatory' => $request->has('is_mandatory') ? 1 : 0,
            'notes'        => $validated['notes']
        ]);

        return back()->with('success', 'Instrumento agregado a la receta correctamente.');
    }

    public function destroy(Item $item, $componentId)
    {
        $item->components()->detach($componentId);
        return back()->with('success', 'Instrumento retirado de la receta.');
    }
}