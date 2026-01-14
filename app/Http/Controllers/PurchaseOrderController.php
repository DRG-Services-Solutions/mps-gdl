<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\StorageLocation;
use App\Models\Product;
use App\Models\PurchaseOrderReceipt;
use App\Models\ReceiptItem;
use App\Services\PurchaseOrderService;
use App\Models\InventoryMovement;
use App\Models\ProductUnit;
use App\Models\LegalEntity;
use App\Models\SubWareHouse;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\RfidLabelService;

class PurchaseOrderController extends Controller
{
    protected $purchaseOrderService;

    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with([
            'supplier',
            'legalEntity', 
            'destinationWarehouse',
            'createdBy',
            'items.product',
            'subWareHouse',
            'receipts' => function($q) {
                $q->with(['receivedBy', 'warehouse', 'items.product'])
                  ->latest('received_at');
            }
        ])->withCount('receipts');

        // Filtro por estado
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filtro por proveedor
        if ($request->has('supplier_id') && $request->supplier_id != '') {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filtro por estado de pago
        if ($request->has('is_paid')) {
            $query->where('is_paid', $request->boolean('is_paid'));
        }

        // Búsqueda por texto
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // ✅ Filtro por Legal Entity
        if ($request->filled('legal_entity')) {
            $query->where('legal_entity_id', $request->legal_entity);
        }

        $purchaseOrders = $query->orderBy('order_date', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->paginate(15)
                                ->withQueryString();

        $suppliers = Supplier::active()->orderBy('name')->get();
        $legalEntities = LegalEntity::active()->orderBy('name')->get(); 

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers', 'legalEntities')); 
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $legalEntities = LegalEntity::active()->orderBy('name')->get(); 
        $subWarehouses = SubWareHouse::with('legalEntity')->where('is_active', true)->orderBy('name')->get()->groupBy('legal_entity_id');

        return view('purchase-orders.create', compact('suppliers', 'legalEntities', 'subWarehouses'));
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(Request $request)
    {
        // 1. Validación de campos estáticos
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'legal_entity_id' => 'required|exists:legal_entities,id', 
            'sub_warehouse_id' => 'nullable|exists:sub_warehouses,id', 
            'expected_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'items_json' => 'required|string', 
        ]);

        if ($request->filled('sub_warehouse_id')) {
            $subWarehouse = SubWarehouse::find($request->sub_warehouse_id);
            if ($subWarehouse && $subWarehouse->legal_entity_id != $request->legal_entity_id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'El sub-almacén seleccionado no pertenece a la razón social elegida.');
            }
        }

        try {
            DB::beginTransaction();
        
            // 2. Decodificar y Validar el array de Items
            $items = json_decode($validated['items_json'], true);
            \Log::info('📥 Items recibidos del frontend:', ['items' => $items]);

            if (empty($items)) {
                throw new \Exception('La orden de compra debe tener al menos un producto.');
            }

            $productIds = collect($items)->pluck('product_id')->unique()->toArray();
            
            $itemRules = [
                '*.product_id' => 'required|exists:products,id',
                '*.quantity_ordered' => 'required|integer|min:1',
                '*.unit_price' => 'required|numeric|min:0',
            ];
            
            $validator = Validator::make($items, $itemRules);

            if ($validator->fails()) {
                throw new \Exception('Error de validación en los productos: ' . $validator->errors()->first());
            }
            
            // Generar el SNAPSHOT de los productos
            $productsSnapshot = Product::whereIn('id', $productIds)
                ->get(['id', 'code', 'name', 'description'])
                ->keyBy('id');

            \Log::info('🔍 Snapshot de productos:', ['snapshot' => $productsSnapshot->toArray()]);

            // 3. Crear la Orden
            $purchaseOrder = PurchaseOrder::create([
                'order_number' => $this->generateOrderNumber(),
                'created_by' => auth()->id(), 
                'supplier_id' => $validated['supplier_id'],
                'legal_entity_id' => $validated['legal_entity_id'], 
                'sub_warehouse_id' => $validated['sub_warehouse_id'],
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'order_date' => now(),
                'status' => 'pending',
            ]);
            
            \Log::info('✅ Orden creada:', ['order_id' => $purchaseOrder->id, 'order_number' => $purchaseOrder->order_number]);

            // 4. Preparar la Inserción Masiva
            $itemsToInsert = [];
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $snapshot = $productsSnapshot->get($productId);
                
                if (!$snapshot) {
                    throw new \Exception("El producto con ID {$productId} no se encontró o no está disponible.");
                }

                $subtotalCalculated = round($item['quantity_ordered'] * $item['unit_price'], 2);

                $itemsToInsert[] = [
                    'product_id' => $productId,
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotalCalculated, 
                    'product_code' => $snapshot->code,
                    'product_name' => $snapshot->name,
                    'description' => $snapshot->description ?? null,
                    'status' => 'pending',
                    'supplier_id' => $validated['supplier_id'],
                    'created_at' => now(), 
                    'updated_at' => now(), 
                ];
            }
            
