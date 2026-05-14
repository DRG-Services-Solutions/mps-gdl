<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemRelation;
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
            'relation_type' => 'required|in:requires,compatible_with,conflicts_with,alternative_to',
            'notes' => 'nullable|string|max:255'
        ]);

        // Guardamos o actualizamos la regla
        ItemRelation::updateOrCreate(
            [
                'item_id' => $item->id,
                'related_item_id' => $validated['related_item_id'],
            ],
            [
                'relation_type' => $validated['relation_type'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Opcional (Arquitectura Espejo): Si A es compatible con B, ¿B es compatible con A?
        // En relaciones como 'compatible_with' o 'conflicts_with', podríamos replicar la regla a la inversa automáticamente.

        return redirect()->route('items.show', $item)
                         ->with('success', 'Regla de compatibilidad definida con éxito.');
    }

    /**
     * Elimina una regla de la matriz
     */
    public function destroy(Item $item, ItemRelation $relation)
    {
        if ($relation->item_id !== $item->id) {
            abort(403);
        }

        $relation->delete();

        return redirect()->route('items.show', $item)
                         ->with('success', 'Regla de compatibilidad eliminada.');
    }
}