<?php

namespace App\Http\Controllers;

use App\Models\SurgicalChecklist;
use App\Models\ChecklistItem;
use App\Models\ChecklistConditional;
use App\Models\Product;
use App\Models\LegalEntity;
use Illuminate\Http\Request;

class ChecklistItemController extends Controller
{
    /**
     * Agregar item al check list
     */
    public function store(Request $request, SurgicalChecklist $checklist)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'is_mandatory' => 'required|boolean',
            'order' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        // Verificar que no exista el producto
        if ($checklist->items()->where('product_id', $validated['product_id'])->exists()) {
            return back()->with('error', 'Este producto ya existe en el check list.');
        }

        $item = $checklist->items()->create($validated);

        return back()->with('success', 'Producto agregado al check list exitosamente.');
    }

    /**
     * Actualizar item
     */
    public function update(Request $request, ChecklistItem $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'is_mandatory' => 'required|boolean',
            'order' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $item->update($validated);

        return back()->with('success', 'Item actualizado exitosamente.');
    }

    /**
     * Eliminar item
     */
    public function destroy(ChecklistItem $item)
    {
        $item->delete();

        return back()->with('success', 'Item eliminado del check list exitosamente.');
    }

    /**
     * Agregar condicional a un item
     */
    public function addConditional(Request $request, ChecklistItem $item)
    {
        $validated = $request->validate([
            'legal_entity_id' => 'nullable|exists:legal_entities,id',
            'payment_mode' => 'nullable|in:particular,aseguradora',
            'condition_type' => 'required|in:required,excluded,optional',
            'quantity_multiplier' => 'required|numeric|min:0|max:10',
            'notes' => 'nullable|string',
        ]);

        // Al menos uno debe estar presente
        if (empty($validated['legal_entity_id']) && empty($validated['payment_mode'])) {
            return back()->with('error', 'Debe seleccionar al menos Hospital/Doctor o Modalidad.');
        }

        $item->conditionals()->create($validated);

        return back()->with('success', 'Condicional agregado exitosamente.');
    }

    /**
     * Eliminar condicional
     */
    public function removeConditional(ChecklistConditional $conditional)
    {
        $conditional->delete();

        return back()->with('success', 'Condicional eliminado exitosamente.');
    }

    /**
     * Reordenar items
     */
    public function reorder(Request $request, SurgicalChecklist $checklist)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:checklist_items,id',
            'items.*.order' => 'required|integer',
        ]);

        foreach ($validated['items'] as $itemData) {
            ChecklistItem::where('id', $itemData['id'])
                ->update(['order' => $itemData['order']]);
        }

        return response()->json(['success' => true]);
    }
}