<?php
// app/Http/Controllers/SurgeryPreparationController.php

namespace App\Http\Controllers;

use App\Models\ScheduledSurgery;
use App\Models\SurgeryPreparation;
use App\Models\SurgeryPreparationItem;
use App\Models\SurgeryPreparationUnit;
use App\Models\PreAssembledPackage;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurgeryPreparationController extends Controller
{
    /**
     * Iniciar preparación de cirugía
     */
    public function start(ScheduledSurgery $surgery)
    {
        // Verificar que no tenga preparación ya
        if ($surgery->preparation) {
            return redirect()
                ->route('surgeries.preparations.selectPackage', $surgery)
                ->with('info', 'Esta cirugía ya tiene una preparación iniciada.');
        }

        // Crear preparación
        $preparation = SurgeryPreparation::create([
            'scheduled_surgery_id' => $surgery->id,
            'status' => 'pending',
        ]);

        // Actualizar estado de la cirugía
        $surgery->updateStatus('in_preparation');

        return redirect()
            ->route('surgeries.preparations.selectPackage', $surgery)
            ->with('success', 'Preparación iniciada. Seleccione un paquete pre-armado.');
    }

    /**
     * Mostrar paquetes disponibles para seleccionar
     */
    public function selectPackage(ScheduledSurgery $surgery)
    {
        $surgery->load(['checklist', 'preparation']);

        if (!$surgery->preparation) {
            return redirect()
                ->route('surgeries.show', $surgery)
                ->with('error', 'Debe iniciar la preparación primero.');
        }

        // Obtener paquetes disponibles del mismo tipo de cirugía
        $packages = PreAssembledPackage::available()
            ->forSurgeryType($surgery->checklist_id)
            ->with(['contents.product', 'storageLocation'])
            ->get()
            ->map(function($package) use ($surgery) {
                // Calcular porcentaje de completitud
                $package->completeness = $package->getCompletenessPercentage($surgery->checklist_id);
                
                // Verificar productos caducados
                $package->has_expired = $package->hasExpiredProducts();
                
                return $package;
            })
            ->sortByDesc('completeness');

        return view('surgeries.preparations.select-package', compact('surgery', 'packages'));
    }

    /**
     * Asignar paquete pre-armado y hacer comparación
     */
    public function assignPackage(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:pre_assembled_packages,id',
        ]);

        $preparation = $surgery->preparation;
        
        if (!$preparation) {
            return back()->with('error', 'No hay preparación iniciada.');
        }

        $package = PreAssembledPackage::findOrFail($validated['package_id']);

        // Verificar que el paquete esté disponible
        if ($package->status !== 'available') {
            return back()->with('error', 'El paquete seleccionado no está disponible.');
        }

        DB::beginTransaction();
        try {
            // Asignar paquete
            $preparation->update([
                'pre_assembled_package_id' => $package->id,
                'status' => 'comparing',
                'started_at' => now(),
                'prepared_by' => auth()->id(),
            ]);

            // Cambiar estado del paquete
            $package->updateStatus('in_preparation');

            // COMPARACIÓN: Check List vs Pre-Armado
            $this->performComparison($surgery, $preparation, $package);

            DB::commit();

            return redirect()
                ->route('surgeries.preparations.compare', $surgery)
                ->with('success', 'Paquete asignado y comparación realizada.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al asignar paquete: ' . $e->getMessage());
        }
    }

    /**
     * Realizar comparación entre check list y paquete
     */
    private function performComparison($surgery, $preparation, $package)
    {
        // Obtener items del check list con condicionales aplicados
        $checklistItems = $surgery->getChecklistItemsWithConditionals();

        foreach ($checklistItems as $itemData) {
            $item = $itemData['item'];
            $requiredQty = $itemData['adjusted_quantity'];

            // Contar cuántos hay en el paquete
            $availableQty = $package->contents()
                ->where('product_id', $item->product_id)
                ->count();

            // Calcular faltante
            $missingQty = max(0, $requiredQty - $availableQty);

            // Obtener ubicación del producto en almacén
            $storageLocation = $item->product->productUnits()
                ->where('current_status', 'in_stock')
                ->whereNotNull('storage_location_id')
                ->with('storageLocation')
                ->first()
                ?->storageLocation;

            // Crear item de preparación
            $prepItem = SurgeryPreparationItem::create([
                'preparation_id' => $preparation->id,
                'product_id' => $item->product_id,
                'quantity_required' => $requiredQty,
                'is_mandatory' => $itemData['is_mandatory'],
                'quantity_in_package' => $availableQty,
                'quantity_missing' => $missingQty,
                'quantity_picked' => 0,
                'status' => $missingQty === 0 ? 'in_package' : 'pending',
                'storage_location_id' => $storageLocation?->id,
            ]);

            // Si hay productos en el paquete, crear registros de unidades
            if ($availableQty > 0) {
                $unitsInPackage = $package->contents()
                    ->where('product_id', $item->product_id)
                    ->take($requiredQty) // Solo los que se necesitan
                    ->get();

                foreach ($unitsInPackage as $content) {
                    SurgeryPreparationUnit::create([
                        'preparation_item_id' => $prepItem->id,
                        'product_unit_id' => $content->product_unit_id,
                        'source_type' => 'pre_assembled',
                        'source_package_id' => $package->id,
                        'assigned_at' => now(),
                        'assigned_by' => auth()->id(),
                    ]);
                }
            }
        }
    }

    /**
     * Mostrar comparación check list vs paquete
     */
    public function compare(ScheduledSurgery $surgery)
    {
        $surgery->load([
            'preparation.preAssembledPackage',
            'preparation.items.product',
            'preparation.items.storageLocation',
            'preparation.items.units.productUnit'
        ]);

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return redirect()
                ->route('surgeries.show', $surgery)
                ->with('error', 'No hay preparación para esta cirugía.');
        }

        // Agrupar items por estado
        $itemsComplete = $preparation->items()->where('status', 'in_package')->get();
        $itemsPending = $preparation->items()->where('status', '!=', 'in_package')->get();

        return view('surgeries.preparations.compare', compact(
            'surgery',
            'preparation',
            'itemsComplete',
            'itemsPending'
        ));
    }

    /**
     * Vista de surtido de faltantes
     */
    public function picking(ScheduledSurgery $surgery)
    {
        $surgery->load([
            'preparation.items' => function($q) {
                $q->where('quantity_missing', '>', 0);
            },
            'preparation.items.product',
            'preparation.items.storageLocation'
        ]);

        $preparation = $surgery->preparation;

        if (!$preparation) {
            return redirect()->route('surgeries.show', $surgery);
        }

        // Cambiar estado a picking
        if ($preparation->status === 'comparing') {
            $preparation->update(['status' => 'picking']);
        }

        return view('surgeries.preparations.picking', compact('surgery', 'preparation'));
    }

    /**
     * Agregar producto surtido (Escaneo RFID)
     */
    public function addPickedProduct(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'preparation_item_id' => 'required|exists:surgery_preparation_items,id',
            'epc' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $prepItem = SurgeryPreparationItem::findOrFail($validated['preparation_item_id']);

            // Buscar product unit por EPC
            $productUnit = ProductUnit::where('epc', $validated['epc'])->first();

            if (!$productUnit) {
                return response()->json([
                    'success' => false,
                    'message' => 'EPC no encontrado en el sistema.'
                ], 404);
            }

            // Verificar que sea del producto correcto
            if ($productUnit->product_id !== $prepItem->product_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El EPC no corresponde al producto requerido.'
                ], 400);
            }

            // Verificar que esté disponible
            if ($productUnit->current_status !== 'in_stock') {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto no está disponible en almacén.'
                ], 400);
            }

            // Verificar que no se exceda la cantidad
            if ($prepItem->quantity_picked >= $prepItem->quantity_missing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya se completó la cantidad requerida.'
                ], 400);
            }

            // Agregar unidad
            $prepItem->addPickedUnit($productUnit->id, 'warehouse', auth()->id());

            // Actualizar estado del product_unit
            $productUnit->update([
                'current_status' => 'in_pre_assembled',
                'current_package_id' => $surgery->preparation->pre_assembled_package_id,
            ]);

            // Agregar al contenido del paquete
            $surgery->preparation->preAssembledPackage->contents()->create([
                'package_id' => $surgery->preparation->pre_assembled_package_id,
                'product_id' => $productUnit->product_id,
                'product_unit_id' => $productUnit->id,
                'quantity' => 1,
                'added_at' => now(),
                'added_by' => auth()->id(),
                'expiration_date' => $productUnit->expiration_date,
                'entry_date' => $productUnit->entry_date,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado exitosamente.',
                'item' => $prepItem->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar preparación
     */
    public function verify(ScheduledSurgery $surgery)
    {
        $preparation = $surgery->preparation;

        if (!$preparation) {
            return redirect()->route('surgeries.show', $surgery);
        }

        // Verificar que esté completa
        if (!$preparation->isComplete()) {
            return back()->with('error', 'La preparación aún no está completa.');
        }

        DB::beginTransaction();
        try {
            // Completar preparación
            $preparation->complete(auth()->id());

            // Cambiar estado del paquete
            $preparation->preAssembledPackage->updateStatus('in_surgery');

            // Actualizar product units a "in_surgery"
            ProductUnit::where('current_package_id', $preparation->pre_assembled_package_id)
                ->update([
                    'current_status' => 'in_surgery',
                    'current_surgery_id' => $surgery->id,
                ]);

            DB::commit();

            return redirect()
                ->route('surgeries.show', $surgery)
                ->with('success', 'Preparación verificada y completada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al verificar: ' . $e->getMessage());
        }
    }

    /**
     * Resumen de preparación
     */
    public function summary(ScheduledSurgery $surgery)
    {
        $surgery->load([
            'preparation.items.product',
            'preparation.items.units.productUnit',
            'preparation.preAssembledPackage'
        ]);

        return view('surgeries.preparations.summary', compact('surgery'));
    }
}