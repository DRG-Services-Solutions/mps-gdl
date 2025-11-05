<?php

namespace App\Services;

use App\Models\PrintJob;
use App\Models\ProductUnit;
use App\Models\PurchaseOrderReceipt;

class RfidLabelService
{
    /**
     * Generar EPC único en formato SGTIN-96 (24 caracteres hex = 96 bits)
     * 
     * FORMATO SGTIN-96:
     * - Header: 8 bits (0x30 = SGTIN-96)
     * - Filter: 3 bits (tipo de producto)
     * - Partition: 3 bits (indica cómo dividir Company y Item)
     * - Company Prefix: 20-40 bits (tu código de empresa GS1)
     * - Item Reference: 4-24 bits (tu código de producto interno)
     * - Serial: 38 bits (número de serie único)
     * 
     * SIMPLIFICADO (sin GS1 real):
     * - Header: 30 (fijo, identifica SGTIN-96)
     * - Company: 6 caracteres hex (tu ID de empresa)
     * - Product: 6 caracteres hex (código de producto)
     * - Serial: 12 caracteres hex (número único secuencial o aleatorio)
     * 
     * Total: 2 + 6 + 6 + 10 = 24 caracteres hex = 96 bits
     */
    public function generateEPC(int $productId, int $unitId): string
    {
        $maxAttempts = 10;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            // FORMATO SGTIN-96 SIMPLIFICADO:
            
            // 1. Header (2 chars hex): Siempre "30" para SGTIN-96
            $header = '30';
            
            // 2. Company Prefix (6 chars hex): Usar ID de tu empresa o constante
            // Puedes cambiarlo por tu código GS1 si tienes uno
            $companyPrefix = str_pad(dechex(123456), 6, '0', STR_PAD_LEFT); // Ejemplo: 01E240
            
            // 3. Item Reference (6 chars hex): Basado en product_id
            $itemReference = str_pad(dechex($productId), 6, '0', STR_PAD_LEFT);
            
            // 4. Serial Number (10 chars hex): Aleatorio o secuencial
            // Opción A: Completamente aleatorio (40 bits)
            $serialNumber = strtoupper(bin2hex(random_bytes(5))); // 5 bytes = 10 chars hex
            
            // Opción B: Basado en timestamp + unitId (más predecible pero único)
            // $timestamp = substr(dechex(time()), -5); // Últimos 5 chars del timestamp
            // $serialNumber = str_pad($timestamp, 5, '0', STR_PAD_LEFT) . str_pad(dechex($unitId), 5, '0', STR_PAD_LEFT);
            
            // Construir EPC completo (24 chars hex)
            $epc = strtoupper($header . $companyPrefix . $itemReference . $serialNumber);
            
            // Verificar que no exista en la base de datos
            $existsInUnits = \DB::table('product_units')->where('epc', $epc)->exists();
            $existsInJobs = \DB::table('print_jobs')->where('epc_code', $epc)->exists();
            
            if (!$existsInUnits && !$existsInJobs) {
                \Log::info("✅ EPC SGTIN-96 generado: {$epc}");
                \Log::info("   └─ Header: {$header} | Company: {$companyPrefix} | Product: {$itemReference} | Serial: {$serialNumber}");
                return $epc;
            }
            
            \Log::warning("⚠️ EPC duplicado: {$epc}, regenerando (intento {$attempt})");
        }
        
        throw new \Exception("No se pudo generar un EPC único después de {$maxAttempts} intentos");
    }

    /**
     * Generar comando ZPL para etiqueta RFID 7.4cm x 1.8cm (74mm x 18mm)
     * Zebra ZT411R con módulo RFID UHF
     * 
     * COMANDO RFID CORRECTO:
     * ^RS = Configuración de parámetros RFID
     * ^RFW = Escribir datos RFID
     * 
     * FORMATO: ^RFW,H,memoria,inicio,longitud^FDdatos^FS
     * - H = datos en hexadecimal
     * - memoria: E = EPC bank (memoria principal del tag)
     * - inicio: 2 = comenzar después del CRC (word 2)
     * - longitud: 12 = cantidad de words (12 words × 16 bits = 192 bits, pero usamos 96 bits = 6 words)
     */
    public function generateZPL(array $labelData): string
{
    $epc = $labelData['epc'];
    
    // Validar EPC
    if (strlen($epc) !== 24 || !ctype_xdigit($epc)) {
        throw new \Exception("EPC inválido: debe ser 24 caracteres hexadecimales. Recibido: {$epc}");
    }

    \Log::info("🏷️ Generando ZPL para EPC: {$epc}");
    
    // ========================================
    // COMANDO CORRECTO: ^RFW,H (no E,2,6)
    // ========================================
    $zpl = "^XA\n";
    $zpl .= "^RFW,H^FD{$epc}^FS\n";  // 🎯 Formato hexadecimal simple
    $zpl .= "^XZ";

    \Log::info("✅ ZPL generado con ^RFW,H");
    
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
                // Generar EPC único en formato SGTIN-96
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

                // Generar comando ZPL corregido
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

                \Log::info("✅ Print Job creado: {$printJob->job_number}");
                \Log::info("   └─ Unidad: {$unit->id} | EPC: {$epc}");

                $jobsCreated++;
            }
        }

        \Log::info("🎉 Total de print jobs creados: {$jobsCreated}");

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
                
                \Log::info("🔄 Reintentando job: {$job->job_number}");
            }
        }

        \Log::info("🔄 Total de jobs reintentados: {$retriedCount}");

        return $retriedCount;
    }
}