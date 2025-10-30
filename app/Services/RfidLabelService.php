<?php

namespace App\Services;

use App\Models\PrintJob;
use App\Models\ProductUnit;
use App\Models\PurchaseOrderReceipt;

class RfidLabelService
{
    /**
     * Generar EPC único en formato hexadecimal (24 caracteres)
     * Formato EPC-96: Header(2) + CompanyPrefix(7) + ItemRef(6) + Serial(9)
     */
    public function generateEPC(int $productId, int $unitId): string
    {
        // Header (2 hex) - 30 para consumibles RFID
        $header = '30';
        
        // Company Prefix (7 hex) - ID de tu empresa (configurable)
        $companyPrefix = str_pad(dechex(config('rfid.company_prefix', 1234567)), 7, '0', STR_PAD_LEFT);
        
        // Item Reference (6 hex) - ID del producto
        $itemRef = str_pad(dechex($productId), 6, '0', STR_PAD_LEFT);
        
        // Serial Number (9 hex) - ID de unidad + timestamp para unicidad
        $serialBase = $unitId . substr(time(), -4);
        $serial = str_pad(dechex($serialBase), 9, '0', STR_PAD_LEFT);
        
        // Concatenar todo (24 caracteres hex)
        $epc = strtoupper($header . $companyPrefix . $itemRef . $serial);
        
        // Asegurar 24 caracteres exactos
        return substr(str_pad($epc, 24, '0', STR_PAD_LEFT), 0, 24);
    }

    /**
     * Generar comando ZPL para etiqueta 4"x1" (102mm x 25mm)
     * Zebra ZT411R con módulo RFID
     */
    public function generateZPL(array $labelData): string
    {
        $epc = $labelData['epc'];
        $productCode = $labelData['product_code'];
        $productName = substr($labelData['product_name'], 0, 30); // Limitar nombre
        $batchNumber = $labelData['batch_number'] ?? '';
        $expiryDate = $labelData['expiry_date'] ?? '';

        $zpl = <<<ZPL
^XA

~TA000
~JSN
^LT0
^MNW
^MTD
^PON
^PMN
^LH0,0

^MMT
^PW591
^LL144
^LS0

^RFW,H,1,2,1^FD{$epc}^FS

^XZ
ZPL;

        return $zpl;
    }

    /**
     * Crear trabajos de impresión para una recepción
     */
    public function createPrintJobsForReceipt(PurchaseOrderReceipt $receipt): int
    {
        $jobsCreated = 0;

        foreach ($receipt->items as $item) {
            // Solo productos con tracking RFID
            if ($item->product->tracking_type !== 'rfid') {
                continue;
            }

            // Obtener todas las unidades creadas para este item
            $units = ProductUnit::where('product_id', $item->product_id)
                ->where('batch_number', $item->batch_number)
                ->whereNull('print_job_id') // Solo unidades sin etiqueta impresa
                ->limit($item->quantity_received)
                ->get();

            foreach ($units as $unit) {
                // Generar EPC único
                $epc = $this->generateEPC($item->product_id, $unit->id);
                
                // Actualizar la unidad con el EPC
                $unit->update(['epc' => $epc]);

                // Preparar datos de la etiqueta
                $labelData = [
                    'epc' => $epc,
                    'product_code' => $item->product->code,
                    'product_name' => $item->product->name,
                    'batch_number' => $item->batch_number ?? 'N/A',
                    'expiry_date' => $item->expiry_date ? $item->expiry_date->format('d/m/Y') : 'N/A',
                ];

                // Generar comando ZPL
                $zpl = $this->generateZPL($labelData);

                // Crear trabajo de impresión
                $printJob = PrintJob::create([
                    'job_number' => PrintJob::generateJobNumber(),
                    'receipt_id' => $receipt->id,
                    'product_unit_id' => $unit->id,
                    'epc_code' => $epc,
                    'zpl_commands' => $zpl,
                    'label_data' => $labelData,
                    'status' => 'pending',
                ]);

                // Vincular el trabajo de impresión a la unidad
                $unit->update(['print_job_id' => $printJob->id]);

                $jobsCreated++;
            }
        }

        return $jobsCreated;
    }

    /**
     * Reintentar trabajos fallidos
     */
    public function retryFailedJobs(PurchaseOrderReceipt $receipt): int
    {
        $failedJobs = PrintJob::where('receipt_id', $receipt->id)
            ->where('status', 'failed')
            ->get();

        $retriedCount = 0;

        foreach ($failedJobs as $job) {
            if ($job->canRetry()) {
                $job->update([
                    'status' => 'pending',
                    'error_message' => null,
                ]);
                $retriedCount++;
            }
        }

        return $retriedCount;
    }
}