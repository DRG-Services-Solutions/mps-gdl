<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReceipt;
use App\Models\ReceiptItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class PurchaseOrderService
{
    /**
     * Procesa la recepción de una orden de compra
     */
    public function receive(PurchaseOrder $purchaseOrder, array $validated, int $userId): PurchaseOrderReceipt
    {
        if (!$purchaseOrder->canBeReceived()) {
            throw new Exception("Esta orden no puede ser recibida en este momento.");
        }

        return DB::transaction(function () use ($purchaseOrder, $validated, $userId) {

            $receipt = PurchaseOrderReceipt::create([
                'receipt_number' => PurchaseOrderReceipt::generateReceiptNumber(),
                'purchase_order_id' => $purchaseOrder->id,
                'warehouse_id' => $purchaseOrder->warehouse_id,
                'received_by' => $userId,
                'received_at' => now(),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            $totalReceived = 0;
            $hasIssues = false;

            foreach ($validated['items'] as $itemId => $itemData) {
                $quantity = (int) $itemData['quantity_received'];

                if ($quantity <= 0) continue;

                $orderItem = $purchaseOrder->items()->findOrFail($itemId);

                if ($quantity > $orderItem->pending_quantity) {
                    throw new Exception("La cantidad a recibir ({$quantity}) excede la cantidad pendiente ({$orderItem->pending_quantity}) para {$orderItem->product_name}");
                }

                $condition = $itemData['condition'] ?? 'good';
                if ($condition !== 'good') $hasIssues = true;

                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'quantity_ordered' => $orderItem->quantity_ordered,
                    'quantity_received' => $quantity,
                    'unit_price' => $orderItem->unit_price,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'condition' => $condition,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Actualiza la cantidad recibida
                $orderItem->increment('quantity_received', $quantity);
                $orderItem->update([
                    'received_by' => $userId,
                    'received_at' => now(),
                ]);

                // Actualiza stock global (o por almacén)
                if ($condition === 'good') {
                    $product = Product::findOrFail($orderItem->product_id);
                    $product->increment('stock', $quantity);
                }

                $totalReceived += $quantity;
            }

            // Actualiza estados
            $receiptStatus = $hasIssues ? 'with_issues' : ($purchaseOrder->isFullyReceived() ? 'completed' : 'partial');
            $receipt->update(['status' => $receiptStatus]);

            if ($purchaseOrder->isFullyReceived()) {
                $purchaseOrder->update([
                    'status' => 'completed',
                    'received_date' => now(),
                ]);
            } elseif ($totalReceived > 0) {
                $purchaseOrder->update(['status' => 'partial']);
            }

            return $receipt;
        });
    }
}