            \Log::info('📦 Items preparados para insertar:', ['count' => count($itemsToInsert), 'items' => $itemsToInsert]);

            $purchaseOrder->items()->createMany($itemsToInsert);
            
            \Log::info('✅ Items insertados en BD:', ['count' => $purchaseOrder->items()->count()]);

            // 5. Calcular Totales
            $purchaseOrder->calculateTotals();
            
            \Log::info('💰 Totales calculados:', [
                'subtotal' => $purchaseOrder->subtotal,
                'tax' => $purchaseOrder->tax,
                'total' => $purchaseOrder->total
            ]);
            
            DB::commit();
            
            \Log::info('🎉 Orden de compra creada exitosamente');

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Orden de compra creada exitosamente: ' . $purchaseOrder->order_number);

        } catch (QueryException $e) {
            DB::rollBack();
            \Log::error('❌ Error de base de datos:', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A'
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error en la base de datos. Mensaje: ' . $e->getMessage()); 
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ Error general:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
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
            'legalEntity',
            'subWarehouse',
            'destinationWarehouse',
            'createdBy', 
            'items.product'
        ]); 

        // Determinar si hay productos pendientes por recibir
        $hasPendingItems = $purchaseOrder->items()
            ->whereColumn('quantity_received', '<', 'quantity_ordered')
            ->exists();

        return view('purchase-orders.show', compact('purchaseOrder', 'hasPendingItems'));
    }

    /**
     * Show the form for editing the purchase order.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeEdited()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'No se puede editar una orden cancelada.');
        }

        $purchaseOrder->load('items.product');

        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $legalEntities = LegalEntity::active()->orderBy('name')->get();
        $subWarehouses = SubWarehouse::with('legalEntity')
                                     ->where('is_active', true)
                                     ->orderBy('name')
                                     ->get()
                                     ->groupBy('legal_entity_id');


        // Preparar los items para Alpine.js
        $items = $purchaseOrder->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity_ordered' => $item->quantity_ordered,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->quantity_ordered * $item->unit_price,
            ];
        });

        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'warehouses', 'products', 'items', 'legalEntities'));
    }

    /**
     * Update the specified purchase order.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeEdited()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'No se puede editar una orden cancelada.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'legal_entity_id' => 'required|exists:legal_entities,id',
            'sub_warehouse_id' => 'nullable|exists:sub_warehouses,id',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            
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

                'legal_entity_id' => $validated['legal_entity_id'], 
                'sub_warehouse_id' => $validated['sub_warehouse_id'], 
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($request->filled('sub_warehouse_id')) {
                $subWarehouse = SubWarehouse::find($request->sub_warehouse_id);
                if ($subWarehouse && $subWarehouse->legal_entity_id != $request->legal_entity_id) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'El sub-almacén seleccionado no pertenece a la razón social elegida.');
                }
            }

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
     * Receive items from purchase order.
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeReceived()) {
            return back()->with('error', 'Esta orden no puede ser recibida en este momento.');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.quantity_received' => 'required|integer|min:0',
            'items.*.batch_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'nullable|date|after:today',
            'items.*.condition' => 'nullable|in:good,damaged,expired',
            'items.*.notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'invoice_number' => 'nullable|string|max:255',      
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', 
        ]);

        DB::beginTransaction();
        try {
            // Crear el registro de recepción
            $receipt = PurchaseOrderReceipt::create([
                'receipt_number' => PurchaseOrderReceipt::generateReceiptNumber(),
                'purchase_order_id' => $purchaseOrder->id,
                'received_by' => auth()->id(),
                'received_at' => now(),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
            ]);
            
            if ($request->hasFile('invoice_file')) {
                $file = $request->file('invoice_file');
                $fileName = 'invoice_' . $receipt->receipt_number . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('invoices/receipts', $fileName, 'public');
                $receipt->update(['invoice_file' => $path]);
            }

            $totalReceived = 0;
            $hasIssues = false;

            // Procesar cada item
            foreach ($validated['items'] as $itemId => $itemData) {
                $quantityToReceive = (int) $itemData['quantity_received'];
                
                if ($quantityToReceive <= 0) {
                    continue;
                }

                $orderItem = $purchaseOrder->items()->findOrFail($itemId);
                
                if ($quantityToReceive > $orderItem->pending_quantity) {
                    throw new \Exception("La cantidad a recibir ({$quantityToReceive}) excede la cantidad pendiente ({$orderItem->pending_quantity}) para {$orderItem->product_name}");
                }

                $condition = $itemData['condition'] ?? 'good';
                
                if ($condition !== 'good') {
                    $hasIssues = true;
                }

                // Crear el registro de item recibido
                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'quantity_ordered' => $orderItem->quantity_ordered,
                    'quantity_received' => $quantityToReceive,
                    'unit_price' => $orderItem->unit_price,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'condition' => $condition,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Actualizar el item de la orden de compra
                $orderItem->increment('quantity_received', $quantityToReceive);
                $orderItem->update([
                    'received_by' => auth()->id(),
                    'received_at' => now(),
                ]);

                // ========================================
                // ACTUALIZAR INVENTARIO SEGÚN TIPO DE TRACKING
                // ========================================
                if ($condition === 'good') {
                    $product = Product::findOrFail($orderItem->product_id);
                    
                    $initialStatus = match($condition) {
                        'damaged' => 'damaged',
                        'expired' => 'expired',
                        default => 'available',
                    };

                    switch ($product->tracking_type) {
                        case 'stock':
                            InventoryMovement::create([
                                'type' => 'entry',
                                'product_id' => $product->id,
                                'product_unit_id' => null,
                                'legal_entity_id' => $purchaseOrder->legal_entity_id,
                                'sub_warehouse_id' => $purchaseOrder->sub_warehouse_id,
                                'quantity' => $quantityToReceive,
                                'from_location_id' => null,
                                'to_location_id' => $purchaseOrder->destination_warehouse_id,
                                'user_id' => auth()->id(),
                                'reference_number' => $receipt->receipt_number,
                                'notes' => "Recepción de orden de compra {$purchaseOrder->order_number}",
                                'unit_cost' => $orderItem->unit_price,
                                'total_cost' => $orderItem->unit_price * $quantityToReceive,
                                'lot_number' => $itemData['batch_number'] ?? null,
                                'expiration_date' => $itemData['expiry_date'] ?? null,
                                'movement_date' => now(),
                            ]);
                            
                            \Log::info("✅ Movimiento de inventario creado (tipo: stock, cantidad: {$quantityToReceive})");
                            break;

                        case 'rfid':
                        case 'serial':
                            // PRODUCTOS CON TRACKING INDIVIDUAL (INSTRUMENTALES)
                            for ($i = 0; $i < $quantityToReceive; $i++) {
                                // Crear unidad individual
                                $unit = ProductUnit::create([
                                    'product_id' => $product->id,
                                    'legal_entity_id' => $purchaseOrder->legal_entity_id,
                                    'sub_warehouse_id' => $purchaseOrder->sub_warehouse_id,
                                    'epc' => $product->tracking_type === 'rfid' ? $this->generateEPC() : null,
                                    'serial_number' => $product->tracking_type === 'serial' ? $this->generateSerialNumber($product) : null,
                                    'batch_number' => $itemData['batch_number'] ?? null,
                                    'expiration_date' => $itemData['expiry_date'] ?? null,
                                    'status' => $initialStatus,
                                    'current_location_id' => $purchaseOrder->destination_warehouse_id,
                                    'sterilization_cycles' => 0,
                                    'acquisition_cost' => $orderItem->unit_price,
                                    'acquisition_date' => now(),
                                    'notes' => "Recibido en orden {$purchaseOrder->order_number}",
                                    'created_by' => auth()->id(),
                                ]);

                                // Crear movimiento de entrada para esta unidad
                                InventoryMovement::create([
                                    'type' => 'entry',
                                    'product_id' => $product->id,
                                    'product_unit_id' => $unit->id,
                                    'legal_entity_id' => $purchaseOrder->legal_entity_id,
                                    'sub_warehouse_id' => $purchaseOrder->sub_warehouse_id,
                                    'quantity' => 1,
                                    'from_location_id' => null,
                                    'to_location_id' => $purchaseOrder->destination_warehouse_id,
                                    'user_id' => auth()->id(),
                                    'reference_number' => $receipt->receipt_number,
                                    'notes' => "Recepción de unidad individual - {$purchaseOrder->order_number}",
                                    'unit_cost' => $orderItem->unit_price,
                                    'total_cost' => $orderItem->unit_price,
                                    'movement_date' => now(),
                                ]);
                            }
                            
                            \Log::info("✅ {$quantityToReceive} unidades individuales creadas (tipo: {$product->tracking_type})");
                            break;

                        case 'none':
                        default:
                            // Sin tracking
                            InventoryMovement::create([
                                'type' => 'entry',
                                'product_id' => $product->id,
                                'product_unit_id' => null,
                                'legal_entity_id' => $purchaseOrder->legal_entity_id,
                                'sub_warehouse_id' => $purchaseOrder->sub_warehouse_id,
                                'quantity' => $quantityToReceive,
                                //'from_location_id' => null,
                                //'to_location_id' => $purchaseOrder->destination_warehouse_id,
                                'user_id' => auth()->id(),
                                'reference_number' => $receipt->receipt_number,
                                'notes' => "Recepción de orden {$purchaseOrder->order_number} (sin tracking)",
                                'unit_cost' => $orderItem->unit_price,
                                'total_cost' => $orderItem->unit_price * $quantityToReceive,
                                'movement_date' => now(),
                            ]);
                            break;
                    }
                }

                $totalReceived += $quantityToReceive;
            }

            // Actualizar estado de la recepción
            $receiptStatus = $hasIssues ? 'with_issues' : ($purchaseOrder->isFullyReceived() ? 'completed' : 'partial');
            $receipt->update(['status' => $receiptStatus]);

            // Actualizar estado de la orden de compra
            if ($purchaseOrder->isFullyReceived()) {
                $purchaseOrder->update([
                    'status' => 'received',
                    'received_date' => now(),
                ]);
            } else if ($totalReceived > 0) {
                $purchaseOrder->update([
                    'status' => 'partial',
                ]);
            }

            // Generar trabajos de impresión RFID
            $rfidService = new RfidLabelService();
            $printJobsCreated = $rfidService->createPrintJobsForReceipt($receipt);

            DB::commit();

            // Construir mensaje de éxito
            $message = "Recepción registrada exitosamente. {$totalReceived} piezas recibidas.";
            if ($hasIssues) {
                $message .= " ⚠️ Hay productos con problemas de calidad.";
            }
            $message .= " Número de recepción: {$receipt->receipt_number}";

            if ($printJobsCreated > 0) {
                $message .= " 🏷️ {$printJobsCreated} etiquetas RFID encoladas para impresión.";
            }

            return redirect()
                ->route('purchase-orders.show', $purchaseOrder)
                ->with('success', $message)
                ->with('print_jobs_count', $printJobsCreated)
                ->with('receipt_id', $receipt->id);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error al registrar recepción: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()
                ->with('error', 'Error al registrar la recepción: ' . $e->getMessage())
                ->withInput();
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
                'unit_price' => $product->price ?? 0,
            ]
        ]);
    }

    /**
     * Búsqueda AJAX de productos para órdenes de compra
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }
            
            $products = Product::where(function($q) use ($query) {
                    $q->where('code', 'like', "%{$query}%")
                      ->orWhere('name', 'like', "%{$query}%");
                })
                ->select('id', 'code', 'name', 'description')
                ->orderBy('code')
                ->limit(50)
                ->get()
                ->map(function($product) {
                    return [
                        'id' => $product->id,
                        'code' => $product->code,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => 0
                    ];
                });
            
            return response()->json($products);
            
        } catch (\Exception $e) {
            \Log::error('Error en búsqueda de productos: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ========================================
    // MÉTODOS PRIVADOS / HELPERS
    // ========================================

    private function generateOrderNumber(): string
    {
        $today = now()->format('Ymd');
        
        $count = PurchaseOrder::where('order_number', 'like', "OC-{$today}-%")
            ->count();
        
        $nextNumber = $count + 1;
        $suffix = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        return "OC-{$today}-{$suffix}";
    }

    private function generateEPC(): string
    {
        $length = 24; 
        
        do {
            $epc = Str::random($length, '0123456789abcdef'); 
            $exists = ProductUnit::where('epc', $epc)->exists();
        } while ($exists);

        return strtoupper($epc);
    }

    private function generateSerialNumber(Product $product): string
    {
        $prefix = $product->code ? $product->code . '-' : 'SN-';
        
        $count = ProductUnit::where('serial_number', 'like', "{$prefix}%")
            ->count();
        
        $nextNumber = $count + 1;
        $suffix = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $suffix;
    }
}