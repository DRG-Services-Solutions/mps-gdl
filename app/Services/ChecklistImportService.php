<?php

namespace App\Services;

use App\Models\SurgicalChecklist;
use App\Models\ChecklistItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ChecklistImportService
{
    /**
     * Columnas esperadas en el Excel (en orden)
     */
    protected array $expectedColumns = [
        'checklist_code',   // Código del checklist (agrupa filas)
        'surgery_type',     // Tipo de cirugía
        'product_sku',      // SKU del producto
        'quantity',         // Cantidad
        'is_mandatory',     // ¿Obligatorio? (Sí/No)
        'notes',            // Notas (opcional)
    ];

    /**
     * Parsear y validar el archivo Excel.
     * Retorna datos estructurados para preview.
     */
    public function parseAndValidate(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Validar que haya datos
        if (count($rows) < 2) {
            return [
                'success' => false,
                'error' => 'El archivo está vacío o solo tiene encabezados.',
                'checklists' => [],
                'errors' => [],
                'warnings' => [],
                'stats' => [],
            ];
        }

        // Quitar header
        $header = array_shift($rows);
        $headerNormalized = array_map(fn($h) => strtolower(trim($h ?? '')), array_values($header));

        // Validar columnas mínimas
        $missingCols = $this->validateColumns($headerNormalized);
        if (!empty($missingCols)) {
            return [
                'success' => false,
                'error' => 'Columnas faltantes: ' . implode(', ', $missingCols),
                'checklists' => [],
                'errors' => [],
                'warnings' => [],
                'stats' => [],
            ];
        }

        // Mapear índices de columnas
        $colMap = $this->mapColumns($headerNormalized);

        // Parsear filas
        $errors = [];
        $warnings = [];
        $parsedRows = [];

        foreach ($rows as $rowIndex => $row) {
            $rowNum = $rowIndex + 1; // +1 porque quitamos header pero el array mantiene keys
            $rowValues = array_values($row);

            // Saltar filas completamente vacías
            if ($this->isEmptyRow($rowValues)) {
                continue;
            }

            $parsed = $this->parseRow($rowValues, $colMap, $rowNum, $errors, $warnings);
            if ($parsed) {
                $parsedRows[] = $parsed;
            }
        }

        if (empty($parsedRows) && !empty($errors)) {
            return [
                'success' => false,
                'error' => 'No se pudo parsear ninguna fila válida.',
                'checklists' => [],
                'errors' => $errors,
                'warnings' => $warnings,
                'stats' => [],
            ];
        }

        // Agrupar por checklist_code
        $grouped = collect($parsedRows)->groupBy('checklist_code');

        // Validar productos contra la BD
        $allSkus = collect($parsedRows)->pluck('product_sku')->unique()->values();
        $existingProducts = Product::whereIn('sku', $allSkus)->pluck('id', 'sku');

        $missingSkus = $allSkus->diff($existingProducts->keys());
        foreach ($missingSkus as $sku) {
            $affectedRows = collect($parsedRows)
                ->where('product_sku', $sku)
                ->pluck('row_number')
                ->implode(', ');
            $errors[] = "SKU '{$sku}' no existe en el catálogo de productos (filas: {$affectedRows}).";
        }

        // Validar checklists duplicados en BD
        $existingCodes = SurgicalChecklist::whereIn('code', $grouped->keys())->pluck('code');
        foreach ($existingCodes as $code) {
            $warnings[] = "El checklist '{$code}' ya existe. Se omitirá su creación (los items tampoco se crearán).";
        }

        // Construir estructura de preview
        $checklists = [];
        foreach ($grouped as $code => $items) {
            $firstItem = $items->first();
            $isExisting = $existingCodes->contains($code);

            $checklistItems = [];
            foreach ($items as $item) {
                $productId = $existingProducts->get($item['product_sku']);
                $checklistItems[] = [
                    'product_sku' => $item['product_sku'],
                    'product_id' => $productId,
                    'product_exists' => $productId !== null,
                    'quantity' => $item['quantity'],
                    'is_mandatory' => $item['is_mandatory'],
                    'notes' => $item['notes'],
                    'row_number' => $item['row_number'],
                ];
            }

            // Detectar SKUs duplicados dentro del mismo checklist
            $skuCounts = collect($checklistItems)->countBy('product_sku');
            foreach ($skuCounts->filter(fn($count) => $count > 1) as $sku => $count) {
                $warnings[] = "El producto '{$sku}' aparece {$count} veces en el checklist '{$code}'. Solo se tomará la primera aparición.";
            }

            $checklists[] = [
                'code' => $code,
                'surgery_type' => $firstItem['surgery_type'],
                'already_exists' => $isExisting,
                'items' => $checklistItems,
                'items_count' => count($checklistItems),
                'valid_items_count' => collect($checklistItems)->where('product_exists', true)->count(),
            ];
        }

        $hasBlockingErrors = collect($checklists)
            ->reject(fn($c) => $c['already_exists'])
            ->flatMap(fn($c) => $c['items'])
            ->contains(fn($item) => !$item['product_exists']);

        $stats = [
            'total_rows' => count($parsedRows),
            'total_checklists' => count($checklists),
            'new_checklists' => collect($checklists)->where('already_exists', false)->count(),
            'skipped_checklists' => collect($checklists)->where('already_exists', true)->count(),
            'total_items' => collect($checklists)->sum('items_count'),
            'valid_items' => collect($checklists)->sum('valid_items_count'),
        ];

        return [
            'success' => !$hasBlockingErrors,
            'error' => $hasBlockingErrors ? 'Hay productos con SKU no encontrado. Corrígelos antes de importar.' : null,
            'checklists' => $checklists,
            'errors' => $errors,
            'warnings' => $warnings,
            'stats' => $stats,
        ];
    }

    /**
     * Ejecutar la importación después del preview.
     */
    public function executeImport(array $checklists): array
    {
        $created = 0;
        $skipped = 0;
        $itemsCreated = 0;
        $itemErrors = [];

        DB::beginTransaction();

        try {
            foreach ($checklists as $checklistData) {
                // Omitir los que ya existen
                if ($checklistData['already_exists']) {
                    $skipped++;
                    continue;
                }

                // Crear checklist
                $checklist = SurgicalChecklist::create([
                    'code' => $checklistData['code'],
                    'surgery_type' => $checklistData['surgery_type'],
                    'status' => 'active',
                ]);

                // Crear items (solo con productos válidos, sin duplicados)
                $addedSkus = [];
                $order = 1;

                foreach ($checklistData['items'] as $itemData) {
                    if (!$itemData['product_exists']) {
                        $itemErrors[] = "Fila {$itemData['row_number']}: SKU '{$itemData['product_sku']}' no encontrado, item omitido.";
                        continue;
                    }

                    // Evitar duplicados dentro del mismo checklist
                    if (in_array($itemData['product_sku'], $addedSkus)) {
                        continue;
                    }

                    $checklist->items()->create([
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'is_mandatory' => $itemData['is_mandatory'],
                        'order' => $order++,
                        'notes' => $itemData['notes'],
                    ]);

                    $addedSkus[] = $itemData['product_sku'];
                    $itemsCreated++;
                }

                $created++;
            }

            DB::commit();

            Log::info("Importación masiva de checklists completada", [
                'created' => $created,
                'skipped' => $skipped,
                'items_created' => $itemsCreated,
            ]);

            return [
                'success' => true,
                'created' => $created,
                'skipped' => $skipped,
                'items_created' => $itemsCreated,
                'item_errors' => $itemErrors,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en importación masiva de checklists", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Error durante la importación: ' . $e->getMessage(),
                'created' => 0,
                'skipped' => 0,
                'items_created' => 0,
                'item_errors' => [],
            ];
        }
    }

    // ==================== MÉTODOS PRIVADOS ====================

    protected function validateColumns(array $header): array
    {
        $required = ['checklist_code', 'surgery_type', 'product_sku', 'quantity'];
        $missing = [];

        foreach ($required as $col) {
            if (!in_array($col, $header)) {
                $missing[] = $col;
            }
        }

        return $missing;
    }

    protected function mapColumns(array $header): array
    {
        $map = [];
        foreach ($this->expectedColumns as $col) {
            $index = array_search($col, $header);
            $map[$col] = $index !== false ? $index : null;
        }
        return $map;
    }

    protected function isEmptyRow(array $row): bool
    {
        return collect($row)->every(fn($val) => $val === null || trim((string)$val) === '');
    }

    protected function parseRow(array $row, array $colMap, int $rowNum, array &$errors, array &$warnings): ?array
    {
        $get = fn(string $col) => isset($colMap[$col]) && isset($row[$colMap[$col]])
            ? trim((string)$row[$colMap[$col]])
            : null;

        $checklistCode = $get('checklist_code');
        $surgeryType = $get('surgery_type');
        $productSku = $get('product_sku');
        $quantity = $get('quantity');
        $isMandatory = $get('is_mandatory');
        $notes = $get('notes');

        // Validaciones
        if (empty($checklistCode)) {
            $errors[] = "Fila {$rowNum}: 'checklist_code' es requerido.";
            return null;
        }

        if (empty($productSku)) {
            $errors[] = "Fila {$rowNum}: 'product_sku' es requerido.";
            return null;
        }

        if (empty($quantity) || !is_numeric($quantity) || (int)$quantity < 1) {
            $errors[] = "Fila {$rowNum}: 'quantity' debe ser un número entero mayor a 0 (valor: '{$quantity}').";
            return null;
        }

        if (empty($surgeryType)) {
            $warnings[] = "Fila {$rowNum}: 'surgery_type' vacío, se usará el valor de la primera fila del grupo.";
        }

        // Parsear is_mandatory
        $mandatoryValue = true; // default
        if ($isMandatory !== null && $isMandatory !== '') {
            $lower = strtolower($isMandatory);
            $mandatoryValue = in_array($lower, ['sí', 'si', 'yes', '1', 'true', 'obligatorio']);
        }

        return [
            'checklist_code' => $checklistCode,
            'surgery_type' => $surgeryType ?: '',
            'product_sku' => $productSku,
            'quantity' => (int)$quantity,
            'is_mandatory' => $mandatoryValue,
            'notes' => $notes,
            'row_number' => $rowNum,
        ];
    }
}
