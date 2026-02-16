<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\InventorySummary;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Registra una entrada o salida de inventario y actualiza el resumen.
     * * @param int $productId
     * @param int $warehouseId
     * @param float $quantity (Siempre positivo)
     * @param string $type ('in' o 'out')
     * @param string|null $lotNumber
     * @param string|null $expirationDate
     * @param string $reason (Ej. 'Compra', 'Venta', 'Ajuste')
     */
    public function registerMovement(
        int $productId, 
        int $subWarehouseId, 
        int $legalEntityId,
        float $quantity, 
        string $type, 
        ?string $lotNumber = null,
        ?string $expirationDate = null,
        string $reason = ''
    ) {
        return DB::transaction(function () use ($productId, $subWarehouseId, $legalEntityId, $quantity, $type, $lotNumber, $expirationDate, $reason) {
            
            // 1. TRADUCCIÓN (Esto crea la variable $dbType)
            $dbType = match($type) {
                'in' => 'entry',  // Traduce 'in' -> 'entry'
                'out' => 'exit',  // Traduce 'out' -> 'exit'
                default => $type,
            };

            $lotNumber = $lotNumber ?? '';

            // 2. Summary (Snapshot)
            $summary = InventorySummary::firstOrCreate(
                [
                    'product_id' => $productId,
                    'sub_warehouse_id' => $subWarehouseId,
                    'legal_entity_id' => $legalEntityId,
                    'batch_number' => $lotNumber
                ],
                [
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'expiration_date' => $expirationDate
                ]
            );

            $summary->refresh()->lockForUpdate();
            
            // Cálculo de saldos
            $previousBalance = floatval($summary->quantity_on_hand);
            
            if ($type === 'out') {
                if ($previousBalance < $quantity) throw new Exception("Stock insuficiente.");
                $summary->decrement('quantity_on_hand', $quantity);
                $newBalance = $previousBalance - $quantity;
            } else {
                $summary->increment('quantity_on_hand', $quantity);
                $newBalance = $previousBalance + $quantity;
            }

            // 3. CREAR MOVIMIENTO (¡AQUÍ ESTÁ EL ERROR!)
            $movement = InventoryMovement::create([
                'product_id' => $productId,
                'sub_warehouse_id' => $subWarehouseId,
                'legal_entity_id' => $legalEntityId,
                
                // --- CORRECCIÓN CRÍTICA ---
                'type' => $dbType, // <--- DEBE DECIR $dbType, NO $type
                // --------------------------
                
                'quantity' => $quantity,
                'batch_number' => $lotNumber,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'reason' => $reason,
                'user_id' => auth()->id() ?? null,
            ]);

            return $movement;
        });
    }
}