<?php
// app/Http/Controllers/ScheduledSurgeryController.php

namespace App\Http\Controllers;

use App\Models\ScheduledSurgery;
use App\Models\SurgicalChecklist;
use App\Models\LegalEntity;
use Illuminate\Http\Request;

class ScheduledSurgeryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ScheduledSurgery::query()
            ->with(['checklist', 'hospital', 'doctor', 'scheduler']);

        // Filtro por fecha
        if ($request->filled('date_from')) {
            $query->where('surgery_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('surgery_date', '<=', $request->date_to);
        }

        // Filtro por hospital
        if ($request->filled('hospital_id')) {
            $query->where('hospital_id', $request->hospital_id);
        }

        // Filtro por doctor
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Búsqueda
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('patient_name', 'like', '%' . $request->search . '%');
            });
        }

        $surgeries = $query->latest('surgery_date')->paginate(15);

        // Para filtros
        $hospitals = LegalEntity::where('type', 'hospital')
            ->select('id', 'business_name')
            ->orderBy('business_name')
            ->get();

        $doctors = LegalEntity::where('type', 'doctor')
            ->select('id', 'business_name')
            ->orderBy('business_name')
            ->get();

        return view('surgeries.index', compact('surgeries', 'hospitals', 'doctors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $checklists = SurgicalChecklist::active()
            ->select('id', 'code', 'name', 'surgery_type')
            ->get();

        $hospitals = LegalEntity::where('type', 'hospital')
            ->select('id', 'business_name')
            ->orderBy('business_name')
            ->get();

        $doctors = LegalEntity::where('type', 'doctor')
            ->select('id', 'business_name')
            ->orderBy('business_name')
            ->get();

        return view('surgeries.create', compact('checklists', 'hospitals', 'doctors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'checklist_id' => 'required|exists:surgical_checklists,id',
            'hospital_id' => 'required|exists:legal_entities,id',
            'doctor_id' => 'required|exists:legal_entities,id',
            'payment_mode' => 'required|in:particular,aseguradora',
            'surgery_date' => 'required|date|after:now',
            'patient_name' => 'nullable|string|max:255',
            'surgery_notes' => 'nullable|string',
        ]);

        $validated['code'] = ScheduledSurgery::generateCode();
        $validated['scheduled_by'] = auth()->id();
        $validated['status'] = 'scheduled';

        $surgery = ScheduledSurgery::create($validated);

        return redirect()
            ->route('surgeries.show', $surgery)
            ->with('success', 'Cirugía agendada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ScheduledSurgery $surgery)
    {
        $surgery->load([
            'checklist.items.product',
            'hospital',
            'doctor',
            'scheduler',
            'preparation.preAssembledPackage',
            'preparation.items.product',
            'invoice'
        ]);

        // Obtener check list con condicionales aplicados
        $checklistItems = $surgery->getChecklistItemsWithConditionals();

        return view('surgeries.show', compact('surgery', 'checklistItems'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScheduledSurgery $surgery)
    {
        if (!$surgery->canBeEdited()) {
            return back()->with('error', 'No se puede editar una cirugía en este estado.');
        }

        $checklists = SurgicalChecklist::active()
            ->select('id', 'code', 'name', 'surgery_type')
            ->get();

        $hospitals = LegalEntity::where('type', 'hospital')
            ->select('id', 'business_name')
            ->orderBy('business_name')
            ->get();

        $doctors = LegalEntity::where('type', 'doctor')
            ->select('id', 'business_name')
            ->orderBy('business_name')
            ->get();

        return view('surgeries.edit', compact('surgery', 'checklists', 'hospitals', 'doctors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ScheduledSurgery $surgery)
    {
        if (!$surgery->canBeEdited()) {
            return back()->with('error', 'No se puede editar una cirugía en este estado.');
        }

        $validated = $request->validate([
            'checklist_id' => 'required|exists:surgical_checklists,id',
            'hospital_id' => 'required|exists:legal_entities,id',
            'doctor_id' => 'required|exists:legal_entities,id',
            'payment_mode' => 'required|in:particular,aseguradora',
            'surgery_date' => 'required|date',
            'patient_name' => 'nullable|string|max:255',
            'surgery_notes' => 'nullable|string',
        ]);

        $surgery->update($validated);

        return redirect()
            ->route('surgeries.show', $surgery)
            ->with('success', 'Cirugía actualizada exitosamente.');
    }

    /**
     * Cancel surgery
     */
    public function cancel(ScheduledSurgery $surgery)
    {
        if (!$surgery->canBeCancelled()) {
            return back()->with('error', 'No se puede cancelar esta cirugía.');
        }

        $surgery->updateStatus('cancelled');

        // Si tiene preparación, liberar recursos
        if ($surgery->preparation) {
            // Liberar paquete pre-armado
            if ($surgery->preparation->preAssembledPackage) {
                $surgery->preparation->preAssembledPackage->updateStatus('available');
            }

            // Liberar product units
            ProductUnit::where('current_surgery_id', $surgery->id)
                ->update([
                    'current_status' => 'in_stock',
                    'current_surgery_id' => null,
                ]);
        }

        return back()->with('success', 'Cirugía cancelada exitosamente.');
    }

    /**
     * Vista del check list aplicado con condicionales
     */
    public function viewChecklist(ScheduledSurgery $surgery)
    {
        $surgery->load([
            'checklist.items.product',
            'hospital',
            'doctor'
        ]);

        $checklistItems = $surgery->getChecklistItemsWithConditionals();

        return view('surgeries.checklist', compact('surgery', 'checklistItems'));
    }
}