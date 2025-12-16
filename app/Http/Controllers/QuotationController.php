<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Hospital;
use App\Models\Doctor;
use App\Models\LegalEntity;
use App\Models\ProductUnit;
use App\Models\SubWarehouse;
use App\Models\SurgicalKit;
use App\Models\SurgicalKitItem;

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
        $surgicalKits = SurgicalKit::where('is_active', true)->orderBy('surgery_type')->get();

        return view('quotations.create', compact('hospitals', 'doctors', 'legalEntities', 'surgicalKits'));
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
            'surgical_kit_id' => 'nullable|exists:surgical_kits,id', 
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
            'items.productUnit.product.category', // ← Cargamos la categoría
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
            ->with(['product.category', 'legalEntity', 'subWarehouse'])
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
        $surgicalKits = SurgicalKit::where('is_active', true)->orderBy('surgery_type')->get();

        return view('quotations.edit', compact('quotation', 'hospitals', 'doctors', 'legalEntities', 'surgicalKits'));
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
            'surgical_kit_id' => 'nullable|exists:surgical_kits,id',
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
     * NO VALIDAMOS BILLING_MODE AQUÍ - Se valida al retorno según categoría
     */
    public function addItem(Request $request, Quotation $quotation)
    {
        if ($quotation->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Solo se pueden agregar productos en borrador.');
        }

        // VALIDACIÓN SIMPLIFICADA - Aceptamos cualquier billing_mode
        $validated = $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
            'quantity' => 'required|integer|min:1', 
            'billing_mode' => 'required|in:rental,sale',
            'rental_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
        ]);

        $productUnit = ProductUnit::with('product.category')->findOrFail($validated['product_unit_id']);

        // Verificar que no esté ya en la cotización
        $exists = $quotation->items()->where('product_unit_id', $productUnit->id)->exists();
        if ($exists) {
            return redirect()
                ->back()
                ->with('error', 'Este producto ya está en la cotización.');
        }

        // CREAR ITEM SIN VALIDAR TIPO DE PRODUCTO
        // La validación se hará al momento del retorno
        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_unit_id' => $productUnit->id,
            'product_id' => $productUnit->product_id,
            'quantity' => $validated['quantity'],
            'source_legal_entity_id' => $productUnit->legal_entity_id,
            'source_sub_warehouse_id' => $productUnit->sub_warehouse_id,
            'billing_mode' => $validated['billing_mode'],
            'rental_price' => $validated['rental_price'] ?? 0,
            'sale_price' => $validated['sale_price'] ?? 0,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('quotations.show', $quotation)
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

        $quotation->load(['items.productUnit.product.category', 'hospital']);

        return view('quotations.return', compact('quotation'));
    }

    /**
     * Register return from surgery.
     * ⭐ AQUÍ SE VALIDA EL BILLING_MODE SEGÚN LA CATEGORÍA DEL PRODUCTO
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
                $errors = [];
                
                foreach ($validated['items'] as $itemData) {
                    $item = QuotationItem::with('productUnit.product.category')->findOrFail($itemData['id']);
                    $product = $item->productUnit->product;
                    $categoryName = $product->category->name ?? '';
                    
                    // ═══════════════════════════════════════════════════════════
                    // VALIDACIÓN ESTRICTA SEGÚN CATEGORÍA
                    // ═══════════════════════════════════════════════════════════
                    
                    if ($itemData['returned']) {
                        // ✅ SÍ REGRESÓ
                        
                        if ($categoryName === 'Consumibles Quirúrgicos') {
                            // Consumibles DEBEN ser venta (error si es rental)
                            if ($item->billing_mode !== 'sale') {
                                $errors[] = "❌ El producto '{$product->name}' es CONSUMIBLE y debe ser VENTA, pero está marcado como RENTA.";
                            }
                        } elseif ($categoryName === 'Instrumental Quirúrgico') {
                            // Instrumental DEBE ser renta (error si es sale)
                            if ($item->billing_mode !== 'rental') {
                                $errors[] = "❌ El producto '{$product->name}' es INSTRUMENTAL y debe ser RENTA, pero está marcado como VENTA.";
                            }
                        }
                        
                        $item->markAsReturned();
                        
                    } else {
                        // ❌ NO REGRESÓ (se quedó en el paciente)
                        // Independientemente del tipo, DEBE ser venta
                        
                        if ($item->billing_mode !== 'sale') {
                            $errors[] = "❌ El producto '{$product->name}' NO REGRESÓ, por lo tanto debe ser VENTA, pero está marcado como RENTA.";
                        }
                        
                        $item->update([
                            'quantity_returned' => 0,
                            'status' => 'used',
                        ]);
                    }
                }
                
                // Si hay errores, lanzar excepción para hacer rollback
                if (!empty($errors)) {
                    throw new \Exception("Errores de validación:\n\n" . implode("\n", $errors));
                }

                $quotation->update(['status' => 'completed']);
            });

            return redirect()
                ->route('quotations.show', $quotation)
                ->with('success', 'Retorno registrado exitosamente. Ahora puedes generar las ventas.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
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

    public function applySurgicalKit(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'surgical_kit_id' => 'required|exists:surgical_kits,id',
        ]);
        
        if ($quotation->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Solo se pueden aplicar prearmados a cotizaciones en borrador.');
        }
        
        $surgicalKit = SurgicalKit::findOrFail($validated['surgical_kit_id']);
        $availability = $surgicalKit->checkAvailability();
        
        if (!$availability['all_available']) {
            return redirect()
                ->back()
                ->with('warning', 'Advertencia: Algunos productos del prearmado no tienen stock completo.')
                ->with('missing_products', $surgicalKit->getMissingProducts());
        }
        
        try {
            DB::transaction(function () use ($surgicalKit, $quotation) {
                // Actualizar el surgical_kit_id
                $quotation->update([
                    'surgical_kit_id' => $surgicalKit->id,
                    'surgery_type' => $surgicalKit->surgery_type,
                ]);
                
                // Aplicar productos del prearmado
                $productUnits = $surgicalKit->getAvailableProductUnits();
                
                foreach ($productUnits as $productData) {
                    $requiredQty = $productData['required_quantity'];
                    
                    foreach ($productData['units'] as $unit) {
                        if ($requiredQty <= 0) break;
                        
                        // Verificar que no exista ya
                        $exists = $quotation->items()
                            ->where('product_unit_id', $unit->id)
                            ->exists();
                        
                        if (!$exists) {
                            QuotationItem::create([
                                'quotation_id' => $quotation->id,
                                'product_unit_id' => $unit->id,
                                'product_id' => $unit->product_id,
                                'quantity' => 1,
                                'source_legal_entity_id' => $unit->legal_entity_id,
                                'source_sub_warehouse_id' => $unit->sub_warehouse_id,
                                'billing_mode' => 'rental', // Por defecto renta, se ajusta al retorno
                                'rental_price' => 0,
                                'sale_price' => 0,
                                'status' => 'pending',
                            ]);
                            
                            $requiredQty -= 1;
                        }
                    }
                }
            });
            
            return redirect()
                ->route('quotations.edit', $quotation)
                ->with('success', "Prearmado '{$surgicalKit->name}' aplicado exitosamente.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al aplicar prearmado: ' . $e->getMessage());
        }
    }
}