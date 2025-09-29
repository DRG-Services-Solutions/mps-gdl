<?php

namespace App\Http\Controllers;

use App\Models\MedicalSpecialty;
use Illuminate\Http\Request;

class MedicalSpecialtyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specialties = MedicalSpecialty::withCount('products')->latest()->paginate(10); 
        return view('specialties.index', compact('specialties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('specialties.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:medical_specialties,name',
            'description' => 'nullable|string',
        ]);

        MedicalSpecialty::create($request->all());

        return redirect()->route('specialties.index')
                         ->with('success', 'Especialidad creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicalSpecialty $medicalSpecialty)
    {
        return view('specialties.show', compact('specialty'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MedicalSpecialty $medicalSpecialty)
    {
        return view('specialties.edit', compact('specialty'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicalSpecialty $medicalSpecialty)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:medical_specialties,name,' . $specialty->id,
            'description' => 'nullable|string',
        ]);

        $specialty->update($request->all());

        return redirect()->route('specialties.index')
                         ->with('success', 'Especialidad actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicalSpecialty $medicalSpecialty)
    {
        $specialty->delete();

        return redirect()->route('specialties.index')
                         ->with('success', 'Especialidad eliminada exitosamente.');
    }
}
