<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\StorageLocation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'destinationWarehouse', 'createdBy']);

        // Filtros
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('supplier_id') && $request->supplier_id != '') {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('is_paid')) {
            $query->where('is_paid', $request->boolean('is_paid'));
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenar por más recientes
        $purchaseOrders = $query->orderBy('order_date', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->paginate(15);

        // Para los filtros
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = StorageLocation::active()->warehouses()->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('purchase-orders.create', compact('suppliers', 'warehouses', 'products'));
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'destination_warehouse_id' => 'required|exists:storage_locations,id',
            'expected_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string',
            
            // Items
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Crear la orden
            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'destination_warehouse_id' => $validated['destination_warehouse_id'],
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'order_date' => now(),
                'status' => 'pending',
            ]);

            // Crear los items
            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            // Calcular totales
            $purchaseOrder->calculateTotals();

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Orden de compra creada exitosamente: ' . $purchaseOrder->order_number);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la orden de compra: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'destinationWarehouse',
            'createdBy',
            'items.product'
        ]);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the purchase order.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        // No permitir editar si está cancelada
        if (!$purchaseOrder->canBeEdited()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'No se puede editar una orden cancelada.');
        }

        $purchaseOrder->load('items.product');
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = StorageLocation::active()->warehouses()->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'warehouses', 'products'));
    }

    /**
     * Update the specified purchase order.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        // No permitir editar si está cancelada
        if (!$purchaseOrder->canBeEdited()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'No se puede editar una orden cancelada.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'destination_warehouse_id' => 'required|exists:storage_locations,id',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            
            // Items
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Actualizar la orden
            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'destination_warehouse_id' => $validated['destination_warehouse_id'],
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // IDs de items existentes en el request
            $itemIds = collect($validated['items'])->pluck('id')->filter();

            // Eliminar items que ya no están en el request
            $purchaseOrder->items()->whereNotIn('id', $itemIds)->delete();

            // Actualizar o crear items
            foreach ($validated['items'] as $itemData) {
                if (isset($itemData['id'])) {
                    // Actualizar existente
                    $item = PurchaseOrderItem::find($itemData['id']);
                    $item->update([
                        'product_id' => $itemData['product_id'],
                        'quantity_ordered' => $itemData['quantity_ordered'],
                        'unit_price' => $itemData['unit_price'],
                    ]);
                } else {
                    // Crear nuevo
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $itemData['product_id'],
                        'quantity_ordered' => $itemData['quantity_ordered'],
                        'unit_price' => $itemData['unit_price'],
                    ]);
                }
            }

            // Recalcular totales
            $purchaseOrder->calculateTotals();

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Orden de compra actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la orden de compra: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the purchase order.
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        if (!$purchaseOrder->canBeEdited()) {
            return redirect()->back()
                ->with('error', 'Esta orden ya está cancelada.');
        }

        $purchaseOrder->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Orden de compra cancelada.');
    }

    /**
     * Mark as paid.
     */
    public function markAsPaid(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'is_paid' => true,
            'paid_date' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Orden marcada como pagada.');
    }

    /**
     * Mark as unpaid.
     */
    public function markAsUnpaid(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'is_paid' => false,
            'paid_date' => null,
        ]);

        return redirect()->back()
            ->with('success', 'Orden marcada como no pagada.');
    }

    /**
     * Get product details for adding to order (AJAX).
     */
    public function getProductDetails(Product $product)
    {
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'description' => $product->description,
                'unit_price' => $product->price ?? 0, // Asume que tienes un campo price
            ]
        ]);
    }
}