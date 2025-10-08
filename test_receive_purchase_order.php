<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceipt;
use App\Models\ReceiptItem;
use App\Models\Supplier;
use App\Models\StorageLocation;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

// Inicializar el entorno Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Iniciando prueba de recepción de orden de compra...\n";

try {
    // 🔹 Limpiar base de datos correctamente
    echo "🗑️  Limpiando tablas...\n";
    
    DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Deshabilitar foreign keys
    
    DB::table('receipt_items')->truncate();
    DB::table('purchase_order_receipts')->truncate();
    DB::table('purchase_order_items')->truncate();
    DB::table('purchase_orders')->truncate();
    // NO limpiar productos, suppliers ni warehouses (datos maestros)
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Rehabilitar foreign keys
    
    echo "✅ Tablas limpiadas\n\n";

    // 🔹 Obtener supplier
    $supplier = Supplier::first();
    if (!$supplier) {
        echo "❌ No hay suppliers en el sistema\n";
        exit(1);
    }
    echo "✅ Supplier: {$supplier->name}\n";

    // 🔹 Obtener warehouse
    $warehouse = StorageLocation::where('type', 'warehouse')->first();
    if (!$warehouse) {
        echo "❌ No hay almacenes en el sistema\n";
        exit(1);
    }
    echo "✅ Warehouse: {$warehouse->name}\n";

    // 🔹 Obtener usuario
    $user = User::first();
    if (!$user) {
        echo "❌ No hay usuarios en el sistema\n";
        exit(1);
    }
    echo "✅ Usuario: {$user->name}\n\n";

    // 🔹 Buscar o crear producto
    echo "📦 Buscando o creando producto...\n";
    $product = Product::firstOrCreate(
        ['code' => 'AR-1325'], // Buscar por código
        [
            'name' => 'Spear for Bio-Fastak',
            'description' => 'Producto de prueba para recepciones',
            'stock' => 0,
            'min_stock' => 5,
            'price' => 350.00,
            'unit' => 'pcs',
            'is_active' => true,
        ]
    );
    
    // Resetear stock a 0 para la prueba
    $stockAnterior = $product->stock;
    $product->update(['stock' => 0]);
    
    echo "✅ Producto: {$product->code} - {$product->name}\n";
    echo "   Stock anterior: {$stockAnterior}, Stock para prueba: {$product->stock}\n\n";

    // 🔹 Crear orden de compra
    echo "📋 Creando orden de compra...\n";
    $order = PurchaseOrder::create([
        'order_number' => 'PO-2025-TEST-' . now()->format('His'),
        'supplier_id' => $supplier->id,
        'destination_warehouse_id' => $warehouse->id,
        'created_by' => $user->id,
        'status' => 'pending',
        'order_date' => now(),
        'expected_date' => now()->addDays(7),
        'subtotal' => 6300.00,
        'tax' => 1008.00,
        'total' => 7308.00,
        'is_paid' => false,
    ]);
    echo "✅ Orden creada: {$order->order_number}\n";
    echo "   Status: {$order->status}\n\n";

    // 🔹 Crear item de orden
    echo "📦 Creando item de orden...\n";
    $item = PurchaseOrderItem::create([
        'purchase_order_id' => $order->id,
        'product_id' => $product->id,
        'product_code' => $product->code,
        'product_name' => $product->name,
        'description' => $product->description,
        'quantity_ordered' => 18,
        'quantity_received' => 0,
        'unit_price' => 350.00,
        'subtotal' => 6300.00,
    ]);
    echo "✅ Item creado:\n";
    echo "   Producto: {$item->product_name}\n";
    echo "   Cantidad ordenada: {$item->quantity_ordered}\n";
    echo "   Cantidad recibida: {$item->quantity_received}\n";
    echo "   Pendiente: {$item->pending_quantity}\n\n";

    // 🔹 Verificar que puede recibirse
    echo "🔍 Verificando si la orden puede recibirse...\n";
    $canReceive = $order->canBeReceived();
    echo "   Can be received: " . ($canReceive ? '✅ SÍ' : '❌ NO') . "\n";
    
    if (!$canReceive) {
        echo "   Status: {$order->status}\n";
        echo "   Is fully received: " . ($order->isFullyReceived() ? 'SÍ' : 'NO') . "\n";
        
        // Debug adicional
        echo "\n🔍 Debug información:\n";
        echo "   Status actual: {$order->status}\n";
        echo "   Status permitidos: pending, partial\n";
        echo "   Total items: {$order->items->count()}\n";
        
        foreach ($order->items as $debugItem) {
            echo "   - Item {$debugItem->id}: ordenado={$debugItem->quantity_ordered}, recibido={$debugItem->quantity_received}, pendiente={$debugItem->pending_quantity}\n";
        }
        
        echo "\n❌ La orden NO puede recibirse\n";
        exit(1);
    }
    echo "\n";

    // 🔹 Simular recepción
    echo "📥 Simulando recepción de 10 unidades...\n";
    
    DB::beginTransaction();
    
    try {
        // Crear recepción
        $receiptNumber = PurchaseOrderReceipt::generateReceiptNumber();
        echo "   Generando número: {$receiptNumber}\n";
        
        $receipt = PurchaseOrderReceipt::create([
            'receipt_number' => $receiptNumber,
            'purchase_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'received_by' => $user->id,
            'received_at' => now(),
            'status' => 'pending',
            'notes' => 'Recepción parcial de prueba automatizada',
        ]);
        
        echo "✅ Receipt creado: {$receipt->receipt_number}\n";

        // Crear item de recepción
        $quantityToReceive = 10;
        
        ReceiptItem::create([
            'receipt_id' => $receipt->id,
            'purchase_order_item_id' => $item->id,
            'product_id' => $product->id,
            'quantity_ordered' => $item->quantity_ordered,
            'quantity_received' => $quantityToReceive,
            'unit_price' => $item->unit_price,
            'batch_number' => 'LOTE-TEST-' . now()->format('Ymd-His'),
            'expiry_date' => now()->addYears(2),
            'condition' => 'good',
            'notes' => 'Recepción de prueba',
        ]);
        
        echo "✅ ReceiptItem creado (cantidad: {$quantityToReceive})\n";

        // Actualizar item de orden
        $oldQuantity = $item->quantity_received;
        $item->increment('quantity_received', $quantityToReceive);
        $item->update([
            'received_by' => $user->id,
            'received_at' => now(),
        ]);
        
        $item->refresh();
        echo "✅ PurchaseOrderItem actualizado: {$oldQuantity} → {$item->quantity_received}\n";

        // Actualizar stock
        $oldStock = $product->stock;
        $product->increment('stock', $quantityToReceive);
        $product->refresh();
        
        echo "✅ Stock actualizado: {$oldStock} → {$product->stock}\n";

        // Actualizar estado de la orden
        $oldStatus = $order->status;
        $order->update(['status' => 'partial']);
        
        echo "✅ Estado de orden actualizado: {$oldStatus} → partial\n";

        // Actualizar estado del receipt
        $receipt->update(['status' => 'partial']);

        DB::commit();
        
        echo "\n✅ Transacción completada exitosamente\n\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "\n❌ Error en la transacción: {$e->getMessage()}\n";
        throw $e;
    }

    // 🔹 Refrescar modelos
    $order->refresh();
    $item->refresh();
    $product->refresh();
    $receipt->refresh();

    // 🔹 Mostrar resultados
    echo "═══════════════════════════════════════════\n";
    echo "📊 RESULTADOS FINALES\n";
    echo "═══════════════════════════════════════════\n\n";
    
    echo "📋 ORDEN DE COMPRA:\n";
    echo "   Número: {$order->order_number}\n";
    echo "   Estado: {$order->status} (" . $order->status_label . ")\n";
    echo "   Proveedor: {$order->supplier->name}\n";
    echo "   Almacén: {$order->destinationWarehouse->name}\n";
    echo "   Total items: {$order->items->count()}\n";
    echo "   Total recepciones: {$order->receipts->count()}\n\n";
    
    echo "📦 ITEM DE ORDEN:\n";
    echo "   Producto: {$item->product_code} - {$item->product_name}\n";
    echo "   Cantidad ordenada: {$item->quantity_ordered}\n";
    echo "   Cantidad recibida: {$item->quantity_received}\n";
    echo "   Cantidad pendiente: {$item->pending_quantity}\n";
    echo "   Progreso: " . number_format($item->receipt_progress, 1) . "%\n";
    echo "   Completamente recibido: " . ($item->isFullyReceived() ? '✅ SÍ' : '❌ NO (faltan ' . $item->pending_quantity . ')') . "\n\n";
    
    echo "📥 RECEPCIÓN:\n";
    echo "   Número: {$receipt->receipt_number}\n";
    echo "   Estado: {$receipt->status}\n";
    echo "   Items recibidos: {$receipt->items->count()}\n";
    echo "   Total recibido: {$receipt->items->sum('quantity_received')} piezas\n";
    echo "   Recibido por: {$receipt->receivedBy->name}\n";
    echo "   Fecha: {$receipt->received_at->format('d/m/Y H:i:s')}\n\n";
    
    echo "🏭 INVENTARIO DE PRODUCTO:\n";
    echo "   Código: {$product->code}\n";
    echo "   Nombre: {$product->name}\n";
    echo "   Stock actual: {$product->stock} {$product->unit}\n";
    echo "   Stock mínimo: {$product->min_stock} {$product->unit}\n";
    echo "   Estado: " . ($product->is_active ? '✅ Activo' : '❌ Inactivo') . "\n\n";
    
    // Verificar consistencia
    echo "🔍 VERIFICACIÓN DE CONSISTENCIA:\n";
    $totalRecibidoEnRecepciones = $order->receipts->sum(function($r) { 
        return $r->items->sum('quantity_received'); 
    });
    $totalRecibidoEnItems = $order->items->sum('quantity_received');
    
    echo "   Total en recepciones: {$totalRecibidoEnRecepciones}\n";
    echo "   Total en items: {$totalRecibidoEnItems}\n";
    echo "   Stock del producto: {$product->stock}\n";
    echo "   Consistencia: " . ($totalRecibidoEnRecepciones === $totalRecibidoEnItems && $totalRecibidoEnItems === $product->stock ? '✅ CORRECTA' : '❌ INCONSISTENTE') . "\n\n";
    
    echo "═══════════════════════════════════════════\n";
    echo "✅ PRUEBA COMPLETADA EXITOSAMENTE\n";
    echo "═══════════════════════════════════════════\n";
    echo "\n💡 La orden ahora está en estado 'partial' con 10/18 unidades recibidas.\n";
    echo "   Puedes intentar registrar otra recepción de las 8 restantes.\n\n";

} catch (Exception $e) {
    echo "\n═══════════════════════════════════════════\n";
    echo "❌ ERROR DURANTE LA PRUEBA\n";
    echo "═══════════════════════════════════════════\n";
    echo "Mensaje: {$e->getMessage()}\n";
    echo "Archivo: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
    echo "\n";
}