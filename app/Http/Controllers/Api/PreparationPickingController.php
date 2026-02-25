<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScheduledSurgery;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Services\PreparationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PreparationPickingController extends Controller
{
    protected $preparationService;

    public function __construct(PreparationService $service)
    {
        $this->preparationService = $service;
    }

    /**
     * Procesa cualquier entrada (Barcode o EPC) y lo registra.
     */
    public function process(Request $request, ScheduledSurgery $surgery)
    {
        $preparation = $surgery->preparation;
        if (!$preparation) {
            return response()->json(['success' => false, 'message' => 'No hay preparación activa'], 404);
        }

        try {
            // 1. Identificar la unidad
            $unit = $this->identifyUnit($request);

            if (!$unit) {
                return response()->json(['success' => false, 'message' => 'Unidad no encontrada o no disponible'], 404);
            }

            // 2. Registrar el picking usando tu servicio existente
            // (Asegúrate de que tu PreparationService maneje la reserva internamente)
            $result = $this->preparationService->pickProduct(
                $preparation->id,
                $unit->epc,
                auth()->id()
            );

            // 3. Respuesta estandarizada
            return response()->json([
                'success' => true,
                'message' => "✓ {$unit->product->name} registrado",
                'data' => array_merge($result, [
                    'unit_info' => [
                        'epc' => $unit->epc,
                        'batch' => $unit->batch_number,
                        'expiration' => $unit->expiration_date?->format('Y-m-d'),
                        'location' => $unit->currentLocation?->code
                    ]
                ])
            ]);

        } catch (\Exception $e) {
            Log::error("Error en Picking API: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    private function identifyUnit(Request $request)
    {
        // Si viene un EPC (Modo RFID)
        if ($request->has('epc')) {
            return ProductUnit::findByEPC($request->epc);
        }
        
        // Si viene un Barcode (Modo Manual)
        if ($request->has('barcode')) {
            $product = Product::findByCode($request->barcode);
            return $product ? $product->getNextAvailableUnit() : null;
        }

        return null;
    }
}