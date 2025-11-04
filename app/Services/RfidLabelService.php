<?php

namespace App\Services;

use App\Models\PrintJob;
use App\Models\ProductUnit;
use App\Models\PurchaseOrderReceipt;

class RfidLabelService
{
    /**
     * Generar EPC único en formato hexadecimal (24 caracteres = 96 bits)
     * Formato SGTIN-96: Header(2) + Company(6) + Item(5) + Serial(11)
     */
    public function generateEPC(int $productId, int $unitId): string
    {
        $maxAttempts = 10;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            // Generar 12 bytes aleatorios = 24 caracteres hexadecimales
            $epc = strtoupper(bin2hex(random_bytes(12)));
            
            // Verificar que no exista en la base de datos
            $existsInUnits = \DB::table('product_units')->where('epc', $epc)->exists();
            $existsInJobs = \DB::table('print_jobs')->where('epc_code', $epc)->exists();
            
            if (!$existsInUnits && !$existsInJobs) {
                \Log::info("✅ EPC aleatorio único generado: {$epc}");
                return $epc;
            }
            
            \Log::warning("⚠️ EPC duplicado: {$epc}, regenerando (intento {$attempt})");
        }
        
        // Esto es extremadamente improbable (1 en 79 septillones)
        throw new \Exception("No se pudo generar un EPC único después de {$maxAttempts} intentos");
    }



    /**
     * Generar comando ZPL para etiqueta 7.4cm x 1.8cm (74mm x 18mm)
     * Zebra ZT411R con módulo RFID - SOLO CODIFICACIÓN, SIN TEXTO
     */
    public function generateZPL(array $labelData): string
    {
        $epc = $labelData['epc'];

        // Cálculos para 7.4cm x 1.8cm a 203 DPI
        // 74mm / 25.4 * 203 = ~591 dots de ancho
        // 18mm / 25.4 * 203 = ~144 dots de alto
        
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
                \Log::info("Producto {$item->product->code} no tiene tracking RFID, omitiendo");
                continue;
            }

            \Log::info("Procesando producto RFID: {$item->product->code}, cantidad: {$item->quantity_received}");

            // Obtener todas las unidades creadas para este producto en esta recepción
            $units = ProductUnit::where('product_id', $item->product_id)
                ->where('current_location_id', $receipt->warehouse_id)
                ->whereNull('print_job_id') // Solo unidades sin etiqueta impresa
                ->orderBy('created_at', 'desc')
                ->limit($item->quantity_received)
                ->get();

            \Log::info("Unidades encontradas: {$units->count()}");

            if ($units->isEmpty()) {
                \Log::warning("No se encontraron unidades para producto {$item->product->code}");
                continue;
            }

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

                \Log::info("✅ Print Job creado: {$printJob->job_number} para unidad {$unit->id} con EPC: {$epc}");

                $jobsCreated++;
            }
        }

        \Log::info("Total de print jobs creados: {$jobsCreated}");

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