<?php

namespace App\Http\Controllers;

use App\Models\ShippingNote;
use App\Models\ShippingNotePackage;
use App\Models\ShippingNoteKit;
use App\Models\ShippingNoteItem;
use App\Models\ShippingNoteRentalConcept;
use App\Models\ScheduledSurgery;
use App\Models\PreAssembledPackage;
use App\Models\SurgicalKit;
use App\Models\Hospital;
use App\Models\Doctor;
use App\Models\LegalEntity;
use App\Models\ProductUnit;
use App\Services\ShippingNoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingNoteController extends Controller
{
    protected ShippingNoteService $service;

    public function __construct(ShippingNoteService $service)
    {
        $this->service = $service;
    }

    // ═══════════════════════════════════════════════════════════
    // CRUD PRINCIPAL
    // ═══════════════════════════════════════════════════════════

    /**
     * Listado de remisiones con filtros
     */
    public function index(Request $request)
    {
        $query = ShippingNote::with(['hospital', 'doctor', 'billingLegalEntity', 'scheduledSurgery']);

        // Búsqueda por número
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shipping_number', 'like', "%{$search}%")
                  ->orWhereHas('hospital', fn($h) => $h->where('name', 'like', "%{$search}%"));
            });
        }

        // Filtro por hospital
        if ($request->filled('hospital_id')) {
            $query->byHospital($request->hospital_id);
        }

        // Filtro por razón social
        if ($request->filled('legal_entity_id')) {
            $query->byLegalEntity($request->legal_entity_id);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por rango de fechas
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $shippingNotes = $query->latest()->paginate(20)->withQueryString();

        // Datos para filtros
        $hospitals = Hospital::orderBy('name')->get();
        $legalEntities = LegalEntity::orderBy('name')->get();
        $statusLabels = ShippingNote::getStatusLabels();

        return view('shipping-notes.index', compact(
            'shippingNotes',
            'hospitals',
            'legalEntities',
            'statusLabels'
        ));
    }

    /**
     * Formulario para crear remisión desde una cirugía programada
     */
    public function create(Request $request)
    {
        // Cirugías disponibles para generar remisión
        $surgeries = ScheduledSurgery::with(['checklist', 'doctor', 'hospital', 'hospitalModalityConfig'])
            ->whereIn('status', ['scheduled', 'in_preparation', 'ready'])
            ->whereDoesntHave('shippingNote', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            })
            ->orderBy('surgery_datetime', 'asc')
            ->get();

        $legalEntities = LegalEntity::where('is_active', true)->orderBy('name')->get();

        // Si viene con surgery_id preseleccionado
        $selectedSurgery = null;
        if ($request->filled('surgery_id')) {
            $selectedSurgery = ScheduledSurgery::with([
                'checklist.items.product',
                'doctor',
                'hospital',
                'hospitalModalityConfig',
            ])->find($request->surgery_id);
        }

        return view('shipping-notes.create', compact('surgeries', 'legalEntities', 'selectedSurgery'));
    }

    /**
     * Crear la remisión
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'scheduled_surgery_id' => 'required|exists:scheduled_surgeries,id',
            'billing_legal_entity_id' => 'required|exists:legal_entities,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        try {
            $surgery = ScheduledSurgery::findOrFail($validated['scheduled_surgery_id']);

            $shippingNote = $this->service->createFromSurgery(
                $surgery,
                $validated['billing_legal_entity_id'],
                $validated['notes'] ?? null
            );

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', "Remisión {$shippingNote->shipping_number} creada. Ahora asigne paquetes y kits.");

        } catch (\Exception $e) {
            Log::error("Error al crear remisión: " . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear la remisión: ' . $e->getMessage());
        }
    }

    /**
     * Vista detalle de la remisión
     */
    public function show(ShippingNote $shippingNote)
    {
        $preview = $this->service->getFullPreview($shippingNote);

        // Paquetes disponibles para asignar
        $availablePackages = collect();
        if ($shippingNote->isDraft()) {
            $availablePackages = PreAssembledPackage::where('status', 'available')
                ->where('surgery_checklist_id', $shippingNote->surgical_checklist_id)
                ->with('surgeryChecklist')
                ->get();
        }

        // Kits disponibles para asignar
        $availableKits = collect();
        if ($shippingNote->isDraft()) {
            $assignedKitIds = $shippingNote->kits()->pluck('surgical_kit_id');
            $availableKits = SurgicalKit::where('is_active', true)
                ->whereNotIn('id', $assignedKitIds)
                ->get();
        }

        // Productos disponibles para agregar individualmente
        $availableProducts = collect();
        if ($shippingNote->isDraft()) {
            $availableProducts = ProductUnit::where('status', 'available')
                ->with(['product.category', 'legalEntity', 'subWarehouse'])
                ->limit(100)
                ->get();
        }

        return view('shipping-notes.show', array_merge($preview, [
            'availablePackages' => $availablePackages,
            'availableKits' => $availableKits,
            'availableProducts' => $availableProducts,
        ]));
    }

    /**
     * Editar datos generales de la remisión (solo en draft)
     */
    public function edit(ShippingNote $shippingNote)
    {
        if (!$shippingNote->canBeEdited()) {
            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('error', 'Solo se pueden editar remisiones en borrador.');
        }

        $legalEntities = LegalEntity::where('is_active', true)->orderBy('name')->get();

        return view('shipping-notes.edit', compact('shippingNote', 'legalEntities'));
    }

    /**
     * Actualizar datos generales
     */
    public function update(Request $request, ShippingNote $shippingNote)
    {
        if (!$shippingNote->canBeEdited()) {
            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('error', 'Solo se pueden editar remisiones en borrador.');
        }

        $validated = $request->validate([
            'billing_legal_entity_id' => 'required|exists:legal_entities,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $shippingNote->update($validated);

        return redirect()
            ->route('shipping-notes.show', $shippingNote)
            ->with('success', 'Remisión actualizada.');
    }

    /**
     * Eliminar remisión (solo en draft)
     */
    public function destroy(ShippingNote $shippingNote)
    {
        if (!$shippingNote->canBeEdited()) {
            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('error', 'Solo se pueden eliminar remisiones en borrador.');
        }

        try {
            // Liberar paquetes asignados
            foreach ($shippingNote->packages as $notePackage) {
                $notePackage->preAssembledPackage->update(['status' => 'available']);
            }

            // Eliminar en cascada (items, packages, kits, concepts se eliminan por FK cascade)
            $shippingNote->delete();

            return redirect()
                ->route('shipping-notes.index')
                ->with('success', 'Remisión eliminada.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // PAQUETES PRE-ARMADOS
    // ═══════════════════════════════════════════════════════════

    /**
     * Asignar paquete pre-armado
     */
    public function assignPackage(Request $request, ShippingNote $shippingNote)
    {
        $validated = $request->validate([
            'pre_assembled_package_id' => 'required|exists:pre_assembled_packages,id',
        ]);

        try {
            $package = PreAssembledPackage::findOrFail($validated['pre_assembled_package_id']);

            $notePackage = $this->service->assignPackage($shippingNote, $package);

            $completeness = $notePackage->getCompletenessPercentage();
            $message = "Paquete {$package->code} asignado ({$completeness}% completo).";

            $missingItems = $notePackage->getMissingItems();
            if (!empty($missingItems)) {
                $missingNames = collect($missingItems)->pluck('product_name')->implode(', ');
                $message .= " Faltantes: {$missingNames}";
            }

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with($completeness >= 100 ? 'success' : 'warning', $message);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remover paquete de la remisión
     */
    public function removePackage(ShippingNote $shippingNote, ShippingNotePackage $package)
    {
        try {
            $this->service->removePackage($shippingNote, $package);

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', 'Paquete removido de la remisión.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // KITS QUIRÚRGICOS
    // ═══════════════════════════════════════════════════════════

    /**
     * Asignar kit quirúrgico
     */
    public function assignKit(Request $request, ShippingNote $shippingNote)
    {
        $validated = $request->validate([
            'surgical_kit_id' => 'required|exists:surgical_kits,id',
            'rental_price' => 'required|numeric|min:0',
            'exclude_from_invoice' => 'nullable|boolean',
        ]);

        try {
            $kit = SurgicalKit::findOrFail($validated['surgical_kit_id']);

            $this->service->assignKit(
                $shippingNote,
                $kit,
                (float) $validated['rental_price'],
                (bool) ($validated['exclude_from_invoice'] ?? false)
            );

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', "Kit {$kit->code} asignado a la remisión.");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remover kit de la remisión
     */
    public function removeKit(ShippingNote $shippingNote, ShippingNoteKit $kit)
    {
        try {
            $this->service->removeKit($shippingNote, $kit);

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', 'Kit removido de la remisión.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ITEMS INDIVIDUALES
    // ═══════════════════════════════════════════════════════════

    /**
     * Agregar producto individual
     */
    public function addItem(Request $request, ShippingNote $shippingNote)
    {
        $validated = $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'quantity' => 'required|integer|min:1',
            'billing_mode' => 'required|in:sale,rental,no_charge',
            'unit_price' => 'required|numeric|min:0',
            'exclude_from_invoice' => 'nullable|boolean',
        ]);

        try {
            $productUnit = ProductUnit::with('product')->findOrFail($validated['product_unit_id']);

            $this->service->addStandaloneItem(
                $shippingNote,
                $productUnit->product_id,
                $validated['quantity'],
                $validated['billing_mode'],
                (float) $validated['unit_price'],
                $productUnit->id,
                (bool) ($validated['exclude_from_invoice'] ?? false)
            );

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', "Producto {$productUnit->product->name} agregado.");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Actualizar item (cantidad, precio, billing_mode)
     */
    public function updateItem(Request $request, ShippingNote $shippingNote, ShippingNoteItem $item)
    {
        $validated = $request->validate([
            'quantity_required' => 'nullable|integer|min:1',
            'billing_mode' => 'nullable|in:sale,rental,no_charge',
            'unit_price' => 'nullable|numeric|min:0',
            'exclude_from_invoice' => 'nullable|boolean',
        ]);

        try {
            $this->service->updateItem($shippingNote, $item, $validated);

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', 'Item actualizado.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remover item individual
     */
    public function removeItem(ShippingNote $shippingNote, ShippingNoteItem $item)
    {
        try {
            $this->service->removeItem($shippingNote, $item);

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', 'Producto removido.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // CONCEPTOS DE RENTA
    // ═══════════════════════════════════════════════════════════

    /**
     * Agregar concepto de renta
     */
    public function addRentalConcept(Request $request, ShippingNote $shippingNote)
    {
        $validated = $request->validate([
            'concept' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'exclude_from_invoice' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->service->addRentalConcept(
                $shippingNote,
                $validated['concept'],
                $validated['quantity'],
                (float) $validated['unit_price'],
                (bool) ($validated['exclude_from_invoice'] ?? false),
                $validated['notes'] ?? null
            );

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', 'Concepto de renta agregado.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remover concepto de renta
     */
    public function removeRentalConcept(ShippingNote $shippingNote, ShippingNoteRentalConcept $concept)
    {
        try {
            $this->service->removeRentalConcept($shippingNote, $concept);

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', 'Concepto de renta removido.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // FLUJO DE ESTADOS
    // ═══════════════════════════════════════════════════════════

    /**
     * Confirmar remisión (draft → confirmed)
     */
    public function confirm(ShippingNote $shippingNote)
    {
        try {
            $shippingNote->confirm();

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', "Remisión {$shippingNote->shipping_number} confirmada.");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Enviar material (confirmed → sent)
     */
    public function send(ShippingNote $shippingNote)
    {
        try {
            $shippingNote->markAsSent();

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', 'Material enviado a cirugía.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar: ' . $e->getMessage());
        }
    }

    /**
     * Marcar en cirugía (sent → in_surgery)
     */
    public function startSurgery(ShippingNote $shippingNote)
    {
        try {
            $shippingNote->markInSurgery();

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', 'Material marcado en cirugía.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Formulario de retorno
     */
    public function showReturnForm(ShippingNote $shippingNote)
    {
        if (!$shippingNote->canRegisterReturn()) {
            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('error', 'No se puede registrar retorno en el estado actual.');
        }

        $shippingNote->load([
            'items.product',
            'items.productUnit',
            'items.shippingNotePackage.preAssembledPackage',
            'items.shippingNoteKit.surgicalKit',
            'packages.preAssembledPackage',
            'kits.surgicalKit',
            'hospital',
            'doctor',
        ]);

        return view('shipping-notes.return', compact('shippingNote'));
    }

    /**
     * Registrar retorno de cirugía
     */
    public function registerReturn(Request $request, ShippingNote $shippingNote)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:shipping_note_items,id',
            'items.*.returned' => 'required|boolean',
            'items.*.quantity_returned' => 'nullable|integer|min:0',
        ]);

        try {
            $summary = $this->service->registerReturn($shippingNote, $validated['items']);

            $message = "Retorno registrado: {$summary['returned']} retornados, {$summary['used']} usados.";

            if (!empty($summary['errors'])) {
                return redirect()
                    ->route('shipping-notes.show', $shippingNote)
                    ->with('warning', $message)
                    ->with('return_errors', $summary['errors']);
            }

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al registrar retorno: ' . $e->getMessage());
        }
    }

    /**
     * Completar remisión (returned → completed)
     */
    public function complete(ShippingNote $shippingNote)
    {
        try {
            $this->service->complete($shippingNote);

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', "Remisión {$shippingNote->shipping_number} completada.");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancelar remisión
     */
    public function cancel(Request $request, ShippingNote $shippingNote)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        try {
            $shippingNote->cancel($validated['cancellation_reason'] ?? null);

            return redirect()
                ->route('shipping-notes.index')
                ->with('success', "Remisión {$shippingNote->shipping_number} cancelada.");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // RE-EVALUAR CHECKLIST
    // ═══════════════════════════════════════════════════════════

    /**
     * Re-evaluar checklist (si cambiaron datos de la cirugía)
     */
    public function reevaluateChecklist(ShippingNote $shippingNote)
    {
        try {
            $this->service->reevaluateChecklist($shippingNote);

            return redirect()
                ->route('shipping-notes.show', $shippingNote)
                ->with('success', 'Checklist re-evaluado con los datos actuales de la cirugía.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // API ENDPOINTS (para Alpine.js / búsquedas async)
    // ═══════════════════════════════════════════════════════════

    /**
     * Buscar productos disponibles (para agregar items standalone)
     */
    public function searchAvailableProducts(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = ProductUnit::where('status', 'available')
            ->whereHas('product', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%");
            })
            ->orWhere('epc', 'like', "%{$query}%")
            ->with(['product.category', 'legalEntity', 'subWarehouse'])
            ->limit(20)
            ->get()
            ->map(fn($unit) => [
                'id' => $unit->id,
                'product_id' => $unit->product_id,
                'name' => $unit->product->name,
                'code' => $unit->product->code,
                'epc' => $unit->epc,
                'category' => $unit->product->category->name ?? 'N/A',
                'legal_entity' => $unit->legalEntity->name ?? 'N/A',
                'sub_warehouse' => $unit->subWarehouse->name ?? 'N/A',
                'list_price' => $unit->product->list_price ?? 0,
            ]);

        return response()->json($products);
    }

    /**
     * Preview de evaluación del checklist para una cirugía (AJAX)
     */
    public function previewChecklist(Request $request)
    {
        $validated = $request->validate([
            'surgery_id' => 'required|exists:scheduled_surgeries,id',
        ]);

        $surgery = ScheduledSurgery::with(['checklist.items.product', 'doctor', 'hospital'])
            ->findOrFail($validated['surgery_id']);

        $evaluatedItems = $surgery->getChecklistItemsWithConditionals();

        return response()->json([
            'surgery' => [
                'code' => $surgery->code,
                'hospital' => $surgery->hospital->name ?? 'N/A',
                'doctor' => $surgery->doctor->full_name ?? 'N/A',
                'checklist' => $surgery->checklist->surgery_type ?? 'N/A',
                'date' => $surgery->surgery_datetime->format('d/m/Y H:i'),
            ],
            'items' => $evaluatedItems->map(fn($item) => [
                'product_name' => $item['product_name'],
                'base_quantity' => $item['base_quantity'],
                'adjusted_quantity' => $item['adjusted_quantity'],
                'has_conditional' => $item['has_conditional'],
                'conditional_description' => $item['conditional_description'],
                'is_mandatory' => $item['is_mandatory'],
                'source' => $item['source'],
            ]),
            'summary' => $surgery->getPreparationSummary(),
        ]);
    }
}