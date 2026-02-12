<?php

namespace App\Helpers;

use App\Models\ProductUnit;
use App\Models\Product;
use App\Models\PackageContent;

class ProductSearchHelper
{
    /**
     * Identificar tipo de búsqueda
     */
    public static function identifySearchType(string $input): string
    {
        $input = trim($input);
        $length = strlen($input);
        
        \Log::info('[SEARCH] 🔍 Identificando tipo de búsqueda', [
            'input' => $input,
            'length' => $length,
        ]);

        // 1. EPC RFID: 24 caracteres hexadecimales
        if ($length === 24 && ctype_xdigit($input)) {
            \Log::info('[SEARCH] ✅ Identificado como EPC (24 hex chars)');
            return 'epc';
        }

        // 2. Serial Number: formato SN-xxxxx o SERIAL-xxxxx
        if (preg_match('/^(SN|SERIAL)[_\-]/i', $input)) {
            \Log::info('[SEARCH] ✅ Identificado como SERIAL');
            return 'serial';
        }

        // 3. Por defecto: Código de producto
        \Log::info('[SEARCH] ✅ Identificado como CODE (producto)');
        return 'code';
    }

    /**
     * Buscar ProductUnit por tipo
     */
    public static function searchProductUnit(string $input, string $type): ?ProductUnit
    {
        \Log::info('[SEARCH] 🔎 Iniciando búsqueda de ProductUnit', [
            'input' => $input,
            'type' => $type
        ]);

        $productUnit = match($type) {
            'epc' => self::searchByEpc($input),
            'serial' => self::searchBySerial($input),
            'code' => self::searchByCode($input),
            default => null,
        };

        if (!$productUnit) {
            \Log::warning('[SEARCH] ❌ No se encontró ProductUnit', [
                'input' => $input,
                'type' => $type
            ]);
        }

        return $productUnit;
    }

    /**
     * Buscar por EPC (RFID Tag)
     */
    private static function searchByEpc(string $epc): ?ProductUnit
    {
        \Log::info('[SEARCH] 🏷️ Buscando por EPC', ['epc' => $epc]);

        $productUnit = ProductUnit::where('epc', $epc)->first();

        if ($productUnit) {
            \Log::info('[SEARCH] ✅ ProductUnit encontrado por EPC', [
                'product_unit_id' => $productUnit->id,
                'product_id' => $productUnit->product_id,
                'product_code' => $productUnit->product->code ?? 'N/A',
                'product_name' => $productUnit->product->name ?? 'N/A',
                'status' => $productUnit->status,
                'has_serial' => !empty($productUnit->serial_number),
            ]);
        } else {
            \Log::warning('[SEARCH] ⚠️ No existe ProductUnit con EPC', ['epc' => $epc]);
        }

        return $productUnit;
    }

    /**
     * Buscar por Serial Number
     */
    private static function searchBySerial(string $serial): ?ProductUnit
    {
        \Log::info('[SEARCH] 🔢 Buscando por Serial', ['serial' => $serial]);

        $productUnit = ProductUnit::where('serial_number', $serial)->first();

        if ($productUnit) {
            \Log::info('[SEARCH] ✅ ProductUnit encontrado por Serial', [
                'product_unit_id' => $productUnit->id,
                'product_id' => $productUnit->product_id,
                'product_code' => $productUnit->product->code ?? 'N/A',
                'has_epc' => !empty($productUnit->epc),
                'status' => $productUnit->status,
            ]);
        } else {
            \Log::warning('[SEARCH] ⚠️ No existe ProductUnit con Serial', ['serial' => $serial]);
        }

        return $productUnit;
    }

