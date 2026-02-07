<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CfdiXmlController extends Controller
{
    /**
     * Procesar XML CFDI y validar contra la orden de compra
     */
    public function processForReceipt(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validator = Validator::make($request->all(), [
            'xml_file' => 'required|file|mimes:xml|max:5120',
        ], [
            'xml_file.required' => 'Debes seleccionar un archivo XML.',
            'xml_file.mimes' => 'El archivo debe ser un XML válido.',
            'xml_file.max' => 'El archivo no debe superar los 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $file = $request->file('xml_file');
            $xmlContent = file_get_contents($file->getPathname());
            
            // Parsear el XML
            $cfdiData = $this->parseCfdiXml($xmlContent);
            
            if (!$cfdiData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $cfdiData['message'],
                ], 422);
            }

            // Validar productos contra la orden de compra
            $validationResult = $this->validateAgainstOrder($purchaseOrder, $cfdiData['conceptos']);

            return response()->json([
                'success' => $validationResult['success'],
                'message' => $validationResult['message'],
                'cfdi' => [
                    'uuid' => $cfdiData['uuid'],
                    'folio' => $cfdiData['folio'],
                    'serie' => $cfdiData['serie'],
                    'fecha' => $cfdiData['fecha'],
                    'emisor_rfc' => $cfdiData['emisor_rfc'],
                    'emisor_nombre' => $cfdiData['emisor_nombre'],
                    'receptor_rfc' => $cfdiData['receptor_rfc'],
                    'receptor_nombre' => $cfdiData['receptor_nombre'],
                    'subtotal' => $cfdiData['subtotal'],
                    'total' => $cfdiData['total'],
                ],
                'items' => $validationResult['items'],
                'errors' => $validationResult['errors'],
                'warnings' => $validationResult['warnings'],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al procesar XML CFDI: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo XML: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parsear XML CFDI 4.0
     */
    private function parseCfdiXml(string $xmlContent): array
    {
        try {
            // Limpiar BOM si existe
            $xmlContent = preg_replace('/^\xEF\xBB\xBF/', '', $xmlContent);
            
            $xml = new \SimpleXMLElement($xmlContent);
            
            // Registrar namespaces
            $namespaces = $xml->getNamespaces(true);
            $cfdiNs = $namespaces['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4';
            $tfdNs = $namespaces['tfd'] ?? 'http://www.sat.gob.mx/TimbreFiscalDigital';
            
            $xml->registerXPathNamespace('cfdi', $cfdiNs);
            $xml->registerXPathNamespace('tfd', $tfdNs);

            // Datos del comprobante
            $attrs = $xml->attributes();
            
            // Obtener UUID del TimbreFiscalDigital
            $tfd = $xml->xpath('//tfd:TimbreFiscalDigital');
            $uuid = $tfd ? (string) $tfd[0]->attributes()['UUID'] : null;

            // Datos del emisor
            $emisor = $xml->xpath('//cfdi:Emisor')[0] ?? null;
            $emisorRfc = $emisor ? (string) $emisor->attributes()['Rfc'] : '';
            $emisorNombre = $emisor ? (string) $emisor->attributes()['Nombre'] : '';

            // Datos del receptor
            $receptor = $xml->xpath('//cfdi:Receptor')[0] ?? null;
            $receptorRfc = $receptor ? (string) $receptor->attributes()['Rfc'] : '';
            $receptorNombre = $receptor ? (string) $receptor->attributes()['Nombre'] : '';

            // Conceptos
            $conceptos = [];
            $conceptosXml = $xml->xpath('//cfdi:Concepto');
            
            foreach ($conceptosXml as $concepto) {
                $conceptoAttrs = $concepto->attributes();
                $conceptos[] = [
                    'no_identificacion' => (string) $conceptoAttrs['NoIdentificacion'],
                    'descripcion' => (string) $conceptoAttrs['Descripcion'],
                    'cantidad' => (float) $conceptoAttrs['Cantidad'],
                    'valor_unitario' => (float) $conceptoAttrs['ValorUnitario'],
                    'importe' => (float) $conceptoAttrs['Importe'],
                    'clave_prod_serv' => (string) $conceptoAttrs['ClaveProdServ'],
                    'clave_unidad' => (string) $conceptoAttrs['ClaveUnidad'],
                    'unidad' => (string) $conceptoAttrs['Unidad'],
                ];
            }

            if (empty($conceptos)) {
                return [
                    'success' => false,
                    'message' => 'El XML no contiene conceptos (productos).',
                ];
            }

            return [
                'success' => true,
                'uuid' => $uuid,
                'folio' => (string) $attrs['Folio'],
                'serie' => (string) $attrs['Serie'],
                'fecha' => (string) $attrs['Fecha'],
                'subtotal' => (float) $attrs['SubTotal'],
                'total' => (float) $attrs['Total'],
                'emisor_rfc' => $emisorRfc,
                'emisor_nombre' => $emisorNombre,
                'receptor_rfc' => $receptorRfc,
                'receptor_nombre' => $receptorNombre,
                'conceptos' => $conceptos,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'El archivo no es un XML CFDI válido: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validar conceptos del CFDI contra la orden de compra
     */
    private function validateAgainstOrder(PurchaseOrder $purchaseOrder, array $conceptos): array
    {
        $items = [];
        $errors = [];
        $warnings = [];
        $hasBlockingError = false;

        // Cargar items de la orden con sus códigos
        $orderItems = $purchaseOrder->items()
            ->whereColumn('quantity_received', '<', 'quantity_ordered')
            ->get()
            ->keyBy('product_code');

        // Crear mapa de códigos del XML
        $xmlCodes = collect($conceptos)->pluck('cantidad', 'no_identificacion');

        // Validar cada concepto del XML
        foreach ($conceptos as $concepto) {
            $code = $concepto['no_identificacion'];
            $xmlQty = (int) $concepto['cantidad'];
            
            $orderItem = $orderItems->get($code);

            if (!$orderItem) {
                // Producto no está en la orden
                $errors[] = [
                    'type' => 'not_in_order',
                    'code' => $code,
                    'description' => $concepto['descripcion'],
                    'xml_quantity' => $xmlQty,
                    'message' => "El producto '{$code}' no está en la orden de compra o ya fue recibido completamente.",
                ];
                $hasBlockingError = true;
                continue;
            }

            $pendingQty = $orderItem->pending_quantity;
            $status = 'ok';
            $finalQty = $xmlQty;

            if ($xmlQty > $pendingQty) {
                // Cantidad del XML excede lo pendiente
                $errors[] = [
                    'type' => 'quantity_exceeded',
                    'code' => $code,
                    'description' => $concepto['descripcion'],
                    'xml_quantity' => $xmlQty,
                    'pending_quantity' => $pendingQty,
                    'ordered_quantity' => $orderItem->quantity_ordered,
                    'received_quantity' => $orderItem->quantity_received,
                    'message' => "El producto '{$code}' tiene cantidad {$xmlQty} en el XML pero solo hay {$pendingQty} pendiente(s) de recibir.",
                ];
                $hasBlockingError = true;
                $status = 'error';
                $finalQty = $pendingQty; // Sugerir la cantidad máxima permitida
            } elseif ($xmlQty < $pendingQty) {
                // Cantidad del XML es menor (recepción parcial)
                $warnings[] = [
                    'type' => 'partial_receipt',
                    'code' => $code,
                    'description' => $concepto['descripcion'],
                    'xml_quantity' => $xmlQty,
                    'pending_quantity' => $pendingQty,
                    'message' => "El producto '{$code}' tiene {$pendingQty} pendiente(s) pero el XML solo indica {$xmlQty}. Se registrará recepción parcial.",
                ];
                $status = 'warning';
            }

            $items[] = [
                'item_id' => $orderItem->id,
                'product_code' => $code,
                'product_name' => $orderItem->product_name,
                'description' => $concepto['descripcion'],
                'quantity_ordered' => $orderItem->quantity_ordered,
                'quantity_received' => $orderItem->quantity_received,
                'pending_quantity' => $pendingQty,
                'xml_quantity' => $xmlQty,
                'quantity_to_receive' => $finalQty,
                'status' => $status,
            ];
        }

        // Verificar si hay productos pendientes en la orden que NO están en el XML
        $xmlCodes = collect($conceptos)->pluck('no_identificacion')->toArray();
        foreach ($orderItems as $code => $orderItem) {
            if (!in_array($code, $xmlCodes)) {
                $warnings[] = [
                    'type' => 'not_in_xml',
                    'code' => $code,
                    'description' => $orderItem->product_name,
                    'pending_quantity' => $orderItem->pending_quantity,
                    'message' => "El producto '{$code}' está pendiente en la orden pero no aparece en el XML.",
                ];
            }
        }

        return [
            'success' => !$hasBlockingError,
            'message' => $hasBlockingError 
                ? 'El XML contiene errores que impiden continuar. Revisa los detalles.' 
                : 'XML procesado correctamente.',
            'items' => $items,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}
