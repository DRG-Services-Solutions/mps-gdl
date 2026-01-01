<?php

namespace App\Http\Controllers;

use App\Models\SurgicalChecklist;
use App\Models\Product;
use Illuminate\Http\Request;

class SurgicalChecklistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SurgicalChecklist::query()->with('items.product');

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por tipo de cirugía
        if ($request->filled('surgery_type')) {
            $query->where('surgery_type', 'like', '%' . $request->surgery_type . '%');
        }

        // Búsqueda
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        $checklists = $query->latest()->paginate(15);

        return view('checklists.index', compact('checklists'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('checklists.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:surgical_checklists,code|max:50',
            'name' => 'required|string|max:255',
            'surgery_type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $checklist = SurgicalChecklist::create($validated);

        return redirect()
            ->route('checklists.show', $checklist)
            ->with('success', 'Check list creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SurgicalChecklist $checklist)
    {
        $checklist->load([
            'items.product',
            'items.conditionals.legalEntity',
            'preAssembledPackages',
            'scheduledSurgeries'
        ]);

        return view('checklists.show', compact('checklist'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SurgicalChecklist $checklist)
    {
        return view('checklists.edit', compact('checklist'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SurgicalChecklist $checklist)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:surgical_checklists,code,' . $checklist->id,
            'name' => 'required|string|max:255',
            'surgery_type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $checklist->update($validated);

        return redirect()
            ->route('checklists.show', $checklist)
            ->with('success', 'Check list actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SurgicalChecklist $checklist)
    {
        // Verificar que no tenga cirugías programadas
        if ($checklist->scheduledSurgeries()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un check list con cirugías programadas.');
        }

        $checklist->delete();

        return redirect()
            ->route('checklists.index')
            ->with('success', 'Check list eliminado exitosamente.');
    }

    /**
     * Mostrar vista de gestión de items
     */
    public function items(SurgicalChecklist $checklist)
    {
        // Paginar los items (20 por página)
        $items = $checklist->items()
            ->with([
                'product',
                'conditionals.legalEntity'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Productos para el select
        $products = Product::select('id', 'code', 'name')
            ->orderBy('name')
            ->get();

        return view('checklists.items', compact('checklist', 'items', 'products'));
    }

    /**
     * Duplicar check list
     */
    public function duplicate(SurgicalChecklist $checklist)
    {
        $newChecklist = $checklist->replicate();
        $newChecklist->code = $checklist->code . '-COPY-' . rand(100, 999);
        $newChecklist->name = $checklist->name . ' (Copia)';
        $newChecklist->status = 'inactive';
        $newChecklist->save();

        // Duplicar items
        foreach ($checklist->items as $item) {
            $newItem = $item->replicate();
            $newItem->checklist_id = $newChecklist->id;
            $newItem->save();

            // Duplicar condicionales
            foreach ($item->conditionals as $conditional) {
                $newConditional = $conditional->replicate();
                $newConditional->checklist_item_id = $newItem->id;
                $newConditional->save();
            }
        }

        return redirect()
            ->route('checklists.show', $newChecklist)
            ->with('success', 'Check list duplicado exitosamente.');
    }
}