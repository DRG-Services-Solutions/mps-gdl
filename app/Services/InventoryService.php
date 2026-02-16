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
            

            $dbType = match($type) {
                    'in' => 'entry',  
                    'out' => 'exit',  
                    default => $type, 
                };
           \Log::info("🏁 Iniciando transacción de Inventario para Producto ID: {$productId}");
            $batchNumber = $lotNumber ?? ''; // Lote vacío si es nulo

            $searchCriteria = [
            'product_id' => $productId,
            'sub_warehouse_id' => $subWarehouseId,
            'batch_number' => $batchNumber
            ];
            \Log::info("🔍 Buscando Summary con:", $searchCriteria);
            
            // 2. Buscar o Crear el registro de Resumen (Snapshot)
            // Usamos lockForUpdate() para evitar condiciones de carrera (Race Conditions)
            try {
            $summary = InventorySummary::firstOrCreate(
                [
                    'product_id' => $productId,
                    'sub_warehouse_id' => $subWarehouseId,
                    'batch_number' => $batchNumber,
                    'legal_entity_id' => $legalEntityId
                ],
                [
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'expiration_date' => $expirationDate // Solo se guarda al crear
                ]
            );
            \Log::info("✅ Summary obtenido/creado ID: " . $summary->id);
            } catch (\Exception $e) {
            \Log::error("❌ Error al crear Summary: " . $e->getMessage());
            throw $e; // Re-lanzar para que falle la transacción
        }

            // Bloqueamos la fila para que nadie más la modifique mientras calculamos
            $summary->refresh()->lockForUpdate();

            // 3. Validar y Actualizar Stock
            if ($type === 'out') {
                if ($summary->quantity_on_hand < $quantity) {
                    throw new Exception("Stock insuficiente para el producto ID: {$productId}, Lote: {$batchNumber}. Disponible: {$summary->quantity_on_hand}");
                }
                $summary->decrement('quantity_on_hand', $quantity);
            } else {
                $summary->increment('quantity_on_hand', $quantity);
            }

            // 4. Crear el Histórico (El rastro de migas de pan)
            $movement = InventoryMovement::create([
                'product_id' => $productId,
                'sub_warehouse_id' => $subWarehouseId,
                'legal_entity_id' => $legalEntityId,
                'type' => $dbType,
                'quantity' => $quantity,
                'batch_number' => $batchNumber, // Asegúrate de tener esta columna en inventory_movements también
                'previous_balance' => $type === 'in' ? ($summary->quantity_on_hand - $quantity) : ($summary->quantity_on_hand + $quantity),
                'new_balance' => $summary->quantity_on_hand,
                'reason' => $reason,
                'user_id' => auth()->id() ?? null,
            ]);

            return $movement;
        });
    }
}