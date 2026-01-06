<?php
// app/Http/Controllers/ScheduledSurgeryController.php

namespace App\Http\Controllers;

use App\Models\ScheduledSurgery;
use App\Models\SurgicalChecklist;
use App\Models\LegalEntity;
use App\Models\Hospital;
use App\Models\Doctor;
use App\Models\ProductUnit;
use App\Models\SurgicalKit;
use Illuminate\Http\Request;

class ScheduledSurgeryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Query base
        $query = ScheduledSurgery::with([
            'checklist',
            'hospital',
            'doctor',
            'preparation'
        ]);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                ->orWhere('patient_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('hospital_id')) {
            $query->where('hospital_id', $request->hospital_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('surgery_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('surgery_date', '<=', $request->date_to);
        }

        // Obtener cirugías paginadas
        $surgeries = $query->latest('surgery_date')->paginate(15);

        $scheduledCount = ScheduledSurgery::where('status', 'scheduled')->count();
        $inPreparationCount = ScheduledSurgery::where('status', 'in_preparation')->count();
        $readyCount = ScheduledSurgery::where('status', 'ready')->count();
        $inSurgeryCount = ScheduledSurgery::where('status', 'in_surgery')->count();

        // Datos para filtros
        $hospitals = \App\Models\LegalEntity::orderBy('name')->get();


        return view('surgeries.index', compact(
            'surgeries',
            'scheduledCount',
            'inPreparationCount',
            'readyCount',
            'inSurgeryCount',
            'hospitals'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $checklists = SurgicalChecklist::active()
            ->select('id', 'code', 'surgery_type')
            ->get();

        $hospitals = Hospital::orderBy('name')->get();

        $doctors = Doctor::orderBy('first_name')->get();

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
            'doctor',
            'scheduler',
            'preparation.preAssembledPackage',
            'preparation.items.product',
            'invoice'
        ]);

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

        $hospitals = LegalEntity::where( 'hospital')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $doctors = LegalEntity::where( 'doctor')
            ->select('id', 'name')
            ->orderBy('name')
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