    /**
     * Buscar por Code del Producto
     * NOTA: Retorna el PRIMER ProductUnit disponible que NO esté en un paquete
     */
    private static function searchByCode(string $code): ?ProductUnit
    {
        \Log::info('[SEARCH] 📦 Buscando por Code de Producto', ['code' => $code]);

        // Paso 1: Buscar el producto
        $product = Product::where('code', $code)
            ->where('status', 'active')
            ->first();

        if (!$product) {
            \Log::warning('[SEARCH] ❌ No existe Product con ese código', ['code' => $code]);
            return null;
        }

        \Log::info('[SEARCH] ✅ Product encontrado', [
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);

        // Paso 2: Obtener IDs de ProductUnits que YA están en paquetes
        $usedProductUnitIds = PackageContent::pluck('product_unit_id')->toArray();

        \Log::info('[SEARCH] 📊 ProductUnits en uso', [
            'count_in_packages' => count($usedProductUnitIds),
        ]);

        // Paso 3: Buscar ProductUnit disponible que NO esté en la lista de usados
        $productUnit = ProductUnit::where('product_id', $product->id)
            ->where('status', 'available')
            ->whereNotIn('id', $usedProductUnitIds) // ✅ Excluir los que ya están en paquetes
            ->orderBy('created_at', 'asc') // FIFO
            ->first();

        if ($productUnit) {
            \Log::info('[SEARCH] ✅ ProductUnit disponible encontrado', [
                'product_unit_id' => $productUnit->id,
                'has_epc' => !empty($productUnit->epc),
                'has_serial' => !empty($productUnit->serial_number),
                'status' => $productUnit->status,
            ]);
        } else {
            $totalUnits = ProductUnit::where('product_id', $product->id)->count();
            $availableUnits = ProductUnit::where('product_id', $product->id)
                ->where('status', 'available')
                ->count();
            
            \Log::warning('[SEARCH] ⚠️ Product existe pero SIN stock disponible', [
                'product_id' => $product->id,
                'product_code' => $code,
                'total_units' => $totalUnits,
                'available_units' => $availableUnits,
                'units_in_packages' => count($usedProductUnitIds),
            ]);
        }

        return $productUnit;
    }

    /**
     * Obtener stock disponible de un producto (excluyendo los que están en paquetes)
     */
    public static function getAvailableStock(int $productId): int
    {
        $usedProductUnitIds = PackageContent::where('product_id', $productId)
            ->pluck('product_unit_id')
            ->toArray();

        $stock = ProductUnit::where('product_id', $productId)
            ->where('status', 'available')
            ->whereNotIn('id', $usedProductUnitIds)
            ->count();

        \Log::info('[SEARCH] 📊 Stock disponible consultado', [
            'product_id' => $productId,
            'stock_available' => $stock,
            'stock_in_packages' => count($usedProductUnitIds),
        ]);

        return $stock;
    }

    /**
     * Validar si un ProductUnit está verdaderamente disponible
     */
    public static function isAvailable(ProductUnit $productUnit): bool
    {
        // Verificar si está en algún paquete
        $isInPackage = PackageContent::where('product_unit_id', $productUnit->id)->exists();

        $isAvailable = $productUnit->status === 'available' && !$isInPackage;

        \Log::info('[SEARCH] 🔒 Validación de disponibilidad', [
            'product_unit_id' => $productUnit->id,
            'status' => $productUnit->status,
            'in_package' => $isInPackage,
            'is_available' => $isAvailable,
        ]);

        return $isAvailable;
    }

    /**
     * Buscar múltiples ProductUnits (para escaneo masivo)
     */
    public static function searchMultiple(array $inputs): array
    {
        \Log::info('[SEARCH] 📋 Búsqueda masiva iniciada', [
            'count' => count($inputs)
        ]);

        $results = [];

        foreach ($inputs as $input) {
            $type = self::identifySearchType($input);
            $productUnit = self::searchProductUnit($input, $type);

            $results[] = [
                'input' => $input,
                'type' => $type,
                'product_unit' => $productUnit,
                'found' => !is_null($productUnit),
            ];
        }

        $found = collect($results)->where('found', true)->count();
        $notFound = count($results) - $found;

        \Log::info('[SEARCH] 📊 Búsqueda masiva completada', [
            'total' => count($results),
            'found' => $found,
            'not_found' => $notFound,
        ]);

        return $results;
    }
}