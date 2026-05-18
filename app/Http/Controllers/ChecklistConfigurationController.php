<?php

namespace App\Http\Controllers;

use App\Models\SurgicalChecklist;
use App\Models\ChecklistConfiguration;
use Illuminate\Http\Request;

class ChecklistConfigurationController extends Controller
{
    public function index(SurgicalChecklist $checklist)
    {
        $configurations = ChecklistConfiguration::where('surgical_checklist_id', $checklist->id)
            ->with(['requirements.item'])
            ->get();

        return view('checklists.configurations', compact('checklist', 'configurations'));
    }

    public function store(Request $request, SurgicalChecklist $checklist)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'nullable|boolean'
        ]);

        $validated['surgical_checklist_id'] = $checklist->id;
        $validated['is_default'] = $request->has('is_default') ? true : false;

        if ($validated['is_default']) {
            // Eliminar default de los demás
            ChecklistConfiguration::where('surgical_checklist_id', $checklist->id)
                ->update(['is_default' => false]);
        }

        ChecklistConfiguration::create($validated);

        return back()->with('success', 'Configuración de torre creada exitosamente.');
    }

    public function update(Request $request, ChecklistConfiguration $configuration)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'nullable|boolean'
        ]);

        $validated['is_default'] = $request->has('is_default') ? true : false;

        if ($validated['is_default']) {
            ChecklistConfiguration::where('surgical_checklist_id', $configuration->surgical_checklist_id)
                ->where('id', '!=', $configuration->id)
                ->update(['is_default' => false]);
        }

        $configuration->update($validated);

        return back()->with('success', 'Configuración actualizada exitosamente.');
    }

    public function destroy(ChecklistConfiguration $configuration)
    {
        $configuration->requirements()->delete();
        $configuration->delete();

        return back()->with('success', 'Configuración eliminada exitosamente.');
    }
}
