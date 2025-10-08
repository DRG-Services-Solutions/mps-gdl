<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReceipt;
use App\Models\ReceiptItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReceiptController extends Controller
{
    // Listar todas las recepciones
    public function index()
    {
        $receipts = PurchaseOrderReceipt::with(['purchaseOrder.supplier', 'receivedBy'])
            ->latest()
            ->paginate(15);

        return view('receipts.index', compact('receipts'));
    }

    // Mostrar formulario para nueva recepción
    public function create(PurchaseOrder $purchaseOrder)
    {
        // Verificar que la orden esté aprobada
        if ($purchaseOrder->status !== 'approved') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Solo se pueden recibir órdenes aprobadas.');
        }

        $purchaseOrder->load(['items.product', 'supplier']);
        
        return view('receipts.create', compact('purchaseOrder'));
    }

    // Guardar nueva recepción
    public function store(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'receipt_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|integer|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date|after:today',
            'items.*.condition' => 'required|in:good,damaged,expired',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Crear recepción
            $receipt = PurchaseOrderReceipt::create([
                'purchase_order_id' => $purchaseOrder->id,
                'receipt_number' => PurchaseOrderReceipt::generateReceiptNumber(),
                'receipt_date' => $validated['receipt_date'],
                'received_by' => auth()->id(),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            $hasIssues = false;
            $fullyReceived = true;

            // Crear items de recepción
            foreach ($validated['items'] as $itemData) {
                $orderItem = $purchaseOrder->items()->findOrFail($itemData['purchase_order_item_id']);

                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'quantity_ordered' => $orderItem->quantity,
                    'quantity_received' => $itemData['quantity_received'],
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'condition' => $itemData['condition'],
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Verificar discrepancias
                if ($itemData['quantity_received'] != $orderItem->quantity) {
                    $fullyReceived = false;
                }

                if ($itemData['condition'] !== 'good') {
                    $hasIssues = true;
                }

                // Actualizar inventario (si el producto está en buenas condiciones)
                if ($itemData['condition'] === 'good') {
                    $orderItem->product()->increment('stock', $itemData['quantity_received']);
                }
            }

            // Actualizar estado de la recepción
            $receipt->update([
                'status' => $hasIssues ? 'with_issues' : ($fullyReceived ? 'completed' : 'partial'),
            ]);

            // Actualizar estado de la orden de compra
            $purchaseOrder->update([
                'receipt_status' => $fullyReceived ? 'fully_received' : 'partially_received',
            ]);

            DB::commit();

            return redirect()->route('receipts.show', $receipt)
                ->with('success', 'Recepción registrada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar la recepción: ' . $e->getMessage());
        }
    }

    // Ver detalles de una recepción
    public function show(PurchaseOrderReceipt $receipt)
    {
        $receipt->load(['purchaseOrder.supplier', 'items.product', 'receivedBy']);
        
        return view('receipts.show', compact('receipt'));
    }
}