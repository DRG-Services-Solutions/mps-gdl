<?php

namespace App\Services;

class EPCGenerator
{
    /**
     * Genera un EPC de 24 caracteres hexadecimales (96 bits)
     * Solo para productos con etiqueta RFID impresa
     * 
     * @param int $productId
     * @param int $categoryId
     * @return string
     */
    public static function generate($productId, $categoryId = 0)
    {
        $header = 0x30;
        $filter = 1;
        $partition = 3;
        $companyPrefix = (int) config('rfid.company_prefix_numeric', 614141);
        $itemReference = $categoryId & 0xFFFFF;
        $serialNumber = $productId & 0x3FFFFFFFFF;
        
        $binary = '';
        $binary .= str_pad(decbin($header), 8, '0', STR_PAD_LEFT);
        $filterPartition = ($filter << 3) | $partition;
        $binary .= str_pad(decbin($filterPartition), 6, '0', STR_PAD_LEFT);
        $binary .= str_pad(decbin($companyPrefix), 24, '0', STR_PAD_LEFT);
        $binary .= str_pad(decbin($itemReference), 20, '0', STR_PAD_LEFT);
        $binary .= str_pad(decbin($serialNumber), 38, '0', STR_PAD_LEFT);
        $binary = str_pad(substr($binary, 0, 96), 96, '0', STR_PAD_RIGHT);
        
        $hex = '';
        for ($i = 0; $i < 96; $i += 4) {
            $nibble = substr($binary, $i, 4);
            $hex .= dechex(bindec($nibble));
        }
        
        return strtoupper($hex);
    }
    
    /**
     * Valida formato de EPC
     */
    public static function validateEPC($epc)
    {
        return preg_match('/^[A-F0-9]{24}$/', $epc) === 1;
    }
    
    /**
     * Genera comando ZPL para etiqueta RFID
     */
    public static function generateZPLCommand($epc, $productData = [])
    {
        $name = substr($productData['name'] ?? 'Producto', 0, 30);
        $code = $productData['code'] ?? 'N/A';
        $category = $productData['category'] ?? '';
        $lot = $productData['lot_number'] ?? '';
        
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

^RFW,H^FD{$epc}^FS

^PQ1,0,1,Y
^XZ
ZPL;

        return $zpl;
    }
}