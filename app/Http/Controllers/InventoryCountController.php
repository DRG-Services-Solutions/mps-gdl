<?php

namespace App\Http\Controllers;

use App\Models\InventoryCount;
use App\Models\InventoryCountItem;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\LegalEntity;
use App\Models\SubWarehouse;
use App\Models\StorageLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryCountController extends Controller
{
    /**
     * Listado de tomas de inventario
     */
    public function index(Request $request)
    {
        $query = InventoryCount::with(['legalEntities', 'subWarehouse', 'createdBy', 'assignedTo']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('legal_entity_id')) {
            $query->whereHas('legalEntities', function ($q) use ($request) {
                $q->where('legal_entities.id', $request->legal_entity_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('count_number', 'like', "%{$request->search}%");
        }

        $inventoryCounts = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $legalEntities = LegalEntity::orderBy('name')->get();

        // Estadísticas rápidas
        $stats = [
            'draft' => InventoryCount::draft()->count(),
            'in_progress' => InventoryCount::inProgress()->count(),
            'pending_review' => InventoryCount::pendingReview()->count(),
            'approved_this_month' => InventoryCount::approved()
                ->whereMonth('approved_at', now()->month)
                ->count(),
        ];

        return view('inventory-counts.index', compact('inventoryCounts', 'legalEntities', 'stats'));
    }

    /**
     * Formulario para crear nueva toma de inventario
     */
    public function create()
    {
        $legalEntities = LegalEntity::orderBy('name')->get();
        $subWarehouses = SubWarehouse::orderBy('name')->get();
        $storageLocations = StorageLocation::orderBy('name')->get();

        return view('inventory-counts.create', compact('legalEntities', 'subWarehouses', 'storageLocations'));
    }

    /**
     * Guardar nueva toma de inventario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:full,partial,cyclic,spot_check',
            'method' => 'required|in:rfid_bulk,rfid_handheld,barcode_scan,manual',
            'legal_entity_ids' => 'required|array|min:1',
            'legal_entity_ids.*' => 'exists:legal_entities,id',
            'sub_warehouse_id' => 'nullable|exists:sub_warehouses,id',
            'storage_location_id' => 'nullable|exists:storage_locations,id',
            'scheduled_at' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $inventoryCount = InventoryCount::create([
                'type' => $validated['type'],
                'method' => $validated['method'],
                'sub_warehouse_id' => $validated['sub_warehouse_id'] ?? null,
                'storage_location_id' => $validated['storage_location_id'] ?? null,
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => InventoryCount::STATUS_DRAFT,
                'created_by' => auth()->id(),
            ]);

            // Asociar Legal Entities (múltiples)
            $inventoryCount->legalEntities()->attach($validated['legal_entity_ids']);

            // Generar items esperados basados en ProductUnits
            $itemsGenerated = $inventoryCount->generateExpectedItems();

            DB::commit();

            return redirect()->route('inventory-counts.show', $inventoryCount)
                ->with('success', "Toma de inventario creada exitosamente. Se generaron {$itemsGenerated} unidades para verificar.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear toma de inventario: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la toma de inventario: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de toma de inventario
     */
    public function show(InventoryCount $inventoryCount)
    {
        $inventoryCount->load([
            'legalEntities',
            'subWarehouse',
            'storageLocation',
            'createdBy',
            'assignedTo',
            'approvedBy',
            'items.product',
            'items.productUnit',
            'adjustments',
        ]);

        $itemStats = [
            'total' => $inventoryCount->items->count(),
            'pending' => $inventoryCount->items->where('status', 'pending')->count(),
            'found' => $inventoryCount->items->whereIn('status', ['found', 'matched'])->count(),
            'discrepancies' => $inventoryCount->items->whereIn('status', ['surplus', 'missing', 'wrong_location', 'damaged', 'expired'])->count(),
        ];

        return view('inventory-counts.show', compact('inventoryCount', 'itemStats'));
    }

    /**
     * Iniciar conteo
     */
    public function start(InventoryCount $inventoryCount)
    {
        if (!$inventoryCount->start()) {
            return redirect()->back()->with('error', 'No se puede iniciar el conteo en este momento.');
        }

        return redirect()->route('inventory-counts.count', $inventoryCount)
            ->with('success', 'Conteo iniciado. ¡Comienza a escanear!');
    }

    /**
     * Pantalla de conteo (optimizada para móvil/tablet)
     */
    public function count(InventoryCount $inventoryCount)
    {
        if (!in_array($inventoryCount->status, [InventoryCount::STATUS_DRAFT, InventoryCount::STATUS_IN_PROGRESS])) {
            return redirect()->route('inventory-counts.show', $inventoryCount)
                ->with('error', 'Este inventario no está en proceso de conteo.');
        }

        // Si aún está en borrador, iniciarlo
        if ($inventoryCount->status === InventoryCount::STATUS_DRAFT) {
            $inventoryCount->start();
        }

        $inventoryCount->load(['items.product', 'items.productUnit', 'legalEntities']);

        $items = $inventoryCount->items()
            ->with(['product', 'productUnit'])
            ->orderByRaw("FIELD(status, 'pending', 'missing', 'surplus', 'found', 'matched', 'damaged', 'expired')")
            ->get();

        return view('inventory-counts.count', compact('inventoryCount', 'items'));
    }

    /**
     * Procesar escaneo (AJAX)
     * Busca en ProductUnit por EPC, serial_number o código de barras del producto
     */
    public function processScan(Request $request, InventoryCount $inventoryCount)
    {
        $validated = $request->validate([
            'scan_code' => 'required|string',
            'scan_type' => 'required|in:barcode,rfid,epc,serial,manual',
        ]);

        $scanCode = trim($validated['scan_code']);
        $scanType = $validated['scan_type'];

        // Obtener IDs de legal entities del inventario
        $legalEntityIds = $inventoryCount->legalEntities->pluck('id')->toArray();

        // 1. Buscar en items esperados (por EPC o Serial)
        $item = $inventoryCount->items()
            ->where(function ($q) use ($scanCode) {
                $q->where('expected_epc', $scanCode)
                  ->orWhere('expected_serial', $scanCode);
            })
            ->first();

        if ($item) {
            // Encontrado en la lista esperada
            $item->markAsFound($scanCode, $scanType, auth()->id());

            return response()->json([
                'success' => true,
                'message' => "✓ Encontrado: {$item->product_code}",
                'item' => $this->formatItemResponse($item),
                'action' => 'found',
            ]);
        }

        // 2. Buscar ProductUnit por EPC o Serial que no esté en la lista
        $productUnit = ProductUnit::with('product')
            ->where(function ($q) use ($scanCode) {
                $q->where('epc', $scanCode)
                  ->orWhere('serial_number', $scanCode);
            })
            ->whereIn('legal_entity_id', $legalEntityIds)
            ->first();

        if ($productUnit) {
            // ProductUnit existe pero no estaba esperada - es un SOBRANTE
            $item = $inventoryCount->items()->create([
                'product_unit_id' => $productUnit->id,
                'product_id' => $productUnit->product_id,
                'product_code' => $productUnit->product->code,
                'product_name' => $productUnit->product->name,
                'expected_epc' => null,
                'expected_serial' => null,
                'expected_batch' => $productUnit->batch_number,
                'scanned_epc' => ($scanType === 'rfid' || $scanType === 'epc') ? $scanCode : null,
                'scanned_serial' => ($scanType === 'serial') ? $scanCode : null,
                'expected_quantity' => 0,
                'counted_quantity' => 1,
                'difference' => 1,
                'status' => InventoryCountItem::STATUS_SURPLUS,
                'scanned_at' => now(),
                'scanned_by' => auth()->id(),
                'scan_method' => $scanType,
            ]);

            $inventoryCount->calculateSummary();

            return response()->json([
                'success' => true,
                'message' => "⚠ Sobrante: {$productUnit->product->code} (no esperado en esta ubicación)",
                'item' => $this->formatItemResponse($item),
                'action' => 'surplus',
            ]);
        }

        // 3. Buscar por código de barras del producto
        $product = Product::where('code', $scanCode)->first();


        if ($product) {
            // Encontramos el producto pero no una unidad específica
            // Buscar si hay items pendientes de este producto
            $pendingItem = $inventoryCount->items()
                ->where('product_id', $product->id)
                ->where('status', 'pending')
                ->first();

            if ($pendingItem) {
                $pendingItem->markAsFound($scanCode, 'barcode', auth()->id());

                return response()->json([
                    'success' => true,
                    'message' => "✓ Encontrado: {$product->code}",
                    'item' => $this->formatItemResponse($pendingItem),
                    'action' => 'found',
                ]);
            }

            // No hay pendientes de este producto - posible sobrante
            return response()->json([
                'success' => false,
                'message' => "Producto {$product->code} encontrado pero no hay unidades pendientes de verificar",
                'code' => $scanCode,
                'product' => [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                ],
            ], 200);
        }

        // 4. No encontrado en ninguna parte
        return response()->json([
            'success' => false,
            'message' => 'Código no encontrado: ' . $scanCode,
            'code' => $scanCode,
        ], 404);
    }

    /**
     * Formatear respuesta de item para JSON
     */
    private function formatItemResponse(InventoryCountItem $item): array
    {
        return [
            'id' => $item->id,
            'product_code' => $item->product_code,
            'product_name' => $item->product_name,
            'expected_epc' => $item->expected_epc,
            'expected_serial' => $item->expected_serial,
            'expected_quantity' => $item->expected_quantity,
            'counted_quantity' => $item->counted_quantity,
            'difference' => $item->difference,
            'status' => $item->status,
            'status_label' => $item->status_label,
            'status_color' => $item->status_color,
            'identifier' => $item->identifier,
        ];
    }

    /**
     * Actualizar cantidad manual de un item
     */
    public function updateItemQuantity(Request $request, InventoryCount $inventoryCount, InventoryCountItem $item)
    {
        if ($item->inventory_count_id !== $inventoryCount->id) {
            return response()->json(['success' => false, 'message' => 'Item no pertenece a este inventario'], 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $item->recordCount($validated['quantity'], auth()->id(), 'manual');

        return response()->json([
            'success' => true,
            'message' => 'Cantidad actualizada',
            'item' => $this->formatItemResponse($item),
        ]);
    }

    /**
     * Marcar item como faltante (no encontrado)
     */
    public function markNotFound(InventoryCount $inventoryCount, InventoryCountItem $item)
    {
        if ($item->inventory_count_id !== $inventoryCount->id) {
            return response()->json(['success' => false, 'message' => 'Item no pertenece a este inventario'], 403);
        }

        $item->markAsMissing(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Item marcado como faltante',
            'item' => $this->formatItemResponse($item),
        ]);
    }

    /**
     * Recontar item
     */
    public function recountItem(InventoryCount $inventoryCount, InventoryCountItem $item)
    {
        if ($item->inventory_count_id !== $inventoryCount->id) {
            return response()->json(['success' => false, 'message' => 'Item no pertenece a este inventario'], 403);
        }

        $item->recount();

        return response()->json([
            'success' => true,
            'message' => 'Item listo para recontar',
        ]);
    }

    /**
     * Marcar item como dañado
     */
    public function markDamaged(Request $request, InventoryCount $inventoryCount, InventoryCountItem $item)
    {
        if ($item->inventory_count_id !== $inventoryCount->id) {
            return response()->json(['success' => false, 'message' => 'Item no pertenece a este inventario'], 403);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:500',
        ]);

        $item->markAsDamaged($validated['description'], auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Item marcado como dañado',
            'item' => $this->formatItemResponse($item),
        ]);
    }

    /**
     * Completar conteo
     */
    public function complete(InventoryCount $inventoryCount)
    {
        // Marcar items pendientes como faltantes
        $pendingItems = $inventoryCount->items()->where('status', 'pending')->get();
        
        foreach ($pendingItems as $item) {
            $item->markAsMissing(auth()->id());
        }

        if (!$inventoryCount->complete()) {
            return redirect()->back()->with('error', 'No se puede completar el conteo.');
        }

        return redirect()->route('inventory-counts.review', $inventoryCount)
            ->with('success', 'Conteo completado. Revisa las discrepancias.');
    }

    /**
     * Pantalla de revisión de discrepancias
     */
    public function review(InventoryCount $inventoryCount)
    {
        if ($inventoryCount->status !== InventoryCount::STATUS_PENDING_REVIEW) {
            return redirect()->route('inventory-counts.show', $inventoryCount);
        }

        $inventoryCount->load(['legalEntities', 'items.product', 'items.productUnit']);

        // Solo items con discrepancia
        $discrepancies = $inventoryCount->items()
            ->whereIn('status', ['surplus', 'missing', 'wrong_location', 'damaged', 'expired'])
            ->with(['product', 'productUnit'])
            ->orderBy('status')
            ->get();

        // Items que coinciden
        $matched = $inventoryCount->items()
            ->whereIn('status', ['found', 'matched'])
            ->with('product')
            ->get();

        return view('inventory-counts.review', compact('inventoryCount', 'discrepancies', 'matched'));
    }

    /**
     * Justificar discrepancia
     */
    public function justifyDiscrepancy(Request $request, InventoryCount $inventoryCount, InventoryCountItem $item)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $item->justifyDiscrepancy($validated['reason'], auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Discrepancia justificada',
        ]);
    }

    /**
     * Generar ajustes automáticos
     */
    public function generateAdjustments(InventoryCount $inventoryCount)
    {
        if ($inventoryCount->status !== InventoryCount::STATUS_PENDING_REVIEW) {
            return redirect()->back()->with('error', 'No se pueden generar ajustes en este estado.');
        }

        $discrepancies = $inventoryCount->items()
            ->whereIn('status', ['surplus', 'missing', 'damaged', 'expired'])
            ->get();

        $adjustmentsCreated = 0;

        foreach ($discrepancies as $item) {
            if ($item->requiresAdjustment()) {
                $item->createAdjustment();
                $adjustmentsCreated++;
            }
        }

        return redirect()->route('inventory-counts.adjustments', $inventoryCount)
            ->with('success', "Se generaron {$adjustmentsCreated} ajustes pendientes de aprobación.");
    }

    /**
     * Ver ajustes del inventario
     */
    public function adjustments(InventoryCount $inventoryCount)
    {
        $inventoryCount->load(['legalEntities']);

        $adjustments = $inventoryCount->adjustments()
            ->with(['product', 'productUnit', 'createdBy', 'approvedBy'])
            ->orderBy('status')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingCount = $adjustments->where('status', 'pending')->count();

        return view('inventory-counts.adjustments', compact('inventoryCount', 'adjustments', 'pendingCount'));
    }

    /**
     * Aprobar ajuste individual
     */
    public function approveAdjustment(InventoryAdjustment $adjustment)
    {
        if (!$adjustment->approve(auth()->id())) {
            return redirect()->back()->with('error', 'No se pudo aprobar el ajuste.');
        }

        return redirect()->back()->with('success', "Ajuste {$adjustment->adjustment_number} aprobado y aplicado.");
    }

    /**
     * Rechazar ajuste
     */
    public function rejectAdjustment(Request $request, InventoryAdjustment $adjustment)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if (!$adjustment->reject(auth()->id(), $validated['rejection_reason'])) {
            return redirect()->back()->with('error', 'No se pudo rechazar el ajuste.');
        }

        return redirect()->back()->with('success', "Ajuste {$adjustment->adjustment_number} rechazado.");
    }

    /**
     * Aprobar toma de inventario completa
     */
    public function approve(InventoryCount $inventoryCount)
    {
        // Verificar que todos los ajustes pendientes estén procesados
        $pendingAdjustments = $inventoryCount->adjustments()->where('status', 'pending')->count();

        if ($pendingAdjustments > 0) {
            return redirect()->back()
                ->with('error', "Hay {$pendingAdjustments} ajuste(s) pendientes de procesar.");
        }

        if (!$inventoryCount->approve(auth()->id())) {
            return redirect()->back()->with('error', 'No se puede aprobar el inventario.');
        }

        return redirect()->route('inventory-counts.show', $inventoryCount)
            ->with('success', 'Toma de inventario aprobada exitosamente.');
    }

    /**
     * Cancelar toma de inventario
     */
    public function cancel(Request $request, InventoryCount $inventoryCount)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        if (!$inventoryCount->cancel($validated['cancellation_reason'])) {
            return redirect()->back()->with('error', 'No se puede cancelar el inventario.');
        }

        return redirect()->route('inventory-counts.index')
            ->with('success', 'Toma de inventario cancelada.');
    }

    /**
     * Reporte de inventario
     */
    public function report(InventoryCount $inventoryCount)
    {
        $inventoryCount->load([
            'legalEntities',
            'subWarehouse',
            'storageLocation',
            'createdBy',
            'approvedBy',
            'items.product',
            'items.productUnit',
            'adjustments.product',
        ]);

        return view('inventory-counts.report', compact('inventoryCount'));
    }

    /**
     * Exportar reporte a PDF
     */
    public function exportPdf(InventoryCount $inventoryCount)
    {
        $inventoryCount->load([
            'legalEntities',
            'subWarehouse',
            'storageLocation',
            'createdBy',
            'approvedBy',
            'items.product',
            'items.productUnit',
            'adjustments.product',
        ]);

        return view('inventory-counts.report-pdf', compact('inventoryCount'));
    }
}
