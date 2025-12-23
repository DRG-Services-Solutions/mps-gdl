<?php

namespace App\Http\Controllers;

use App\Models\PreAssembledPackage;
use App\Models\SurgicalChecklist;
use App\Models\StorageLocation;
use App\Models\ProductUnit;
use App\Models\PreAssembledContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreAssembledPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PreAssembledPackage::query()
            ->with(['surgeryChecklist', 'storageLocation', 'creator']);

        // Filtro por tipo de cirugía
        if ($request->filled('checklist_id')) {
            $query->where('surgery_checklist_id', $request->checklist_id);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Búsqueda
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'peps') {
            $query->orderBy('last_used_at', 'asc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $packages = $query->paginate(15);

        $checklists = SurgicalChecklist::active()
            ->select('id', 'code', 'name')
            ->get();

        return view('pre-assembled.index', compact('packages', 'checklists'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $checklists = SurgicalChecklist::active()
            ->select('id', 'code', 'name', 'surgery_type')
            ->get();

        $locations = StorageLocation::orderBy('name')->get();

        return view('pre-assembled.create', compact('checklists', 'locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:pre_assembled_packages,code|max:50',
            'name' => 'required|string|max:255',
            'surgery_checklist_id' => 'required|exists:surgical_checklists,id',
            'package_epc' => 'nullable|string|unique:pre_assembled_packages,package_epc',
            'storage_location_id' => 'nullable|exists:storage_locations,id',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'available';

        $package = PreAssembledPackage::create($validated);

        return redirect()
            ->route('pre-assembled.show', $package)
            ->with('success', 'Paquete pre-armado creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PreAssembledPackage $preAssembled)
    {
        $preAssembled->load([
            'surgeryChecklist',
            'contents.product',
            'contents.productUnit',
            'contents.addedBy',
            'storageLocation',
            'creator'
        ]);

        // Resumen de contenido agrupado
        $contentSummary = $preAssembled->getContentSummary();

        // Productos con caducidad próxima
        $expiringSoon = $preAssembled->contents()
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<=', now()->addDays(30))
            ->where('expiration_date', '>=', now())
            ->with('product')
            ->orderBy('expiration_date')
            ->get();

        // Productos caducados
        $expired = $preAssembled->contents()
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<', now())
            ->with('product')
            ->get();

        return view('pre-assembled.show', compact(
            'preAssembled',
            'contentSummary',
            'expiringSoon',
            'expired'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PreAssembledPackage $preAssembled)
    {
        $checklists = SurgicalChecklist::active()
            ->select('id', 'code', 'name', 'surgery_type')
            ->get();

        $locations = StorageLocation::orderBy('name')->get();

        return view('pre-assembled.edit', compact('preAssembled', 'checklists', 'locations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:pre_assembled_packages,code,' . $preAssembled->id,
            'name' => 'required|string|max:255',
            'surgery_checklist_id' => 'required|exists:surgical_checklists,id',
            'package_epc' => 'nullable|string|unique:pre_assembled_packages,package_epc,' . $preAssembled->id,
            'storage_location_id' => 'nullable|exists:storage_locations,id',
            'status' => 'required|in:available,in_preparation,in_surgery,maintenance',
            'notes' => 'nullable|string',
        ]);

        $preAssembled->update($validated);

        return redirect()
            ->route('pre-assembled.show', $preAssembled)
            ->with('success', 'Paquete pre-armado actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PreAssembledPackage $preAssembled)
    {
        // Verificar que no esté en uso
        if ($preAssembled->status !== 'available') {
            return back()->with('error', 'No se puede eliminar un paquete que está en uso.');
        }

        // Liberar productos del paquete
        ProductUnit::where('current_package_id', $preAssembled->id)
            ->update([
                'current_status' => 'in_stock',
                'current_package_id' => null,
            ]);

        $preAssembled->delete();

        return redirect()
            ->route('pre-assembled.index')
            ->with('success', 'Paquete pre-armado eliminado exitosamente.');
    }

    /**
     * Agregar producto al paquete (Escaneo RFID o Manual)
     */
    public function addProduct(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'product_unit_id' => 'required|exists:product_units,id',
        ]);

        DB::beginTransaction();
        try {
            $productUnit = ProductUnit::findOrFail($validated['product_unit_id']);

            // Verificar que la unidad esté disponible
            if ($productUnit->current_status !== 'in_stock') {
                return back()->with('error', 'El producto no está disponible en almacén.');
            }

            // Agregar al contenido del paquete
            PreAssembledContent::create([
                'package_id' => $preAssembled->id,
                'product_id' => $productUnit->product_id,
                'product_unit_id' => $productUnit->id,
                'quantity' => 1,
                'added_at' => now(),
                'added_by' => auth()->id(),
                'expiration_date' => $productUnit->expiration_date,
                'entry_date' => $productUnit->entry_date,
            ]);

            // Actualizar estado del product_unit
            $productUnit->update([
                'current_status' => 'in_pre_assembled',
                'current_package_id' => $preAssembled->id,
            ]);

            DB::commit();

            return back()->with('success', 'Producto agregado al paquete exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al agregar producto: ' . $e->getMessage());
        }
    }

    /**
     * Remover producto del paquete
     */
    public function removeProduct(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'content_id' => 'required|exists:pre_assembled_contents,id',
        ]);

        DB::beginTransaction();
        try {
            $content = PreAssembledContent::findOrFail($validated['content_id']);

            // Verificar que pertenece al paquete
            if ($content->package_id !== $preAssembled->id) {
                return back()->with('error', 'El producto no pertenece a este paquete.');
            }

            // Actualizar estado del product_unit
            $content->productUnit->update([
                'current_status' => 'in_stock',
                'current_package_id' => null,
            ]);

            // Eliminar del contenido
            $content->delete();

            DB::commit();

            return back()->with('success', 'Producto removido del paquete exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al remover producto: ' . $e->getMessage());
        }
    }

    /**
     * Escaneo masivo de productos (RFID)
     */
    public function bulkScan(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'epcs' => 'required|array',
            'epcs.*' => 'required|string',
        ]);

        $results = [
            'success' => [],
            'errors' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($validated['epcs'] as $epc) {
                $productUnit = ProductUnit::where('epc', $epc)->first();

                if (!$productUnit) {
                    $results['errors'][] = "EPC {$epc}: No encontrado";
                    continue;
                }

                if ($productUnit->current_status !== 'in_stock') {
                    $results['errors'][] = "EPC {$epc}: No disponible";
                    continue;
                }

                // Agregar al paquete
                PreAssembledContent::create([
                    'package_id' => $preAssembled->id,
                    'product_id' => $productUnit->product_id,
                    'product_unit_id' => $productUnit->id,
                    'quantity' => 1,
                    'added_at' => now(),
                    'added_by' => auth()->id(),
                    'expiration_date' => $productUnit->expiration_date,
                    'entry_date' => $productUnit->entry_date,
                ]);

                $productUnit->update([
                    'current_status' => 'in_pre_assembled',
                    'current_package_id' => $preAssembled->id,
                ]);

                $results['success'][] = "EPC {$epc}: Agregado";
            }

            DB::commit();

            $message = count($results['success']) . ' productos agregados exitosamente.';
            if (count($results['errors']) > 0) {
                $message .= ' ' . count($results['errors']) . ' errores encontrados.';
            }

            return back()->with('success', $message)->with('scan_results', $results);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error en escaneo masivo: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado del paquete
     */
    public function updateStatus(Request $request, PreAssembledPackage $preAssembled)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,in_preparation,in_surgery,maintenance',
        ]);

        $preAssembled->updateStatus($validated['status']);

        return back()->with('success', 'Estado del paquete actualizado exitosamente.');
    }
}