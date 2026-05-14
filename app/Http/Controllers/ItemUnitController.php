<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ItemUnitController extends Controller
{
    /**
     * Almacena una nueva unidad física escaneada (RFID/SN) desde el Modal de Alta Física
     */
    public function store(Request $request, Item $item)
    {
        Log::info("=== ALTA FÍSICA RFID (INSTRUMENTAL) ===", [
            'item_id' => $item->id,
            'epc_leido' => $request->epc
        ]);

        // 1. Validación estricta WMS
        $validated = $request->validate([
            // Validamos que el Tag RFID no exista ya en todo el almacén (Mundo Descartable o Reusable)
            'epc'           => 'required|string|unique:product_units,epc|max:255',
            'serial_number' => 'nullable|string|max:100',
            // Opcional: validamos contra tu tabla de ubicaciones si el usuario seleccionó una
            'location_id'   => 'nullable|exists:storage_locations,id' 
        ], [
            'epc.unique' => 'Alerta WMS: Este Tag RFID ya está asociado a otro equipo o insumo en el almacén.'
        ]);

        // 2. Creación del activo físico usando tu modelo ProductUnit
        $unit = new ProductUnit([
            'epc'                 => trim($validated['epc']),
            'serial_number'       => trim($validated['serial_number']),
            'current_location_id' => $validated['location_id'] ?? null,
            'status'              => ProductUnit::STATUS_AVAILABLE,
            
            'max_sterilization_cycles' => $item->requires_maintenance ? $item->maintenance_interval_uses : null,
        ]);

        // 3. El puente arquitectónico: Atamos la unidad física al catálogo maestro
        $unit->item_id = $item->id;
        $unit->save();

        Log::info("Unidad física RFID creada con éxito", ['product_unit_id' => $unit->id]);

        // 4. Retornamos a la vista del expediente recargando los datos
        return redirect()->route('items.show', $item)
                         ->with('success', '¡Unidad física registrada y lista para operar en quirófano!');
    }
}