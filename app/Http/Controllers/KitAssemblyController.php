<?php

namespace App\Http\Controllers;

// 🚨 ACTUALIZAMOS LOS MODELOS A LA NUEVA ARQUITECTURA
use App\Models\ProductUnit; 
use App\Models\KitAssembly;
use App\Models\KitAssemblyItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KitAssemblyController extends Controller
{
    public function index()
    {
        // Actualizamos la relación a 'productUnit.item'
        $recentAssemblies = KitAssembly::with('productUnit.item')
                            ->latest()
                            ->take(5)
                            ->get();

        return view('inventory.kits.index', compact('recentAssemblies'));
    }

    public function startAssembly(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        // Buscamos la unidad en la nueva tabla híbrida
        $unit = ProductUnit::where('serial_number', $request->code)->first();

        if (!$unit) {
            return back()->with('error', 'Alerta WMS: No se encontró ninguna charola física con ese Número de Serie.');
        }

        return redirect()->route('kits.assemble', ['code' => $unit->serial_number]);
    }

    /**
     * Paso 1: Iniciar el proceso de armado
     */
    public function start($code)
    {
        // 1. Buscamos por EPC (Tag RFID) o Número de Serie
        $unit = ProductUnit::where('epc', $code)
                           ->orWhere('serial_number', $code)
                           ->firstOrFail();

        // 2. BLINDAJE ARQUITECTÓNICO: ¿Es realmente una charola?
        // Validamos que el Item maestro sea de tipo kit, set o tray
        if (!$unit->item || !in_array($unit->item->type, ['kit', 'set', 'tray'])) {
            return redirect()->route('inventory.kits.index')
                             ->with('error', 'El código escaneado no pertenece a una charola armable.');
        }

        // 3. Magia Relacional: Traemos la receta desde el modelo Item
        // Usamos la relación 'components' y 'componentItem' que creamos en tu ItemComponentController
        $bom = $unit->item->components()->with('componentItem')->get();

        // Creamos la sesión de armado
        $assembly = KitAssembly::create([
            'product_unit_id' => $unit->id, // Actualizado a la nueva Foreign Key
            'user_id' => auth()->id(),
            'status' => 'in_progress'
        ]);

        return view('inventory.kits.assemble', compact('unit', 'bom', 'assembly'));
    }

    /**
     * Paso 2: Procesar el checklist y finalizar
     */
    public function finalize(Request $request, KitAssembly $assembly)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.qty' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $hasDiscrepancies = false;

            // Recorremos los IDs de los instrumentos (component_item_id)
            foreach ($request->items as $componentItemId => $data) {
                
                // Buscamos la regla en la receta de la charola
                $bomItem = $assembly->productUnit->item->components()
                                    ->where('component_item_id', $componentItemId)
                                    ->first();

                $qtyExpected = $bomItem ? $bomItem->quantity : 0;
                $qtyFound = (int)$data['qty'];

                // Validación de discrepancia basada en la bandera is_mandatory
                if ($bomItem && $bomItem->is_mandatory && $qtyFound < $qtyExpected) {
                    $hasDiscrepancies = true;
                }

                // Guardamos el detalle
                KitAssemblyItem::create([
                    'kit_assembly_id' => $assembly->id,
                    'component_item_id' => $componentItemId, // Ajustado a la tabla de items
                    'quantity_expected' => $qtyExpected,
                    'quantity_found' => $qtyFound,
                    'serial_numbers' => $data['serials'] ?? null, 
                ]);
            }

            // Actualizamos la sesión
            $assembly->update([
                'status' => $hasDiscrepancies ? 'with_discrepancies' : 'completed',
                'notes' => $request->notes,
                'completed_at' => now(),
            ]);

            // ACTUALIZACIÓN DE STOCK FÍSICO
            // Usamos tu convención 'available' que definimos en la vista show.blade.php
            $assembly->productUnit->update([
                'status' => 'available', 
                'last_inspection_at' => now()
            ]);

            DB::commit();

            $msg = $hasDiscrepancies 
                ? 'Kit armado con faltantes, pero registrado en el sistema.' 
                : 'Charola armada y validada correctamente para su esterilización/uso.';

            return redirect()->route('inventory.kits.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error crítico al procesar el armado: ' . $e->getMessage());
        }
    }
}