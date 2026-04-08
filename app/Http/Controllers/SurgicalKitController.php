<?php

namespace App\Http\Controllers;

use App\Models\SurgicalKit;
use App\Models\SurgicalKitItem;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurgicalKitController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    // CRUD BÁSICO
    // ═══════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = SurgicalKit::with(['items', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('surgery_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('surgery_type')) {
            $query->where('surgery_type', $request->surgery_type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }

        $kits = $query->latest()->paginate(15);

        $surgeryTypes = SurgicalKit::select('surgery_type')
            ->distinct()
            ->orderBy('surgery_type')
            ->pluck('surgery_type');

        return view('surgical-kits.index', compact('kits', 'surgeryTypes'));
    }

    public function create()
    {
        $products = Product::where('status', 'active')
            ->whereHas('productUnits')
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                // ✅ CONTAR unidades disponibles (cada ProductUnit = 1 unidad física)
                $totalStock = ProductUnit::where('product_id', $product->id)
                    ->where('status', 'available')
                    ->count(); // ✅ COUNT, no SUM
                
                $product->available_stock = $totalStock;
                $product->code = $product->code ?? 'N/A';
                $product->name = $product->name ?? 'Sin nombre';
                return $product;
            });

        return view('surgical-kits.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surgery_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id|distinct',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.notes' => 'nullable|string',
        ], [
            'products.*.product_id.distinct' => 'No puedes agregar el mismo producto dos veces.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $kit = SurgicalKit::create([
                    'name' => $validated['name'],
                    'surgery_type' => $validated['surgery_type'],
                    'description' => $validated['description'] ?? null,
                    'is_active' => $validated['is_active'] ?? true,
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['products'] as $productData) {
                    SurgicalKitItem::create([
                        'surgical_kit_id' => $kit->id,
                        'product_id' => $productData['product_id'],
                        'quantity' => $productData['quantity'],
                        'notes' => $productData['notes'] ?? null,
                    ]);
                }
            });

            return redirect()
                ->route('surgical-kits.index')
                ->with('success', 'Prearmado creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al crear el prearmado: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(SurgicalKit $surgicalKit)
    {
        $surgicalKit->load(['items.product', 'creator']);
        $availability = $surgicalKit->checkAvailability();

        return view('surgical-kits.show', compact('surgicalKit', 'availability'));
    }

    public function edit(SurgicalKit $surgicalKit)
    {
        $surgicalKit->load('items.product');
        
        // ✅ SOLO productos con inventario
        $products = Product::where('status', 'active')
            ->whereHas('productUnits')
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                // ✅ CONTAR unidades disponibles
                $totalStock = ProductUnit::where('product_id', $product->id)
                    ->where('status', 'available')
                    ->count(); // ✅ COUNT, no SUM
                
                $product->available_stock = $totalStock;
                $product->code = $product->code ?? 'N/A';
                $product->name = $product->name ?? 'Sin nombre';
                return $product;
            });

        return view('surgical-kits.edit', compact('surgicalKit', 'products'));
    }

    public function update(Request $request, SurgicalKit $surgicalKit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surgery_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id|distinct',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.notes' => 'nullable|string',
        ], [
            'products.*.product_id.distinct' => 'No puedes agregar el mismo producto dos veces.',
        ]);

        try {
            DB::transaction(function () use ($validated, $surgicalKit) {
                $surgicalKit->update([
                    'name' => $validated['name'],
                    'surgery_type' => $validated['surgery_type'],
                    'description' => $validated['description'] ?? null,
                    'is_active' => $validated['is_active'] ?? true,
                ]);

                $surgicalKit->items()->delete();

                foreach ($validated['products'] as $productData) {
                    SurgicalKitItem::create([
                        'surgical_kit_id' => $surgicalKit->id,
                        'product_id' => $productData['product_id'],
                        'quantity' => $productData['quantity'],
                        'notes' => $productData['notes'] ?? null,
                    ]);
                }
            });

            return redirect()
                ->route('surgical-kits.show', $surgicalKit)
                ->with('success', 'Prearmado actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al actualizar el prearmado: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(SurgicalKit $surgicalKit)
    {
        try {
            $surgicalKit->delete();

            return redirect()
                ->route('surgical-kits.index')
                ->with('success', 'Prearmado eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar el prearmado: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // VALIDACIÓN DE STOCK
    // ═══════════════════════════════════════════════════════════

    public function checkStock(SurgicalKit $surgicalKit)
    {
        $surgicalKit->load('items.product');
        $availability = $surgicalKit->checkAvailability();

        return view('surgical-kits.check-stock', compact('surgicalKit', 'availability'));
    }

    // ═══════════════════════════════════════════════════════════
    // APLICACIÓN A COTIZACIONES
    // ═══════════════════════════════════════════════════════════

    public function selectQuotation(SurgicalKit $surgicalKit)
    {
        $surgicalKit->load('items.product');
        
        $quotations = Quotation::where('status', 'draft')
            ->with(['hospital', 'doctor'])
            ->latest()
            ->get();

        $availability = $surgicalKit->checkAvailability();

        return view('surgical-kits.select-quotation', compact('surgicalKit', 'quotations', 'availability'));
    }

    public function applyToQuotation(Request $request, SurgicalKit $surgicalKit)
    {
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'force' => 'boolean',
        ]);

        $quotation = Quotation::findOrFail($validated['quotation_id']);

        if ($quotation->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Solo se pueden aplicar prearmados a cotizaciones en borrador.');
        }

        $availability = $surgicalKit->checkAvailability();

        if (!$availability['all_available'] && !($validated['force'] ?? false)) {
            return redirect()
                ->route('surgical-kits.check-stock', $surgicalKit)
                ->with('error', 'No hay stock suficiente. Revisa los productos faltantes.')
                ->with('quotation_id', $validated['quotation_id']);
        }

        try {
            DB::transaction(function () use ($surgicalKit, $quotation) {
                $productUnits = $surgicalKit->getAvailableProductUnits();

                foreach ($productUnits as $productData) {
                    $requiredQty = $productData['required_quantity'];

                    foreach ($productData['units'] as $unit) {
                        if ($requiredQty <= 0) break;

                        // Cada ProductUnit = 1 unidad física
                        $qtyToTake = 1;

                        $exists = $quotation->items()
                            ->where('product_unit_id', $unit->id)
                            ->exists();

                        if (!$exists) {
                            QuotationItem::create([
                                'quotation_id' => $quotation->id,
                                'product_unit_id' => $unit->id,
                                'product_id' => $unit->product_id,
                                'quantity' => $qtyToTake,
                                'source_legal_entity_id' => $unit->legal_entity_id,
                                'source_sub_warehouse_id' => $unit->sub_warehouse_id,
                                'billing_mode' => 'rental',
                                'rental_price' => 0,
                                'sale_price' => 0,
                                'status' => 'pending',
                            ]);

                            $requiredQty -= $qtyToTake;
                        }
                    }
                }
            });

            return redirect()
                ->route('quotations.show', $quotation)
                ->with('success', "Prearmado '{$surgicalKit->name}' aplicado exitosamente.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al aplicar prearmado: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ACCIONES ADICIONALES
    // ═══════════════════════════════════════════════════════════

    public function toggleActive(SurgicalKit $surgicalKit)
    {
        $surgicalKit->update([
            'is_active' => !$surgicalKit->is_active
        ]);

        $status = $surgicalKit->is_active ? 'activado' : 'desactivado';

        return redirect()
            ->back()
            ->with('success', "Prearmado {$status} exitosamente.");
    }

    public function duplicate(SurgicalKit $surgicalKit)
    {
        try {
            DB::transaction(function () use ($surgicalKit) {
                $newKit = $surgicalKit->replicate();
                $newKit->name = $surgicalKit->name . ' (Copia)';
                $newKit->code = null;
                $newKit->created_by = auth()->id();
                $newKit->save();

                foreach ($surgicalKit->items as $item) {
                    SurgicalKitItem::create([
                        'surgical_kit_id' => $newKit->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'notes' => $item->notes,
                    ]);
                }
            });

            return redirect()
                ->route('surgical-kits.index')
                ->with('success', 'Prearmado duplicado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al duplicar el prearmado: ' . $e->getMessage());
        }
    }
}