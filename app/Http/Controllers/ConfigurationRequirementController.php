<?php

namespace App\Http\Controllers;

use App\Models\ChecklistConfiguration;
use App\Models\ConfigurationRequirement;
use Illuminate\Http\Request;

class ConfigurationRequirementController extends Controller
{
    public function store(Request $request, ChecklistConfiguration $configuration)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'requirement_type' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['configuration_id'] = $configuration->id;

        // Evitar duplicados del mismo item en la misma configuración
        $exists = ConfigurationRequirement::where('configuration_id', $configuration->id)
            ->where('item_id', $validated['item_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Este equipo ya está agregado a esta configuración.');
        }

        ConfigurationRequirement::create($validated);

        return back()->with('success', 'Equipo agregado a la configuración exitosamente.');
    }

    public function destroy(ConfigurationRequirement $requirement)
    {
        $requirement->delete();

        return back()->with('success', 'Equipo eliminado de la configuración.');
    }
}
