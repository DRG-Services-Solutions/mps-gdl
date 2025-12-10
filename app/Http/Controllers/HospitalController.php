<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    /**
     * Display a listing of hospitals.
     */
    public function index(Request $request)
    {
        $query = Hospital::query();

        // Búsqueda
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtro de estado
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $hospitals = $query->orderBy('name')->paginate(20);

        return view('hospitals.index', compact('hospitals'));
    }

    /**
     * Show the form for creating a new hospital.
     */
    public function create()
    {
        return view('hospitals.create');
    }

    /**
     * Store a newly created hospital in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
            'rfc' => 'nullable|string',
            'razon_social' => 'nullable|string',
        ]);

        $hospital = Hospital::create($validated);

        return redirect()
            ->route('hospitals.show', $hospital)
            ->with('success', 'Hospital creado exitosamente.');
    }

    /**
     * Display the specified hospital.
     */
    public function show(Hospital $hospital)
    {

    return view('hospitals.show', compact('hospital'));    
    }

    /**
     * Show the form for editing the specified hospital.
     */
    public function edit(Hospital $hospital)
    {
        return view('hospitals.edit', compact('hospital'));
    }

    /**
     * Update the specified hospital in storage.
     */
    public function update(Request $request, Hospital $hospital)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'rfc' => 'nullable|string',
            'razon_social' => 'nullable|string',
        ]);

        $hospital->update($validated);

        return redirect()
            ->route('hospitals.show', $hospital)
            ->with('success', 'Hospital actualizado exitosamente.');
    }

    /**
     * Remove the specified hospital from storage.
     */
    public function destroy(Hospital $hospital)
    {
        // Verificar que no tenga cotizaciones o ventas
        if ($hospital->quotations()->count() > 0) {
            return redirect()
                ->route('hospitals.show', $hospital)
                ->with('error', 'No se puede eliminar el hospital porque tiene cotizaciones asociadas.');
        }

        if ($hospital->sales()->count() > 0) {
            return redirect()
                ->route('hospitals.show', $hospital)
                ->with('error', 'No se puede eliminar el hospital porque tiene ventas asociadas.');
        }

        $hospital->delete();

        return redirect()
            ->route('hospitals.index')
            ->with('success', 'Hospital eliminado exitosamente.');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Hospital $hospital)
    {
        $hospital->update([
            'is_active' => !$hospital->is_active,
        ]);

        $status = $hospital->is_active ? 'activado' : 'desactivado';

        return redirect()
            ->back()
            ->with('success', "Hospital {$status} exitosamente.");
    }

    /**
     * Get hospitals for select2 (API).
     */
    public function select2(Request $request)
    {
        $query = Hospital::all();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $hospitals = $query->limit(20)->get();

        return response()->json([
            'results' => $hospitals->map(function ($hospital) {
                return [
                    'id' => $hospital->id,
                    'text' => $hospital->name,
                ];
            }),
        ]);
    }
}