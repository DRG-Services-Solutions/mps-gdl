<?php

namespace App\Helpers;

use App\Models\ProductUnit;
use App\Models\Product;

class ProductSearchHelper
{
    /**
     * Identificar tipo de búsqueda
     */
    public static function identifySearchType(string $input): string
    {
        \Log::info('[PACKAGE] 🔍 Identificando tipo de búsqueda', [
            'input' => $input,
            'length' => strlen($input),
        ]);

        if (strlen($input) >= 20 && ctype_alnum($input)) {
            \Log::info('[PACKAGE]  Identificado como EPC');
            return 'epc';
        }

        if (preg_match('/^(SN-|SERIAL-)/i', $input)) {
            \Log::info('[PACKAGE]  Identificado como SERIAL');
            return 'serial';
        }

        \Log::info('[PACKAGE]  Identificado como CODE');
        return 'code';
    }

    /**
     * Buscar ProductUnit por tipo
     */
    public static function searchProductUnit(string $input, string $type)
    {
        \Log::info('[PACKAGE] 🔎 Iniciando búsqueda', [
            'input' => $input,
            'type' => $type
        ]);

        switch ($type) {
            case 'epc':
                return self::searchByEpc($input);
            
            case 'serial':
                return self::searchBySerial($input);
            
            case 'code':
                return self::searchByCode($input);
            
            default:
                \Log::warning('[PACKAGE]  Tipo no reconocido', ['type' => $type]);
                return null;
        }
    }

    /**
     * Buscar por EPC
     */
    private static function searchByEpc(string $epc)
    {
        \Log::info('[PACKAGE] Buscando por EPC', ['epc' => $epc]);

        $productUnit = ProductUnit::where('epc', $epc)->first();

        if ($productUnit) {
            \Log::info('[PACKAGE] ✅ ProductUnit encontrado', [
                'product_unit_id' => $productUnit->id,
                'product_id' => $productUnit->product_id,
                'status' => $productUnit->status,
            ]);
        } else {
            \Log::warning('[PACKAGE]  No se encontró ProductUnit con ese EPC', ['epc' => $epc]);
        }

        return $productUnit;
    }

    /**
     * Buscar por Serial
     */
    private static function searchBySerial(string $serial)
    {
        \Log::info('[PACKAGE] Buscando por Serial', ['serial' => $serial]);

        $productUnit = ProductUnit::where('serial_number', $serial)->first();

        if ($productUnit) {
            \Log::info('[PACKAGE] ✅ ProductUnit encontrado', [
                'product_unit_id' => $productUnit->id,
                'product_id' => $productUnit->product_id,
                'status' => $productUnit->status,
            ]);
        } else {
            \Log::warning('[PACKAGE]  No se encontró ProductUnit con ese Serial', ['serial' => $serial]);
        }

        return $productUnit;
    }

    /**
     * Buscar por Code del producto
     */
    private static function searchByCode(string $code)
    {
        \Log::info('[PACKAGE] Buscando por Code', ['code' => $code]);

        $product = Product::where('code', $code)->first();

        if (!$product) {
            \Log::warning('[PACKAGE]  No existe Product con ese código', ['code' => $code]);
            return null;
        }

        \Log::info('[PACKAGE]  Product encontrado', [
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);

        $productUnit = ProductUnit::where('product_id', $product->id)
            ->where('status', 'available')
            ->where('reserved_quantity', 0)
            ->first();

        if ($productUnit) {
            \Log::info('[PACKAGE]  ProductUnit disponible encontrado', [
                'product_unit_id' => $productUnit->id,
                'has_epc' => !empty($productUnit->epc),
                'status' => $productUnit->status,
            ]);
        } else {
            \Log::warning('[PACKAGE]  Product existe pero sin stock disponible', [
                'product_id' => $product->id,
                'product_code' => $code,
            ]);
        }

        return $productUnit;
    }

    /**
     * Validar stock disponible de un producto
     */
    public static function getAvailableStock(int $productId): int
    {
        $stock = ProductUnit::where('product_id', $productId)
            ->where('status', 'available')
            ->where('reserved_quantity', 0)
            ->count();

        \Log::info('[PACKAGE] 📊 Stock disponible consultado', [
            'product_id' => $productId,
            'stock_available' => $stock,
        ]);

        return $stock;
    }
}