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
        
        $query = ScheduledSurgery::with([
            'checklist',
            'hospital',
            'doctor',
            'preparation.items',
            'hospitalModalityConfig.hospital',
            'hospitalModalityConfig.modality',
            'hospitalModalityConfig.legalEntity',

        ]);

        // Filtro: Búsqueda general (código, paciente, tipo de cirugía)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('patient_name', 'like', "%{$search}%")
                  ->orWhereHas('checklist', function ($q2) use ($search) {
                      $q2->where('surgery_type', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro: Doctor
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filtro: Fecha desde
        if ($request->filled('date_from')) {
            $query->whereDate('surgery_datetime', '>=', $request->date_from);
        }

        // Filtro: Fecha hasta
        if ($request->filled('date_to')) {
            $query->whereDate('surgery_datetime', '<=', $request->date_to);
        }

        // Obtener cirugías paginadas (preservar filtros en paginación)
        $surgeries = $query->latest('surgery_datetime')->paginate(15)->withQueryString();

        // Contadores (una sola consulta agrupada)
        $statusCounts = ScheduledSurgery::selectRaw("status, COUNT(*) as total")
            ->whereIn('status', ['scheduled', 'in_preparation', 'ready', 'in_surgery'])
            ->groupBy('status')
            ->pluck('total', 'status');

        $scheduledCount     = $statusCounts['scheduled'] ?? 0;
        $inPreparationCount = $statusCounts['in_preparation'] ?? 0;
        $readyCount         = $statusCounts['ready'] ?? 0;
        $inSurgeryCount     = $statusCounts['in_surgery'] ?? 0;

        // Precalcular progreso para cirugías en preparación (fix N+1)
        $surgeries->getCollection()->transform(function ($surgery) {
            if ($surgery->status === 'in_preparation' && $surgery->preparation) {
                $items = $surgery->preparation->items;
                $totalRequired = $items->sum('quantity_required');
                $totalSatisfied = $items->sum(function ($item) {
                    return $item->quantity_in_package + $item->quantity_picked;
                });
                $surgery->preparation->cached_progress = $totalRequired > 0
                    ? round(($totalSatisfied / $totalRequired) * 100, 1)
                    : 0;
            }
            return $surgery;
        });

        // Doctor seleccionado para persistir en Tom Select al recargar
        $selectedDoctor = null;
        if ($request->filled('doctor_id')) {
            $selectedDoctor = \App\Models\Doctor::find($request->doctor_id);
        }

        return view('surgeries.index', compact(
            'surgeries',
            'scheduledCount',
            'inPreparationCount',
            'readyCount',
            'inSurgeryCount',
            'selectedDoctor'
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
            $validated = $request->validate([
                'checklist_id' => 'required|exists:surgical_checklists,id',
                'patient_name' => 'required|string|max:255',
                'hospital_modality_config_id' => 'required|exists:hospital_modality_configs,id',
                'doctor_id' => 'required|exists:doctors,id',
                'surgery_date' => 'required|date|after_or_equal:today',
                'surgery_time' => 'required',
                'surgery_notes' => 'nullable|string',
                'additional_items' => 'nullable|array',
                'additional_items.*.id' => 'required_with:additional_items', 
                'additional_items.*.type' => 'required_with:additional_items|in:product,instrument,kit',
                'additional_items.*.quantity' => 'required_with:additional_items|integer|min:1',
                'additional_items.*.reason' => 'nullable|string|max:500',
            ]);

            // Usamos una transacción para asegurar integridad total
            return \DB::transaction(function () use ($validated, $request) {
                
                // PASO 2: Preparar fecha y configuración
                $surgeryDatetime = $validated['surgery_date'] . ' ' . $validated['surgery_time'];
                $config = \App\Models\HospitalModalityConfig::findOrFail($validated['hospital_modality_config_id']);

                // PASO 3: Crear el registro principal de la Cirugía
                $surgery = ScheduledSurgery::create([
                    'code' => ScheduledSurgery::generateCode(),
                    'checklist_id' => $validated['checklist_id'],
                    'patient_name' => $validated['patient_name'],
                    'hospital_modality_config_id' => $config->id,
                    'hospital_id' => $config->hospital_id,
                    'doctor_id' => $validated['doctor_id'],
                    'surgery_datetime' => $surgeryDatetime,
                    'surgery_notes' => $validated['surgery_notes'] ?? null,
                    'status' => 'scheduled',
                    'scheduled_by' => auth()->id(),
                ]);

                // PASO 4: Guardar ítems adicionales y actualizar estatus
                if (!empty($validated['additional_items'])) {
                    foreach ($validated['additional_items'] as $item) {
                        
                        // Extraemos el ID numérico real
                        $parts = explode('_', $item['id']);
                        $realId = end($parts);
                        $type = $item['type'];

                        // Creamos el adicional en la tabla polivalente
                        $surgery->additionalItems()->create([
                            'product_id'        => $type === 'product' ? $realId : null,
                            'instrument_id'     => $type === 'instrument' ? $realId : null,
                            'instrument_kit_id' => $type === 'kit' ? $realId : null,
                            'quantity'          => $item['quantity'],
                            'reason'            => $item['reason'] ?? null,
                        ]);

                        // 🔥 NUEVA LÓGICA: Reservar el Kit o Instrumento en la base de datos
                        if ($type === 'kit') {
                            \App\Models\InstrumentKit::where('id', $realId)->update(['status' => 'in_surgery']);
                        } elseif ($type === 'instrument') {
                            \App\Models\Instrument::where('id', $realId)->update(['status' => 'in_surgery']);
                        }
                        // Nota: Los Insumos ('product') normalmente descuentan stock en vez de cambiar estatus, 
                        // eso puedes manejarlo aquí mismo o después cuando la cirugía realmente inicie.
                        
                        \Log::info("[SURGERY] Item adicional guardado y reservado", [
                            'surgery_id' => $surgery->id,
                            'type' => $type,
                            'real_id' => $realId
                        ]);
                    }
                }

                \Log::info('[SURGERY] ✅ Proceso completado con éxito', ['surgery_id' => $surgery->id]);

                return redirect()
                    ->route('surgeries.show', $surgery)
                    ->with('success', 'Cirugía agendada exitosamente.');
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('[SURGERY] ❌ Error de validación', ['errors' => $e->errors()]);
            throw $e; 
        } catch (\Exception $e) {
            \Log::error('[SURGERY] ❌ Error crítico al crear cirugía', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Hubo un problema al agendar la cirugía: ' . $e->getMessage());
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
            'additionalItems',
            'additionalItems.product',
            'additionalItems.instrument',
            'additionalItems.instrumentKit.instruments',
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

        // Stock de todos los productos en una sola consulta (fix N+1)
        $productIds = $checklistItems->pluck('product_id')->unique();
        $stockMap = \App\Models\ProductUnit::whereIn('product_id', $productIds)
            ->where('status', 'available')
            ->groupBy('product_id')
            ->selectRaw('product_id, COUNT(*) as total')
            ->pluck('total', 'product_id');

        return view('surgeries.show', compact('surgery', 'checklistItems', 'excludedItems', 'stockMap'));
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