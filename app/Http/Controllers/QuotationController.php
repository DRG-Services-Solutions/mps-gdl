<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Hospital;
use App\Models\Doctor;
use App\Models\LegalEntity;
use App\Models\ProductUnit;
use App\Models\SubWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    /**
     * Display a listing of quotations.
     */
    public function index(Request $request)
    {
        $query = Quotation::with(['hospital', 'doctor', 'billingLegalEntity']);

        // Búsqueda por número
        if ($request->filled('search')) {
            $query->where('quotation_number', 'like', "%{$request->search}%");
        }

        

        // Filtro por hospital
        if ($request->filled('hospital_id')) {
            $query->byHospital($request->hospital_id);
        }

        // Filtro por razón social
        if ($request->filled('legal_entity_id')) {
            $query->byLegalEntity($request->legal_entity_id);
        }

        // Filtro por rango de fechas
        if ($request->filled('date_from')) {
            $query->where('surgery_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('surgery_date', '<=', $request->date_to);
        }

        $quotations = $query->latest()->paginate(20);

        // Datos para filtros
        $hospitals = Hospital::orderBy('name')->get();
        $legalEntities = LegalEntity::orderBy('name')->get();

        return view('quotations.index', compact('quotations', 'hospitals', 'legalEntities'));
    }

    /**
     * Show the form for creating a new quotation.
     */
    public function create()
    {
        $hospitals = Hospital::orderBy('name')->get();
        $doctors = Doctor::active()->orderBy('last_name')->get();
        $legalEntities = LegalEntity::orderBy('name')->get();

        return view('quotations.create', compact('hospitals', 'doctors', 'legalEntities'));
    }

    /**
     * Store a newly created quotation in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'surgery_type' => 'nullable|string|max:255',
            'surgery_date' => 'nullable|date',
            'billing_legal_entity_id' => 'required|exists:legal_entities,id',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        $quotation = Quotation::create($validated);

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', 'Cotización creada exitosamente. Ahora agrega productos.');
    }

    /**
     * Display the specified quotation.
     */
    public function show(Quotation $quotation)
    {
        $quotation->load([
            'hospital',
            'doctor',
            'billingLegalEntity',
            'items.productUnit.product',
            'items.sourceLegalEntity',
            'items.sourceSubWarehouse',
            'createdBy',
        ]);

        $stats = [
            'total_items' => $quotation->getTotalItems(),
            'sent_items' => $quotation->getSentItems(),
            'returned_items' => $quotation->getReturnedItems(),
            'missing_items' => $quotation->getMissingItems(),
        ];

        // Productos disponibles para agregar
        $availableProducts = ProductUnit::where('status', 'available')
            ->with(['product', 'legalEntity', 'subWarehouse'])
            ->get();

        return view('quotations.show', compact('quotation', 'stats', 'availableProducts'));
    }

    /**
     * Show the form for editing the specified quotation.
     */
    public function edit(Quotation $quotation)
    {
        // Solo editar si está en borrador
        if ($quotation->status !== 'draft') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Solo se pueden editar cotizaciones en borrador.');
        }

        $hospitals = Hospital::active()->orderBy('name')->get();
        $doctors = Doctor::active()->orderBy('first_name')->get();
        $legalEntities = LegalEntity::where('is_active', true)->orderBy('business_name')->get();

        return view('quotations.edit', compact('quotation', 'hospitals', 'doctors', 'legalEntities'));
    }

    /**
     * Update the specified quotation in storage.
     */
    public function update(Request $request, Quotation $quotation)
    {
        // Solo editar si está en borrador
        if ($quotation->status !== 'draft') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Solo se pueden editar cotizaciones en borrador.');
        }

        $validated = $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'surgery_type' => 'nullable|string|max:255',
            'surgery_date' => 'nullable|date',
            'billing_legal_entity_id' => 'required|exists:legal_entities,id',
            'notes' => 'nullable|string',
        ]);

        $quotation->update($validated);

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', 'Cotización actualizada exitosamente.');
    }

    /**
     * Remove the specified quotation from storage.
     */
    public function destroy(Quotation $quotation)
    {
        // Solo eliminar si está en borrador
        if ($quotation->status !== 'draft') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Solo se pueden eliminar cotizaciones en borrador.');
        }

        // Eliminar items primero
        $quotation->items()->delete();
        $quotation->delete();

        return redirect()
            ->route('quotations.index')
            ->with('success', 'Cotización eliminada exitosamente.');
    }

    // ═══════════════════════════════════════════════════════════
    // AGREGAR / QUITAR PRODUCTOS
    // ═══════════════════════════════════════════════════════════

    /**
     * Add item to quotation.
     */
    public function addItem(Request $request, Quotation $quotation)
    {
        if ($quotation->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Solo se pueden agregar productos en borrador.');
        }

        $validated = $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'quantity' => 'required|integer|min:1', // ← Esta línea existe
            'billing_mode' => 'required|in:rental,consignment',
            'rental_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
        ]);

        $productUnit = ProductUnit::findOrFail($validated['product_unit_id']);

        $availableQuantity = $productUnit->quantity ?? 1;
        if ($validated['quantity'] > $availableQuantity) {
            return redirect()
                ->back()
                ->with('error', "Solo hay {$availableQuantity} unidad(es) disponible(s) de este producto.")
                ->withInput();
        }

        $exists = $quotation->items()->where('product_unit_id', $productUnit->id)->exists();


        // Verificar que no esté ya en la cotización
        $exists = $quotation->items()->where('product_unit_id', $productUnit->id)->exists();
        if ($exists) {
            return redirect()
                ->back()
                ->with('error', 'Este producto ya está en la cotización.');
        }

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_unit_id' => $productUnit->id,
            'product_id' => $productUnit->product_id,
            'quantity' => $validated['quantity'], // ← AGREGADO
            'source_legal_entity_id' => $productUnit->legal_entity_id,
            'source_sub_warehouse_id' => $productUnit->sub_warehouse_id,
            'billing_mode' => $validated['billing_mode'],
            'rental_price' => $validated['rental_price'] ?? 0,
            'sale_price' => $validated['sale_price'] ?? 0,
            'status' => 'pending',
        ]);

        return redirect()
            ->back()
            ->with('success', 'Producto agregado a la cotización.');
    }

    /**
     * Remove item from quotation.
     */
    public function removeItem(Quotation $quotation, QuotationItem $item)
    {
        // Solo quitar si está en borrador
        if ($quotation->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Solo se pueden quitar productos en borrador.');
        }

        // Verificar que el item pertenece a esta cotización
        if ($item->quotation_id !== $quotation->id) {
            abort(403);
        }

        $item->delete();

        return redirect()
            ->back()
            ->with('success', 'Producto eliminado de la cotización.');
    }

    // ═══════════════════════════════════════════════════════════
    // FLUJO DE CIRUGÍA
    // ═══════════════════════════════════════════════════════════

    /**
     * Send quotation to surgery.
     */
    public function sendToSurgery(Quotation $quotation)
    {
        if ($quotation->status !== 'draft' && $quotation->status !== 'sent') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Esta cotización ya fue enviada a cirugía.');
        }

        if ($quotation->items()->count() === 0) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Debes agregar al menos un producto antes de enviar a cirugía.');
        }

        try {
            $quotation->sendToSurgery();

            return redirect()
                ->route('quotations.show', $quotation)
                ->with('success', 'Material enviado a cirugía exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Error al enviar a cirugía: ' . $e->getMessage());
        }
    }

    /**
     * Show form to register return from surgery.
     */
    public function showReturnForm(Quotation $quotation)
    {
        if ($quotation->status !== 'in_surgery') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Solo se puede registrar retorno de cotizaciones en cirugía.');
        }

        $quotation->load(['items.productUnit.product', 'hospital']);

        return view('quotations.return', compact('quotation'));
    }

    /**
     * Register return from surgery.
     */
    public function registerReturn(Request $request, Quotation $quotation)
    {
        if ($quotation->status !== 'in_surgery') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Solo se puede registrar retorno de cotizaciones en cirugía.');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:quotation_items,id',
            'items.*.returned' => 'required|boolean',
        ]);

        try {
            DB::transaction(function () use ($validated, $quotation) {
                foreach ($validated['items'] as $itemData) {
                    $item = QuotationItem::findOrFail($itemData['id']);
                    
                    if ($itemData['returned']) {
                        $item->markAsReturned();
                    } else {
                        // No regresó, marcar como usado
                        $item->update([
                            'quantity_returned' => 0,
                            'status' => 'used',
                        ]);
                    }
                }

                $quotation->update(['status' => 'completed']);
            });

            return redirect()
                ->route('quotations.show', $quotation)
                ->with('success', 'Retorno registrado exitosamente. Ahora puedes generar las ventas.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al registrar retorno: ' . $e->getMessage());
        }
    }

    /**
     * Generate sales from quotation.
     */
    public function generateSales(Quotation $quotation)
    {
        if ($quotation->status !== 'completed') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Solo se pueden generar ventas de cotizaciones completadas.');
        }

        try {
            $salesCount = $quotation->generateSales();

            return redirect()
                ->route('quotations.show', $quotation)
                ->with('success', "{$salesCount} venta(s) generada(s) exitosamente.");
        } catch (\Exception $e) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Error al generar ventas: ' . $e->getMessage());
        }
    }
}