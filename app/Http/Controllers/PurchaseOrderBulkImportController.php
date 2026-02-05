<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseOrderBulkImportController extends Controller
{
    /**
     * Descargar template CSV para carga masiva de productos
     */
    public function downloadTemplate(): StreamedResponse
    {
        $filename = 'template_carga_masiva_productos.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ];

        $callback = function () {
            $output = fopen('php://output', 'w');

            // BOM para UTF-8 (para que Excel reconozca los acentos)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($output, ['codigo_producto', 'cantidad']);

            // Filas de ejemplo
            $examples = [
                ['PROD-001', 10],
                ['PROD-002', 5],
                ['PROD-003', 20],
            ];

            foreach ($examples as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Descargar template con catálogo de productos disponibles
     */
    public function downloadTemplateWithCatalog(): StreamedResponse
    {
        $filename = 'template_carga_masiva_con_catalogo.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ];

        $callback = function () {
            $output = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Hoja de instrucciones como comentarios
            fputcsv($output, ['# INSTRUCCIONES:']);
            fputcsv($output, ['# 1. Llena la columna "cantidad" con el numero de unidades a ordenar']);
            fputcsv($output, ['# 2. Elimina las filas que NO necesitas (o deja cantidad en 0)']);
            fputcsv($output, ['# 3. Guarda el archivo y subelo en el sistema']);
            fputcsv($output, ['# 4. Las filas que empiezan con # seran ignoradas']);
            fputcsv($output, ['']);

            // Encabezados
            fputcsv($output, ['codigo_producto', 'nombre_producto', 'cantidad']);

            // Obtener todos los productos activos
            $products = Product::orderBy('code')
                ->select(['code', 'name'])
                ->get();

            foreach ($products as $product) {
                fputcsv($output, [
                    $product->code,
                    $product->name,
                    0, // Cantidad inicial en 0
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Procesar el archivo CSV e importar productos
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'file.required' => 'Debes seleccionar un archivo CSV.',
            'file.mimes' => 'El archivo debe ser un CSV (.csv).',
            'file.max' => 'El archivo no debe superar los 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $handle = fopen($file->getPathname(), 'r');

            if (!$handle) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo abrir el archivo.',
                ], 422);
            }

            $items = [];
            $errors = [];
            $productCodes = [];
            $rowNumber = 0;
            $headers = null;
            $codeIndex = null;
            $quantityIndex = null;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Saltar filas vacías
                if (empty(array_filter($row))) {
                    continue;
                }

                // Saltar comentarios (filas que empiezan con #)
                $firstCell = trim($row[0] ?? '');
                if (str_starts_with($firstCell, '#')) {
                    continue;
                }

                // Detectar encabezados
                if ($headers === null) {
                    $headers = array_map(function ($h) {
                        // Limpiar BOM y espacios
                        $h = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h);
                        return strtolower(trim($h));
                    }, $row);

                    $codeIndex = array_search('codigo_producto', $headers);
                    $quantityIndex = array_search('cantidad', $headers);

                    if ($codeIndex === false || $quantityIndex === false) {
                        fclose($handle);
                        return response()->json([
                            'success' => false,
                            'message' => 'El archivo no tiene las columnas requeridas: codigo_producto, cantidad',
                        ], 422);
                    }
                    continue;
                }

                // Procesar fila de datos
                $code = trim($row[$codeIndex] ?? '');
                $quantity = trim($row[$quantityIndex] ?? '');

                // Saltar filas sin código
                if (empty($code)) {
                    continue;
                }

                // Validar cantidad
                if (!is_numeric($quantity) || (int) $quantity <= 0) {
                    // Si cantidad es 0 o vacía, simplemente saltar (útil para template con catálogo)
                    if (empty($quantity) || (int) $quantity === 0) {
                        continue;
                    }
                    $errors[] = "Fila {$rowNumber}: La cantidad debe ser un número mayor a 0.";
                    continue;
                }

                $quantity = (int) $quantity;

                // Agrupar por código (sumar cantidades si se repite)
                if (isset($productCodes[$code])) {
                    $productCodes[$code] += $quantity;
                } else {
                    $productCodes[$code] = $quantity;
                }
            }

            fclose($handle);

            if (empty($productCodes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron productos válidos en el archivo.',
                    'errors' => $errors,
                ], 422);
            }

            // Buscar productos en la base de datos
            $products = Product::whereIn('code', array_keys($productCodes))
                ->get()
                ->keyBy('code');

            foreach ($productCodes as $code => $quantity) {
                $product = $products->get($code);

                if (!$product) {
                    $errors[] = "Producto '{$code}' no encontrado en el sistema.";
                    continue;
                }

                $items[] = [
                    'product_id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'description' => $product->description,
                    'quantity_ordered' => $quantity,
                    'unit_price' => $product->list_price ?? 0,
                    'subtotal' => $quantity * ($product->list_price ?? 0),
                ];
            }

            return response()->json([
                'success' => true,
                'message' => count($items) . ' producto(s) importado(s) correctamente.',
                'items' => $items,
                'errors' => $errors,
                'summary' => [
                    'total_rows' => $rowNumber - 1, // -1 por el header
                    'imported' => count($items),
                    'failed' => count($errors),
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al importar productos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage(),
            ], 500);
        }
    }
}
