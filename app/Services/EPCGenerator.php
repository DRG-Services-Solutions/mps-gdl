<?php

namespace App\Services;

class EPCGenerator
{
    /**
     * Genera un EPC en formato SGTIN-96 (24 caracteres hexadecimales = 96 bits)
     * 
     * ESTRUCTURA:
     * - Header: 30 (2 chars) - Identifica SGTIN-96
     * - Company Prefix: 6 chars hex - ID de empresa
     * - Item Reference: 6 chars hex - ID de producto/categoría
     * - Serial Number: 10 chars hex - Número único
     * 
     * @param int $productId
     * @param int $categoryId
     * @return string
     */
    public static function generate($productId, $categoryId = 0)
    {
        // 1. Header SGTIN-96 (2 chars hex)
        $header = '30';
        
        // 2. Company Prefix (6 chars hex)
        // Obtener del config o usar valor por defecto
        $companyPrefixNumeric = (int) config('rfid.company_prefix_numeric', 614141);
        $companyPrefix = str_pad(dechex($companyPrefixNumeric), 6, '0', STR_PAD_LEFT);
        
        // 3. Item Reference (6 chars hex)
        // Usar categoryId o productId como referencia
        $itemRefNumeric = $categoryId > 0 ? $categoryId : $productId;
        $itemReference = str_pad(dechex($itemRefNumeric), 6, '0', STR_PAD_LEFT);
        
        // 4. Serial Number (10 chars hex = 40 bits)
        // Generar serial único: timestamp + productId + random
        $timestamp = substr(dechex(time()), -4); // 4 chars del timestamp
        $productHex = str_pad(dechex($productId), 4, '0', STR_PAD_LEFT); // 4 chars
        $randomHex = bin2hex(random_bytes(1)); // 2 chars aleatorios
        
        $serialNumber = strtoupper($timestamp . $productHex . $randomHex);
        
        // Construir EPC completo (24 chars hex)
        $epc = strtoupper($header . $companyPrefix . $itemReference . $serialNumber);
        
        // Validar longitud
        if (strlen($epc) !== 24) {
            \Log::error("EPC generado con longitud incorrecta: " . strlen($epc) . " chars");
            // Ajustar a 24 caracteres
            $epc = str_pad(substr($epc, 0, 24), 24, '0', STR_PAD_RIGHT);
        }
        
        \Log::info("EPC generado: {$epc}");
        \Log::info("  └─ Header: {$header} | Company: {$companyPrefix} | Item: {$itemReference} | Serial: {$serialNumber}");
        
        return $epc;
    }
    
    /**
     * Genera un EPC completamente aleatorio (para productos sin categoría)
     * Mantiene formato SGTIN-96
     * 
     * @return string
     */
    public static function generateRandom()
    {
        // Header SGTIN-96
        $header = '30';
        
        // Company Prefix
        $companyPrefixNumeric = (int) config('rfid.company_prefix_numeric', 614141);
        $companyPrefix = str_pad(dechex($companyPrefixNumeric), 6, '0', STR_PAD_LEFT);
        
        // Item Reference + Serial aleatorios (16 chars hex)
        $randomPart = strtoupper(bin2hex(random_bytes(8)));
        
        $epc = strtoupper($header . $companyPrefix . $randomPart);
        
        \Log::info("EPC aleatorio generado: {$epc}");
        
        return $epc;
    }
    
    /**
     * Valida formato de EPC
     * 
     * @param string $epc
     * @return bool
     */
    public static function validateEPC($epc)
    {
        // Debe ser exactamente 24 caracteres hexadecimales
        if (!preg_match('/^[A-F0-9]{24}$/', $epc)) {
            return false;
        }
        
        // El header debe ser '30' para SGTIN-96
        $header = substr($epc, 0, 2);
        if ($header !== '30') {
            \Log::warning("EPC con header inválido: {$header} (esperado: 30)");
            return false;
        }
        
        return true;
    }
    
    /**
     * Genera comando ZPL CORRECTO para etiqueta RFID
     * 
     * COMANDO RFID CRÍTICO:
     * ^RS8 = Configurar protocolo RFID Gen2
     * ^RFW,E,2,6 = Escribir en EPC bank, desde word 2, 6 words (96 bits)
     * 
     * @param string $epc
     * @param array $productData
     * @return string
     */
    public static function generateZPLCommand($epc, $productData = [])
    {
        // Validar EPC antes de generar ZPL
        if (!self::validateEPC($epc)) {
            throw new \Exception("EPC inválido para generar ZPL: {$epc}");
        }
        
        // Datos del producto (truncados para la etiqueta)
        $name = substr($productData['name'] ?? 'Producto', 0, 30);
        $code = $productData['code'] ?? 'N/A';
        $category = substr($productData['category'] ?? '', 0, 25);
        $lot = $productData['lot_number'] ?? '';
        
        // Calcular longitud en words (24 chars hex = 12 bytes = 96 bits = 6 words)
        $lengthInWords = 6;
        
        $zpl = <<<ZPL
^XA

~TA000
~JSN
^LT0
^MNW
^MTT
^PON
^PMN
^LH0,0
^JMA
^PR4,4
~SD15
^JUS
^LRN
^CI27
^PA0,1,1,0

^FO20,20^A0N,40,40^FH\^FD{$name}^FS
^FO20,70^A0N,28,28^FH\^FDCodigo: {$code}^FS
^FO20,105^A0N,25,25^FH\^FD{$category}^FS
^FO20,135^A0N,22,22^FH\^FDLote: {$lot}^FS

^FO20,180^BQN,2,5
^FH\^FDMA,{$epc}^FS

^FO20,340^A0N,20,20^FH\^FDRFID EPC:^FS
^FO20,365^A0N,18,18^FH\^FD{$epc}^FS

^RS8
^RFW,E,2,{$lengthInWords}^FD{$epc}^FS

^PQ1,0,1,Y
^XZ
ZPL;

        \Log::info("ZPL generado para EPC: {$epc}");
        \Log::info("  └─ Comando RFID: ^RS8 + ^RFW,E,2,{$lengthInWords}");
        
        return $zpl;
    }
    
    /**
     * Genera comando ZPL SOLO para codificación RFID (sin texto impreso)
     * Útil para etiquetas pequeñas de 7.4cm x 1.8cm
     * 
     * @param string $epc
     * @return string
     */
    public static function generateZPLRFIDOnly($epc)
    {
        // Validar EPC
        if (!self::validateEPC($epc)) {
            throw new \Exception("EPC inválido para generar ZPL: {$epc}");
        }
        
        $lengthInWords = 6;
        
        $zpl = <<<ZPL
^XA

~TA000
~JSN
^LT0
^MNW
^MTT
^PON
^PMN
^LH0,0

^MMT
^PW591
^LL144
^LS0

^RS8
^RFW,E,2,{$lengthInWords}^FD{$epc}^FS

^XZ
ZPL;

        return $zpl;
    }
    
    /**
     * Decodificar un EPC en sus componentes
     * Útil para debugging
     * 
     * @param string $epc
     * @return array
     */
    public static function decode($epc)
    {
        if (strlen($epc) !== 24) {
            return ['error' => 'Longitud inválida'];
        }
        
        return [
            'full_epc' => $epc,
            'header' => substr($epc, 0, 2),
            'company_prefix' => substr($epc, 2, 6),
            'item_reference' => substr($epc, 8, 6),
            'serial_number' => substr($epc, 14, 10),
            'is_valid' => self::validateEPC($epc),
        ];
    }
}