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
            'preparation.items',
            'hospitalModalityConfig.hospital',     
            'hospitalModalityConfig.modality',      
            'hospitalModalityConfig.legalEntity',   
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
            $query->whereDate('surgery_datetime', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('surgery_datetime', '<=', $request->date_to);
        }

        // Obtener cirugías paginadas
        $surgeries = $query->latest('surgery_datetime')->paginate(15);
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
        return view('surgeries.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
 * Store a newly created resource in storage.
 */
    public function store(Request $request)
    {
        \Log::info('[SURGERY] ===== INICIO CREAR CIRUGÍA =====', [
            'request_all' => $request->all(),
            'user_id' => auth()->id(),
        ]);

        try {
            // PASO 1: Validación
            \Log::info('[SURGERY] Iniciando validación...');
            
            $validated = $request->validate([
                'checklist_id' => 'required|exists:surgical_checklists,id',
                'patient_name' => 'required|string|max:255',
                'hospital_modality_config_id' => 'required|exists:hospital_modality_configs,id',
                'doctor_id' => 'required|exists:doctors,id',
                'surgery_date' => 'required|date|after_or_equal:today',
                'surgery_time' => 'required',
                'surgery_notes' => 'nullable|string',
            ]);

            \Log::info('[SURGERY] ✅ Validación exitosa', [
                'validated_data' => $validated,
            ]);

            // PASO 2: Combinar fecha y hora
            $surgeryDatetime = $validated['surgery_date'] . ' ' . $validated['surgery_time'];
            
            \Log::info('[SURGERY] Fecha y hora combinadas', [
                'surgery_datetime' => $surgeryDatetime,
            ]);

            // PASO 3: Generar código
            $code = ScheduledSurgery::generateCode();
            
            \Log::info('[SURGERY] Código generado', [
                'code' => $code,
            ]);

            // PASO 4: Preparar datos
            $surgeryData = [
                'code' => $code,
                'checklist_id' => $validated['checklist_id'], 
                'patient_name' => $validated['patient_name'],
                'hospital_modality_config_id' => $validated['hospital_modality_config_id'],
                'doctor_id' => $validated['doctor_id'],
                'surgery_datetime' => $surgeryDatetime,
                'surgery_notes' => $validated['surgery_notes'] ?? null,
                'status' => 'scheduled',
                'scheduled_by' => auth()->id(),
                
            ];

            \Log::info('[SURGERY] Datos preparados para crear', [
                'surgery_data' => $surgeryData,
            ]);
            $config = \App\Models\HospitalModalityConfig::find($validated['hospital_modality_config_id']);
            if ($config) {
                $surgeryData['hospital_id'] = $config->hospital_id;
            }


            $surgery = ScheduledSurgery::create($surgeryData);

            \Log::info('[SURGERY] ✅ Cirugía creada exitosamente', [
                'surgery_id' => $surgery->id,
                'surgery_code' => $surgery->code,
                'config_id' => $surgery->hospital_modality_config_id,
            ]);

            \Log::info('[SURGERY] ===== FIN CREAR CIRUGÍA - ÉXITO =====');

            return redirect()
                ->route('surgeries.show', $surgery)
                ->with('success', 'Cirugía agendada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('[SURGERY] ❌ Error de validación', [
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ]);

            throw $e;

        } catch (\Exception $e) {
            \Log::error('[SURGERY] ❌ Error inesperado al crear cirugía', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al crear la cirugía: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ScheduledSurgery $surgery)
    {
        $surgery->load([
            'checklist.items.product.productUnits',
            'checklist.items.conditionals' => fn($q) => $q->with(['doctor', 'hospital', 'modality', 'legalEntity', 'targetProduct']),
            'doctor',
            'scheduler',
            'preparation.preAssembledPackage',
            'preparation.items.product',
            'invoice',
            'hospital',
            'hospitalModalityConfig.modality',
        ]);

        // Items evaluados (qty > 0) — deduplicados por product_id
        $rawItems = $surgery->getChecklistItemsWithConditionals();
        $checklistItems = $rawItems->unique('product_id')->values();

        // Items excluidos/reemplazados (qty = 0 por condicional)
        $excludedItems = collect();
        if ($surgery->checklist_id) {
            $baseItems = \App\Models\ChecklistItem::where('checklist_id', $surgery->checklist_id)
                ->with(['product.productUnits', 'conditionals' => fn($q) => $q->with(['doctor', 'hospital', 'modality', 'targetProduct'])])
                ->ordered()
                ->get();

            foreach ($baseItems as $item) {
                $adjustedData = $item->getAdjustedQuantity($surgery);
                if ($adjustedData['final_quantity'] === 0 && $adjustedData['has_conditional']) {
                    $excludedItems->push([
                        'item' => $item,
                        'base_quantity' => $adjustedData['base_quantity'],
                        'conditional' => $adjustedData['conditional'],
                        'conditional_description' => $adjustedData['conditional_description'],
                    ]);
                }
            }
        }

        return view('surgeries.show', compact('surgery', 'checklistItems', 'excludedItems'));
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
            ->select('id', 'code', 'surgery_type')
            ->get();

        $hospitals = Hospital::orderBy('name')
            ->get();

        $doctors = Doctor::orderBy('first_name')
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

        \Log::info('[SURGERY] ===== EDITAR CIRUGÍA =====', [
            'surgery_id' => $surgery->id,
            'request_data' => $request->all(),
        ]);

        $validated = $request->validate([
            'checklist_id' => 'required|exists:surgical_checklists,id',
            'patient_name' => 'required|string|max:255',
            'hospital_modality_config_id' => 'required|exists:hospital_modality_configs,id',
            'doctor_id' => 'required|exists:doctors,id',
            'surgery_date' => 'required|date|after_or_equal:today',
            'surgery_time' => 'required',
            'surgery_notes' => 'nullable|string',
        ]);

        // Combinar fecha y hora
        $surgeryDatetime = $validated['surgery_date'] . ' ' . $validated['surgery_time'];

        // Actualizar cirugía
        $surgery->update([
            'surgery_checklist_id' => $validated['checklist_id'],
            'patient_name' => $validated['patient_name'],
            'hospital_modality_config_id' => $validated['hospital_modality_config_id'],
            'doctor_id' => $validated['doctor_id'],
            'surgery_datetime' => $surgeryDatetime,
            'surgery_notes' => $validated['surgery_notes'],
            
        ]);

        \Log::info('[SURGERY] ✅ Cirugía actualizada', [
            'surgery_id' => $surgery->id,
        ]);

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