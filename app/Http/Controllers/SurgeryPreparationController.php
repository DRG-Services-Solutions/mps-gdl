<?php

namespace App\Http\Controllers;
use App\Models\ScheduledSurgery;
use App\Models\SurgeryPreparation;
use App\Models\SurgeryPreparationItem;
use App\Models\SurgeryPreparationUnit;
use App\Models\PreAssembledPackage;
use App\Models\PreAssembledContent;
use App\Models\ProductUnit;
use App\Models\PackageContent;
use Illuminate\Support\Facades\Log;
use App\Services\PreparationService;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurgeryPreparationController extends Controller
{
    protected $preparationService;

    public function __construct(PreparationService $service)
    {
        $this->preparationService = $service;
    }


    /**
     * Iniciar preparación de cirugía
     */
    public function startPreparation(Request $request, ScheduledSurgery $surgery)
    {
        try {
            $preparation = $this->preparationService->createPreparation(
                $surgery->id,
                $request->package_id,
                auth()->id()
            );

            return redirect()->route('surgeries.preparations.picking', $surgery)
                ->with('success', 'Hoja de trabajo generada y paquete asignado.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
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
        $availablePackages = PreAssembledPackage::available()
            ->forSurgeryType($surgery->checklist_id)
            ->with(['contents.product', 'storageLocation'])
            ->get()
            ->map(function($package) use ($surgery) {
                $package->completeness = $package->getCompletenessPercentage($surgery->checklist_id);
                
                // Verificar productos caducados
                $package->has_expired = $package->hasExpiredProducts();
                
                return $package;
            })
            ->sortByDesc('completeness');

        return view('surgeries.preparations.select-package', compact('surgery', 'availablePackages'));
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
        $product = PackageContent::GroupByPackage($preparation->pre_assembled_package_id)->get();

        // Agrupar items por estado
        $itemsComplete = $preparation->items()->where('status', 'in_package')->get();
        $itemsPending = $preparation->items()->where('status', '!=', 'in_package')->get();

        return view('surgeries.preparations.compare', compact(
            'surgery',
            'preparation',
            'itemsComplete',
            'itemsPending',
            'product'
        ));
    }

    /**
     * Vista de surtido de faltantes
     */
    public function picking(ScheduledSurgery $surgery, SurgeryPreparation $preparation)
    {
        $preparation->load([
            'items.product',
            'items.units.productUnit',
            'preAssembledPackage'
        ]);

        $pendingItems = $preparation->items->where('quantity_missing', '>', 0);
        if ($preparation->status === 'comparing') {
            $preparation->update(['status' => 'picking']);
        }

        return view('surgeries.preparations.picking', compact('preparation', 'pendingItems', 'surgery'));
    }

    /**
     * Agregar producto surtido (Escaneo RFID)
     */
    public function addPickedProduct(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'epc' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // 1. Buscar la unidad física
            $productUnit = ProductUnit::where('epc', $validated['epc'])->firstOrFail();
            
            // 2. Buscar si la preparación necesita este producto
            $prepItem = $surgery->preparation->items()
                ->where('product_id', $productUnit->product_id)
                ->where('quantity_missing', '>', 0)
                ->first();

            if (!$prepItem) {
                return response()->json(['success' => false, 'message' => 'Este producto no es requerido o ya está completo.'], 400);
            }

            // 3. Registrar el surtido (Lógica delegada al modelo o servicio)
            $prepItem->addUnit($productUnit, auth()->id());

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Producto detectado: ' . $productUnit->product->name,
                'item_id' => $prepItem->id,
                'quantity_picked' => $prepItem->fresh()->quantity_picked,
                'all_complete' => $surgery->preparation->getCompletenessPercentage() >= 100
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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

    public function store(Request $request, PreparationService $service)
    {
        $request->validate([
            'scheduled_surgery_id' => 'required|exists:scheduled_surgeries,id',
            'pre_assembled_package_id' => 'required|exists:pre_assembled_packages,id',
        ]);

        $preparation = $service->createPreparation(
            $request->scheduled_surgery_id,
            $request->pre_assembled_package_id,
            auth()->id()
        );

        return redirect()->route('surgeries.preparations.compare', $preparation->id)
                        ->with('success', 'Hoja de trabajo generada correctamente');
    }
}