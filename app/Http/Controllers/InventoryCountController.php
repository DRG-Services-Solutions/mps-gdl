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
use Illuminate\Support\Facades\Validator;

class InventoryCountController extends Controller
{
    /**
     * Listado de tomas de inventario
     */
    public function index(Request $request)
    {
        $query = InventoryCount::with(['legalEntity', 'subWarehouse', 'createdBy', 'assignedTo']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('legal_entity_id')) {
            $query->where('legal_entity_id', $request->legal_entity_id);
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
            'legal_entity_id' => 'required|exists:legal_entities,id',
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
                'legal_entity_id' => $validated['legal_entity_id'],
                'sub_warehouse_id' => $validated['sub_warehouse_id'] ?? null,
                'storage_location_id' => $validated['storage_location_id'] ?? null,
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => InventoryCount::STATUS_DRAFT,
                'created_by' => auth()->id(),
            ]);

            // Generar items esperados
            $itemsGenerated = $inventoryCount->generateExpectedItems();

            DB::commit();

            return redirect()->route('inventory-counts.show', $inventoryCount)
                ->with('success', "Toma de inventario creada exitosamente. Se generaron {$itemsGenerated} productos para contar.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear toma de inventario: ' . $e->getMessage());
            
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
            'legalEntity',
            'subWarehouse',
            'storageLocation',
            'createdBy',
            'assignedTo',
            'approvedBy',
            'items.product',
            'adjustments',
        ]);

        // Estadísticas de items
        $itemStats = [
            'total' => $inventoryCount->items->count(),
            'pending' => $inventoryCount->items->where('status', 'pending')->count(),
            'matched' => $inventoryCount->items->where('status', 'matched')->count(),
            'discrepancies' => $inventoryCount->items->whereIn('status', ['surplus', 'shortage', 'not_found', 'unexpected'])->count(),
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

        $inventoryCount->load(['items.product', 'legalEntity']);

        $items = $inventoryCount->items()
            ->with('product')
            ->orderByRaw("FIELD(status, 'pending', 'shortage', 'surplus', 'matched', 'not_found')")
            ->get();

        return view('inventory-counts.count', compact('inventoryCount', 'items'));
    }

    /**
     * Procesar escaneo (AJAX)
     */
    public function processScan(Request $request, InventoryCount $inventoryCount)
    {
        $validated = $request->validate([
            'scan_code' => 'required|string',
            'scan_type' => 'required|in:barcode,rfid,epc,manual',
        ]);

        $scanCode = trim($validated['scan_code']);
        $scanType = $validated['scan_type'];

        // Buscar producto por código de barras, EPC, o código
        $product = null;
        $productUnit = null;

        if ($scanType === 'rfid' || $scanType === 'epc') {
            // Buscar por EPC
            $productUnit = ProductUnit::where('epc', $scanCode)->first();
            if ($productUnit) {
                $product = $productUnit->product;
            }
        } else {
            // Buscar por código de barras o código de producto
            $product = Product::where('code', $scanCode)
                ->orWhere('barcode', $scanCode)
                ->first();

            if (!$product) {
                // Intentar buscar por serial_number en ProductUnit
                $productUnit = ProductUnit::where('serial_number', $scanCode)->first();
                if ($productUnit) {
                    $product = $productUnit->product;
                }
            }
        }

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado para el código: ' . $scanCode,
                'code' => $scanCode,
            ], 404);
        }

        // Buscar o crear item en el conteo
        $item = $inventoryCount->items()->where('product_id', $product->id)->first();

        if (!$item) {
            // Producto no esperado - crear item como "unexpected"
            $item = $inventoryCount->items()->create([
                'product_id' => $product->id,
                'product_code' => $product->code,
                'product_name' => $product->name,
                'expected_quantity' => 0,
                'counted_quantity' => 0,
                'difference' => 0,
                'status' => InventoryCountItem::STATUS_UNEXPECTED,
            ]);
        }

        // Registrar escaneo
        $item->recordScan($scanCode, $scanType, auth()->id());

        return response()->json([
            'success' => true,
            'message' => "Escaneado: {$product->name}",
            'item' => [
                'id' => $item->id,
                'product_code' => $item->product_code,
                'product_name' => $item->product_name,
                'expected_quantity' => $item->expected_quantity,
                'counted_quantity' => $item->counted_quantity,
                'difference' => $item->difference,
                'status' => $item->status,
                'status_label' => $item->status_label,
                'status_color' => $item->status_color,
            ],
        ]);
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
            'item' => [
                'id' => $item->id,
                'counted_quantity' => $item->counted_quantity,
                'difference' => $item->difference,
                'status' => $item->status,
                'status_label' => $item->status_label,
                'status_color' => $item->status_color,
            ],
        ]);
    }

    /**
     * Marcar item como no encontrado
     */
    public function markNotFound(InventoryCount $inventoryCount, InventoryCountItem $item)
    {
        if ($item->inventory_count_id !== $inventoryCount->id) {
            return response()->json(['success' => false, 'message' => 'Item no pertenece a este inventario'], 403);
        }

        $item->markAsNotFound(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Item marcado como no encontrado',
            'item' => [
                'id' => $item->id,
                'status' => $item->status,
                'status_label' => $item->status_label,
            ],
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
     * Completar conteo
     */
    public function complete(InventoryCount $inventoryCount)
    {
        // Verificar que todos los items hayan sido contados
        $pendingCount = $inventoryCount->items()->where('status', 'pending')->count();

        if ($pendingCount > 0) {
            return redirect()->back()
                ->with('error', "Aún hay {$pendingCount} producto(s) pendientes de contar.");
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

        $inventoryCount->load(['legalEntity', 'items.product']);

        // Solo items con discrepancia
        $discrepancies = $inventoryCount->items()
            ->whereIn('status', ['surplus', 'shortage', 'not_found', 'unexpected'])
            ->with('product')
            ->orderBy('status')
            ->get();

        // Items que coinciden
        $matched = $inventoryCount->items()
            ->where('status', 'matched')
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
            ->whereIn('status', ['surplus', 'shortage', 'not_found', 'unexpected'])
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
        $inventoryCount->load(['legalEntity']);

        $adjustments = $inventoryCount->adjustments()
            ->with(['product', 'createdBy', 'approvedBy'])
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
            'legalEntity',
            'subWarehouse',
            'storageLocation',
            'createdBy',
            'approvedBy',
            'items.product',
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
            'legalEntity',
            'subWarehouse',
            'storageLocation',
            'createdBy',
            'approvedBy',
            'items.product',
            'adjustments.product',
        ]);

        // Usar biblioteca de PDF (dompdf, snappy, etc.)
        // Por ahora retornamos la vista
        return view('inventory-counts.report-pdf', compact('inventoryCount'));
    }
}
