<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Hospital;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors.
     */
    public function index(Request $request)
    {
        $query = Doctor::with('primaryHospital');

        // Búsqueda
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtro por especialidad
        if ($request->filled('specialty')) {
            $query->bySpecialty($request->specialty);
        }

        // Filtro por hospital
        if ($request->filled('hospital_id')) {
            $query->byHospital($request->hospital_id);
        }

        // Filtro de estado
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $doctors = $query->orderBy('first_name')->paginate(20);

        
        // Lista de hospitales para filtro
        $hospitals = Hospital::active()->orderBy('name')->get();

        return view('doctors.index', compact('doctors', 'hospitals'));
    }

    /**
     * Show the form for creating a new doctor.
     */
    public function create()
    {
        $hospitals = Hospital::active()->orderBy('name')->get();
        
        return view('doctors.create', compact('hospitals'));
    }

    /**
     * Store a newly created doctor in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:50',
            'is_active' => 'boolean',

        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;


        $doctor = Doctor::create($validated);

        return redirect()
            ->route('doctors.show', $doctor)
            ->with('success', 'Doctor creado exitosamente.');
    }

    /**
     * Display the specified doctor.
     */
    public function show(Doctor $doctor)
    {
        $doctor->load(['primaryHospital', 'quotations' => function ($query) {
            $query->latest()->limit(10);
        }]);

        $stats = [
            'total_quotations' => $doctor->getTotalQuotations(),
        ];

        return view('doctors.show', compact('doctor', 'stats'));
    }

    /**
     * Show the form for editing the specified doctor.
     */
    public function edit(Doctor $doctor)
    {
        $hospitals = Hospital::active()->orderBy('name')->get();
        
        return view('doctors.edit', compact('doctor', 'hospitals'));
    }

    /**
     * Update the specified doctor in storage.
     */
    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $doctor->update($validated);

        return redirect()
            ->route('doctors.show', $doctor)
            ->with('success', 'Doctor actualizado exitosamente.');
    }

    /**
     * Remove the specified doctor from storage.
     */
    public function destroy(Doctor $doctor)
    {
        // Verificar que no tenga cotizaciones
        if ($doctor->quotations()->count() > 0) {
            return redirect()
                ->route('doctors.show', $doctor)
                ->with('error', 'No se puede eliminar el doctor porque tiene cotizaciones asociadas.');
        }

        $doctor->delete();

        return redirect()
            ->route('doctors.index')
            ->with('success', 'Doctor eliminado exitosamente.');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Doctor $doctor)
    {
        $doctor->update([
            'is_active' => !$doctor->is_active,
        ]);

        $status = $doctor->is_active ? 'activado' : 'desactivado';

        return redirect()
            ->back()
            ->with('success', "Doctor {$status} exitosamente.");
    }

    /**
     * Get doctors for select2 (API).
     */
    public function select2(Request $request)
    {
        $query = Doctor::active();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('hospital_id')) {
            $query->byHospital($request->hospital_id);
        }

        $doctors = $query->limit(20)->get();

        return response()->json([
            'results' => $doctors->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'text' => $doctor->name_with_specialty,
                ];
            }),
        ]);
    }
}