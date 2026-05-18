<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemRelationController extends Controller
{
    /**
     * Define una regla de compatibilidad entre dos equipos del catálogo
     */
    public function store(Request $request, Item $item)
    {
        $validated = $request->validate([
            'related_item_id' => [
                'required', 
                'exists:items,id',
                Rule::notIn([$item->id]) // BLINDAJE: Un equipo no puede relacionarse consigo mismo
            ],
            'type' => 'required|in:required,suggested,compatible',
            'notes' => 'nullable|string|max:255'
        ]);

        // Guardamos o actualizamos la regla usando la relación de Eloquent
        $item->relations()->syncWithoutDetaching([
            $validated['related_item_id'] => [
                'type' => $validated['type'],
                'notes' => $validated['notes'] ?? null,
            ]
        ]);

        // Opcional (Arquitectura Espejo): Si A es compatible con B, ¿B es compatible con A?
        if ($validated['type'] === 'compatible') {
            $relatedItem = Item::find($validated['related_item_id']);
            $relatedItem->relations()->syncWithoutDetaching([
                $item->id => [
                    'type' => 'compatible',
                    'notes' => $validated['notes'] ?? null,
                ]
            ]);
        }

        return redirect()->route('items.show', $item)
                         ->with('success', 'Regla de compatibilidad definida con éxito.');
    }

    /**
     * Elimina una regla de la matriz
     */
    public function destroy(Item $item, $relatedItemId)
    {
        $relation = $item->relations()->where('related_item_id', $relatedItemId)->first();

        if ($relation) {
            $item->relations()->detach($relatedItemId);

            if ($relation->pivot->type === 'compatible') {
                $relatedItem = Item::find($relatedItemId);
                if ($relatedItem) {
                    $relatedItem->relations()->detach($item->id);
                }
            }
        }

        return redirect()->route('items.show', $item)
                         ->with('success', 'Regla de compatibilidad eliminada.');
    }
}