<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReceipt;
use App\Models\ReceiptItem;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PrintJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PurchaseOrderService
{
    protected $rfidService;

    public function __construct(RfidLabelService $rfidService)
    {
        $this->rfidService = $rfidService;
    }

    /**
     * Procesa la recepción de una orden de compra
     * 
     * MEJORAS:
     * - Genera print jobs automáticamente para productos RFID
     * - Mantiene compatibilidad con código existente
     */
    public function receive(PurchaseOrder $purchaseOrder, array $validated, int $userId): PurchaseOrderReceipt
    {
        if (!$purchaseOrder->canBeReceived()) {
            throw new Exception("Esta orden no puede ser recibida en este momento.");
        }

        return DB::transaction(function () use ($purchaseOrder, $validated, $userId) {

            // 1. Crear recepción
            $receipt = PurchaseOrderReceipt::create([
                'receipt_number' => PurchaseOrderReceipt::generateReceiptNumber(),
                'purchase_order_id' => $purchaseOrder->id,
                'warehouse_id' => $purchaseOrder->warehouse_id,
                'received_by' => $userId,
                'received_at' => now(),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            Log::info("📦 Recepción creada: {$receipt->receipt_number}");

            $totalReceived = 0;
            $hasIssues = false;
            $totalRfidJobs = 0;

            // 2. Procesar cada ítem recibido
            foreach ($validated['items'] as $itemId => $itemData) {
                $quantity = (int) $itemData['quantity_received'];

                if ($quantity <= 0) continue;

                $orderItem = $purchaseOrder->items()->findOrFail($itemId);
                $product = Product::findOrFail($orderItem->product_id);

                // Validar cantidad pendiente
                if ($quantity > $orderItem->pending_quantity) {
                    throw new Exception(
                        "La cantidad a recibir ({$quantity}) excede la cantidad pendiente ({$orderItem->pending_quantity}) para {$orderItem->product_name}"
                    );
                }

                $condition = $itemData['condition'] ?? 'good';
                if ($condition !== 'good') $hasIssues = true;

                // 2.1 Crear registro de recepción
                $receiptItem = ReceiptItem::create([
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

                Log::info("📋 Item recibido: {$product->name} x {$quantity}");

                // 2.2 Crear unidades individuales según tipo de tracking
                if ($condition === 'good') {
                    $createdUnits = $this->createProductUnits(
                        $product,
                        $quantity,
                        $purchaseOrder->warehouse_id,
                        $receiptItem->id,
                        $itemData
                    );

                    Log::info("   └─ Unidades creadas: {$createdUnits}");
                }

                // 2.3 Actualizar cantidad recibida en la orden
                $orderItem->increment('quantity_received', $quantity);
                $orderItem->update([
                    'received_by' => $userId,
                    'received_at' => now(),
                ]);

                // 2.4 Actualizar stock global (solo para productos en buen estado)
                if ($condition === 'good') {
                    $product->increment('stock', $quantity);
                    Log::info("   └─ Stock actualizado: {$product->stock}");
                }

                $totalReceived += $quantity;
            }

            // 3. Generar print jobs para productos RFID
            Log::info("🏷️ Generando etiquetas RFID...");
            $totalRfidJobs = $this->rfidService->createPrintJobsForReceipt($receipt);
            
            if ($totalRfidJobs > 0) {
                Log::info("✅ {$totalRfidJobs} etiquetas RFID generadas");
            }

            // 4. Actualizar estados de la recepción y orden
            $receiptStatus = $hasIssues 
                ? 'with_issues' 
                : ($purchaseOrder->isFullyReceived() ? 'completed' : 'partial');
            
            $receipt->update(['status' => $receiptStatus]);

            if ($purchaseOrder->isFullyReceived()) {
                $purchaseOrder->update([
                    'status' => 'completed',
                    'received_date' => now(),
                ]);
                Log::info("✅ Orden completada: {$purchaseOrder->order_number}");
            } elseif ($totalReceived > 0) {
                $purchaseOrder->update(['status' => 'partial']);
                Log::info("⚠️ Orden parcial: {$purchaseOrder->order_number}");
            }

            Log::info("🎉 Recepción procesada exitosamente");
            Log::info("   └─ Total recibido: {$totalReceived} unidades");
            Log::info("   └─ Etiquetas RFID: {$totalRfidJobs} jobs");

            return $receipt;
        });
    }

    /**
     * Crear unidades individuales según el tipo de tracking del producto
     * 
     * @param Product $product
     * @param int $quantity
     * @param int $warehouseId
     * @param int $receiptItemId
     * @param array $itemData
     * @return int Cantidad de unidades creadas
     */
    protected function createProductUnits(
        Product $product, 
        int $quantity, 
        int $warehouseId, 
        int $receiptItemId,
        array $itemData
    ): int {
        $trackingType = $product->tracking_type;
        $unitsCreated = 0;

        switch ($trackingType) {
            case 'code':
                // TRACKING POR CÓDIGO: 1 unidad virtual por cada unidad física
                // No necesita número de serie individual
                for ($i = 0; $i < $quantity; $i++) {
                    ProductUnit::create([
                        'product_id' => $product->id,
                        'tracking_type' => 'code',
                        'serial_number' => null, // Sin número de serie
                        'epc' => null, // Sin EPC
                        'batch_number' => $itemData['batch_number'] ?? null,
                        'expiry_date' => $itemData['expiry_date'] ?? null,
                        'status' => 'available',
                        'condition' => 'good',
                        'current_location_id' => $warehouseId,
                        'current_location_type' => 'warehouse',
                        'receipt_item_id' => $receiptItemId,
                    ]);
                    $unitsCreated++;
                }
                
                Log::info("   └─ Tipo: CODE (tracking básico por código)");
                break;

            case 'rfid':
                // TRACKING POR RFID: 1 unidad con EPC único
                // El EPC se generará cuando se cree el print job
                for ($i = 0; $i < $quantity; $i++) {
                    ProductUnit::create([
                        'product_id' => $product->id,
                        'tracking_type' => 'rfid',
                        'serial_number' => null, // Se puede agregar opcionalmente
                        'epc' => null, // Se asignará al generar el print job
                        'batch_number' => $itemData['batch_number'] ?? null,
                        'expiry_date' => $itemData['expiry_date'] ?? null,
                        'status' => 'available',
                        'condition' => 'good',
                        'current_location_id' => $warehouseId,
                        'current_location_type' => 'warehouse',
                        'receipt_item_id' => $receiptItemId,
                        'print_job_id' => null, // Se asignará al crear print job
                    ]);
                    $unitsCreated++;
                }
                
                Log::info("   └─ Tipo: RFID (etiquetas pendientes de impresión)");
                break;

            case 'serial':
                // TRACKING POR NÚMERO DE SERIE: 1 unidad con serial único
                // Nota: Aquí deberías solicitar los números de serie al usuario
                // Por ahora, los generamos automáticamente
                for ($i = 0; $i < $quantity; $i++) {
                    $serialNumber = $this->generateSerialNumber($product, $i);
                    
                    ProductUnit::create([
                        'product_id' => $product->id,
                        'tracking_type' => 'serial',
                        'serial_number' => $serialNumber,
                        'epc' => null, // No usa EPC
                        'batch_number' => $itemData['batch_number'] ?? null,
                        'expiry_date' => $itemData['expiry_date'] ?? null,
                        'status' => 'available',
                        'condition' => 'good',
                        'current_location_id' => $warehouseId,
                        'current_location_type' => 'warehouse',
                        'receipt_item_id' => $receiptItemId,
                    ]);
                    $unitsCreated++;
                }
                
                Log::info("   └─ Tipo: SERIAL (números de serie asignados)");
                break;

            default:
                Log::warning("⚠️ Tipo de tracking desconocido: {$trackingType}");
                break;
        }

        return $unitsCreated;
    }

    /**
     * Generar número de serie automático
     * 
     * NOTA: En producción, estos números deberían venir del usuario
     * o del sistema del proveedor
     * 
     * @param Product $product
     * @param int $index
     * @return string
     */
    protected function generateSerialNumber(Product $product, int $index): string
    {
        // Formato: CODIGO-TIMESTAMP-INDEX
        // Ejemplo: BI001-1730745600-001
        
        $productCode = str_replace([' ', '-'], '', strtoupper($product->code));
        $timestamp = now()->timestamp;
        $indexPadded = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
        
        return "{$productCode}-{$timestamp}-{$indexPadded}";
    }

    /**
     * Regenerar etiquetas RFID para una recepción
     * Útil si hubo problemas con la impresión inicial
     * 
     * @param PurchaseOrderReceipt $receipt
     * @return int Cantidad de jobs regenerados
     */
    public function regenerateRfidLabels(PurchaseOrderReceipt $receipt): int
    {
        return DB::transaction(function () use ($receipt) {
            
            // 1. Eliminar print jobs antiguos
            PrintJob::where('receipt_id', $receipt->id)
                ->where('status', '!=', 'completed')
                ->delete();
            
            // 2. Limpiar referencias en unidades
            ProductUnit::whereHas('receiptItem', function($query) use ($receipt) {
                $query->where('receipt_id', $receipt->id);
            })
            ->whereNotNull('print_job_id')
            ->update([
                'epc' => null,
                'print_job_id' => null
            ]);
            
            // 3. Regenerar print jobs
            $totalJobs = $this->rfidService->createPrintJobsForReceipt($receipt);
            
            Log::info("🔄 Etiquetas RFID regeneradas: {$totalJobs} jobs");
            
            return $totalJobs;
        });
    }

    /**
     * Obtener resumen de una recepción
     * 
     * @param PurchaseOrderReceipt $receipt
     * @return array
     */
    public function getReceiptSummary(PurchaseOrderReceipt $receipt): array
    {
        $items = $receipt->items()->with('product')->get();
        
        $totalUnits = ProductUnit::whereHas('receiptItem', function($query) use ($receipt) {
            $query->where('receipt_id', $receipt->id);
        })->count();
        
        $rfidUnits = ProductUnit::whereHas('receiptItem', function($query) use ($receipt) {
            $query->where('receipt_id', $receipt->id);
        })->where('tracking_type', 'rfid')->count();
        
        $printJobs = PrintJob::where('receipt_id', $receipt->id)->get();
        
        return [
            'receipt' => $receipt,
            'items' => $items,
            'total_units' => $totalUnits,
            'rfid_units' => $rfidUnits,
            'print_jobs' => [
                'total' => $printJobs->count(),
                'pending' => $printJobs->where('status', 'pending')->count(),
                'printing' => $printJobs->where('status', 'printing')->count(),
                'completed' => $printJobs->where('status', 'completed')->count(),
                'failed' => $printJobs->where('status', 'failed')->count(),
            ],
        ];
    }
}