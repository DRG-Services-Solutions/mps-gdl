<?php

namespace App\Services;

use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PriceListImportService
{
    /**
     * Parsear CSV y clasificar productos en encontrados / no encontrados.
     * No guarda nada, solo devuelve la vista previa.
     *
     * @return array{found: array, not_found: array, errors: array, total: int}
     */
    public function parseCSV(string $filePath, string $delimiter = ','): array
    {
        $found = [];
        $notFound = [];
        $errors = [];
        $row = 0;

        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \RuntimeException('No se pudo abrir el archivo CSV.');
        }

        // Detectar si la primera fila es header
        $firstLine = fgetcsv($handle, 0, $delimiter);
        $row++;

        // Si la primera columna parece un código de producto (tiene números), es data, no header
        $isHeader = !is_numeric(trim($firstLine[0] ?? ''));

        if (!$isHeader) {
            // Rebobinar para procesar la primera línea como data
            rewind($handle);
            $row = 0;
        }

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row++;

            // Mínimo 2 columnas: código, precio
            if (count($data) < 2) {
                $errors[] = [
                    'row' => $row,
                    'message' => 'Fila incompleta, se requiere código y precio.',
                    'raw' => implode($delimiter, $data),
                ];
                continue;
            }

            $code = trim($data[0]);
            $price = trim($data[1]);
            $notes = isset($data[2]) ? trim($data[2]) : null;

            // Validar que el código no esté vacío
            if (empty($code)) {
                $errors[] = [
                    'row' => $row,
                    'message' => 'Código de producto vacío.',
                    'raw' => implode($delimiter, $data),
                ];
                continue;
            }

            // Validar que el precio sea numérico
            $price = str_replace(['$', ',', ' '], '', $price);
            if (!is_numeric($price) || floatval($price) < 0) {
                $errors[] = [
                    'row' => $row,
                    'message' => "Precio inválido: '{$data[1]}'",
                    'raw' => implode($delimiter, $data),
                    'code' => $code,
                ];
                continue;
            }

            $unitPrice = round(floatval($price), 2);

            // Buscar producto por código
            $product = Product::where('code', $code)->first();

            if ($product) {
                $found[] = [
                    'row' => $row,
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'notes' => $notes,
                ];
            } else {
                $notFound[] = [
                    'row' => $row,
                    'code' => $code,
                    'unit_price' => $unitPrice,
                    'notes' => $notes,
                ];
            }
        }

        fclose($handle);

        return [
            'found' => $found,
            'not_found' => $notFound,
            'errors' => $errors,
            'total' => $row - ($isHeader ? 1 : 0),
        ];
    }

    /**
     * Ejecutar la importación confirmada.
     *
     * @param PriceList $priceList
     * @param array $foundItems       Items encontrados a importar [{product_id, unit_price, notes}]
     * @param array $createItems      Items no encontrados que el usuario quiere crear [{code, name, unit_price, notes}]
     * @return array{imported: int, created: int, skipped: int}
     */
    public function executeImport(PriceList $priceList, array $foundItems, array $createItems = []): array
    {
        $imported = 0;
        $created = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            // 1. Importar productos encontrados
            foreach ($foundItems as $item) {
                PriceListItem::updateOrCreate(
                    [
                        'price_list_id' => $priceList->id,
                        'product_id' => $item['product_id'],
                    ],
                    [
                        'unit_price' => $item['unit_price'],
                        'notes' => $item['notes'] ?? null,
                    ]
                );
                $imported++;
            }

            // 2. Crear productos nuevos y agregarlos a la lista
            foreach ($createItems as $item) {
                // Crear el producto en el catálogo
                $product = Product::create([
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'is_active' => true,
                ]);

                // Agregar a la lista de precios
                PriceListItem::create([
                    'price_list_id' => $priceList->id,
                    'product_id' => $product->id,
                    'unit_price' => $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                ]);

                $created++;
            }

            DB::commit();

            Log::info("Importación a lista {$priceList->code}: {$imported} importados, {$created} creados.");

            return [
                'imported' => $imported,
                'created' => $created,
                'skipped' => $skipped,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en importación de lista de precios: " . $e->getMessage());
            throw $e;
        }
    }
}
