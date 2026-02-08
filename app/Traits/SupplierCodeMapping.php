<?php

namespace App\Traits;

use App\Models\Product;

/**
 * Trait para manejar el mapeo de códigos de productos por proveedor
 * 
 * Algunos proveedores manejan códigos diferentes a los internos del sistema.
 * Este trait centraliza la lógica de búsqueda con transformaciones de código.
 */
trait SupplierCodeMapping
{
    /**
     * Proveedores que requieren transformación de código
     * 
     * Formato: supplier_id => ['transformaciones a intentar']
     * Las transformaciones se aplican en orden hasta encontrar el producto
     */
    protected array $supplierCodeTransformations = [
        2 => ['/1'], // Medartis: buscar primero con /1, luego sin sufijo
    ];

    /**
     * Buscar producto por código, aplicando transformaciones según el proveedor
     *
     * @param string $code Código del producto (del proveedor)
     * @param int|null $supplierId ID del proveedor
     * @return Product|null
     */
    protected function findProductBySupplierCode(string $code, ?int $supplierId = null): ?Product
    {
        // Si hay transformaciones para este proveedor
        if ($supplierId && isset($this->supplierCodeTransformations[$supplierId])) {
            $transformations = $this->supplierCodeTransformations[$supplierId];
            
            // Intentar primero con cada transformación
            foreach ($transformations as $suffix) {
                $transformedCode = $code . $suffix;
                $product = Product::where('code', $transformedCode)->first();
                
                if ($product) {
                    \Log::info("Código transformado: {$code} -> {$transformedCode} (Proveedor ID: {$supplierId})");
                    return $product;
                }
            }
        }
        
        // Buscar código exacto (sin transformación)
        return Product::where('code', $code)->first();
    }

    /**
     * Buscar múltiples productos por códigos, aplicando transformaciones según el proveedor
     *
     * @param array $codes Array de códigos
     * @param int|null $supplierId ID del proveedor
     * @return array ['found' => [code => Product], 'code_map' => [original => transformed]]
     */
    protected function findProductsBySupplierCodes(array $codes, ?int $supplierId = null): array
    {
        $found = [];
        $codeMap = []; // Mapeo de código original -> código encontrado
        
        foreach ($codes as $originalCode) {
            $product = $this->findProductBySupplierCode($originalCode, $supplierId);
            
            if ($product) {
                $found[$originalCode] = $product;
                $codeMap[$originalCode] = $product->code;
            }
        }
        
        return [
            'found' => $found,
            'code_map' => $codeMap,
        ];
    }

    /**
     * Transformar código según el proveedor (para comparaciones)
     * Retorna el código interno del sistema si existe, o null
     *
     * @param string $code Código del proveedor
     * @param int|null $supplierId ID del proveedor
     * @return string|null Código interno o null si no existe
     */
    protected function transformSupplierCode(string $code, ?int $supplierId = null): ?string
    {
        $product = $this->findProductBySupplierCode($code, $supplierId);
        return $product?->code;
    }
}
