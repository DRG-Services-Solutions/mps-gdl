<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProductImportController extends Controller
{
    /**
     * Mostrar formulario de importación
     */
    public function showImportForm()
    {
        return view('products.import');
    }

    /**
     * Descargar template de Excel
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar nuevos encabezados
        $headers = [
            'code', 'name', 'tracking_type', 'supplier_name', 
            'product_type_name', 'category_name', 'brand_name', 
            'list_price', 'cost_price', 
            'is_composite', 'has_expiration_date',
            'requires_sterilization', 'requires_refrigeration', 'requires_temperature', 
            'status',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $sheet->getStyle('A1:O1')->applyFromArray($headerStyle); // Cambiado a O1

        // Filas de ejemplo adaptadas
        $examples = [
            [
                '16310199', 'Bloque de iliaco tricortical 20-27mm', 'lote', 'Biograft', 'Consumible', 'OSTEOSINTESIS', 'BIOGRAFT', 1500.50, 1700.00, 
                0, 1,
                1, 0, 0, 'activo',
            ],
            [
                'SET-HM-01', 'Set de Artroscopia de Hombro Básico', 'serial', 'Aesculap', 'Set', 'ARTROSCOPIA', 'Aesculap', 0, 0, 
                1, 0, 
                1, 0, 0, 'activo',
            ],
        ];

        $sheet->fromArray($examples, null, 'A2');

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instrucciones');

        $instructions = [
            ['INSTRUCCIONES DE IMPORTACIÓN DE CATÁLOGO MAESTRO'],
            [''],
            ['CAMPOS CLAVE DE ARQUITECTURA:'],
            [' - IS_COMPOSITE (0 o 1): Pon 1 SOLO si el producto es un Set, Kit, o Caja que contiene otras piezas.'],
            [' - HAS_EXPIRATION_DATE (0 o 1): Pon 1 si es un consumible que tiene fecha de caducidad.'],
            [''],
            ['TRACKING_TYPE:'],
            ['  - code: Control numérico general'],
            ['  - rfid: Etiquetas RFID'],
            ['  - serial: Número de serie único (Ej. Consolas, Motores)'],
            ['  - lote: Trazabilidad por lote (Ej. Consumibles, Gasas)'],
            [''],
            ['STATUS (Estado en el Catálogo):'],
            ['  - activo (Por defecto)'],
            ['  - inactivo'],
            ['  - descontinuado (Ya no se usará en el hospital)'],
        ];

        $instructionsSheet->fromArray($instructions, null, 'A1');
        $instructionsSheet->getColumnDimension('A')->setWidth(90);
        $instructionsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $filename = 'template_catalogo_' . now()->format('d-m-Y') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Pre-cargar todos los catálogos en memoria (una sola vez)
     */
    private function preloadCatalogs(): array
    {
        return [
            'suppliers'     => Supplier::all()->keyBy(fn($s) => strtolower(trim($s->name))),
            'brands'        => Brand::all()->keyBy(fn($b) => strtolower(trim($b->name))),
            'product_types' => ProductType::all(),
            'categories'    => Category::all(),
        ];
    }

    /**
     * Extraer todos los códigos del archivo y verificar cuáles ya existen en BD
     */
    private function getExistingCodes(array $rows, array $columnMap): \Illuminate\Support\Collection
    {
        $allCodes = [];

        foreach ($rows as $row) {
            if (!$this->isEmptyRow($row)) {
                $data = $this->mapRowData($row, $columnMap);
                if (!empty($data['code'])) {
                    $allCodes[] = $data['code'];
                }
            }
        }

        // Una sola query con whereIn en lugar de N queries individuales
        return Product::whereIn('code', $allCodes)->pluck('code')->flip();
    }

    /**
     * Preview de importación - OPTIMIZADO
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Quitar encabezado bruto
            $header = array_shift($rows);

            // =========================================================
            // 🚨 FIX: ANTI-BOM Y ANTI-CSV DE UNA SOLA COLUMNA
            // =========================================================
            
            // 1. Limpiar caracteres invisibles (BOM) del primer elemento
            if (isset($header[0])) {
                $header[0] = preg_replace('/^[\xEF\xBB\xBF\xE2\x80\x8B\s]+/', '', $header[0]);
            }

            // 2. Si el array solo detectó 1 columna, significa que el Excel/CSV pegó todo 
            // en la celda A1. Vamos a forzar la separación manualmente.
            if (count($header) === 1 && is_string($header[0])) {
                if (str_contains($header[0], ',')) {
                    $header = explode(',', $header[0]);
                } elseif (str_contains($header[0], ';')) {
                    $header = explode(';', $header[0]);
                }
            }

            // 3. Limpieza agresiva de caracteres invisibles en cada columna
            $header = array_map(function($h) {
                $clean = (string) $h;
                // Elimina espacios de no separación y caracteres basura
                $clean = preg_replace('/[\x00-\x1F\x7F\xA0\xE2\x80\x8B]/u', '', $clean); 
                return strtolower(trim($clean));
            }, $header);
            // =========================================================

            // Mapeo de columnas
            $columnMap = $this->getColumnMapping($header);

            // Guardar info de debug
            session(['import_debug_headers' => $header]);
            session(['import_debug_mapping' => $columnMap]);

            // =========================================================
            // VALIDACIÓN TEMPRANA CON TRAMPA DE DEBUG
            // =========================================================
            if (!isset($columnMap['code']) || !isset($columnMap['name'])) {
                // Si el formato es irreconocible, detenemos la ejecución y mostramos la radiografía:
                dd('🛑 TRAMPA DE DEBUG ACTIVADA', [
                    'Motivo' => 'El sistema no pudo emparejar "code" o "name".',
                    'Total_Columnas_Detectadas' => count($header),
                    'Array_Leido_Por_PHP_Puro' => $header,
                    'Mapa_Generado_Internamente' => $columnMap
                ]);
            }

            // ★ Pre-cargar catálogos (6 queries totales, sin importar cuántas filas)
            $catalogs = $this->preloadCatalogs();

            // ★ Verificar códigos existentes en una sola query
            $existingCodes = $this->getExistingCodes($rows, $columnMap);

            // Track de códigos duplicados dentro del mismo archivo
            $seenCodes = [];

            $validRows = [];
            $invalidRows = [];
            $rowNumber = 2;

            foreach ($rows as $row) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                // Si en el paso 2 tuvimos que separar por comas/punto y coma, 
                // debemos hacer lo mismo con cada fila de datos.
                if (count($row) === 1 && is_string($row[0])) {
                    if (str_contains($row[0], ',')) {
                        $row = explode(',', $row[0]);
                    } elseif (str_contains($row[0], ';')) {
                        $row = explode(';', $row[0]);
                    }
                }

                $data = $this->mapRowData($row, $columnMap);

                // Verificar duplicados dentro del mismo archivo
                $code = $data['code'] ?? null;
                $duplicateInFile = false;
                if (!empty($code)) {
                    if (isset($seenCodes[$code])) {
                        $duplicateInFile = true;
                    }
                    $seenCodes[$code] = $rowNumber;
                }

                // ★ Validar sin queries adicionales
                $validation = $this->validateRowOptimized(
                    $data,
                    $rowNumber,
                    $catalogs,
                    $existingCodes,
                    $duplicateInFile
                );

                if ($validation['valid']) {
                    $validRows[] = [
                        'row'       => $rowNumber,
                        'data'      => $data,
                        'processed' => $validation['processed'],
                        'relations' => $validation['relations'],
                    ];
                } else {
                    $invalidRows[] = [
                        'row'    => $rowNumber,
                        'data'   => $data,
                        'errors' => $validation['errors'],
                    ];
                }

                $rowNumber++;
            }

            // Guardar en sesión
            session([
                'import_preview_valid'   => $validRows,
                'import_preview_invalid' => $invalidRows,
            ]);

            return view('products.import-preview', compact('validRows', 'invalidRows'));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar importación desde preview - OPTIMIZADO
     */
    public function confirmImport(Request $request)
    {
        if (!session()->has('import_preview_valid')) {
            return redirect()
                ->route('products.import.form')
                ->with('error', 'No hay datos para importar. Por favor suba el archivo nuevamente.');
        }

        try {
            DB::beginTransaction();

            $validRows = session('import_preview_valid', []);
            $imported = 0;
            $errors = [];

            // Cache para suppliers y brands nuevos ya creados en esta importación
            $createdSuppliers = [];
            $createdBrands = [];

            foreach ($validRows as $row) {
                try {
                    $data = $row['processed'];

                    // Crear supplier si es nuevo
                    if (!empty($data['_new_supplier'])) {
                        $supplierName = $data['_new_supplier'];
                        $supplierKey = strtolower(trim($supplierName));

                        if (isset($createdSuppliers[$supplierKey])) {
                            $data['supplier_id'] = $createdSuppliers[$supplierKey];
                        } else {
                            $supplier = Supplier::firstOrCreate(
                                ['name' => $supplierName],
                                [
                                    'code'      => 'SUP-' . strtoupper(substr($supplierName, 0, 3)) . '-' . rand(1000, 9999),
                                    'is_active' => true,
                                ]
                            );
                            $data['supplier_id'] = $supplier->id;
                            $createdSuppliers[$supplierKey] = $supplier->id;
                        }
                    }

                    // Crear brand si es nuevo
                    if (!empty($data['_new_brand'])) {
                        $brandName = $data['_new_brand'];
                        $brandKey = strtolower(trim($brandName));

                        if (isset($createdBrands[$brandKey])) {
                            $data['brand_id'] = $createdBrands[$brandKey];
                        } else {
                            $brand = Brand::firstOrCreate(
                                ['name' => $brandName],
                                ['is_active' => true]
                            );
                            $data['brand_id'] = $brand->id;
                            $createdBrands[$brandKey] = $brand->id;
                        }
                    }

                    // Limpiar campos temporales antes de crear
                    unset($data['_new_supplier'], $data['_new_brand']);

                    Product::create($data);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Fila {$row['row']}: " . $e->getMessage();
                }
            }

            DB::commit();

            if ($imported === 0 && count($errors) > 0) {
                dd('🛑 LA BASE DE DATOS RECHAZÓ LA INSERCIÓN', [
                    'Errores detectados:' => $errors,
                    'Datos de la última fila que intentó guardar:' => $data ?? 'Ninguna'
                ]);
            }
            
            // Limpiar sesión
            session()->forget([
                'import_preview_valid',
                'import_preview_invalid',
                'import_debug_headers',
                'import_debug_mapping',
            ]);

            $message = "Importación completada: {$imported} productos importados";

            return redirect()
                ->route('products.index')
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error en la importación: ' . $e->getMessage());
        }
    }

    /**
     * Importar productos directamente (sin preview) - OPTIMIZADO
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $header = array_shift($rows);
            $header = array_map(function($h) {
                $clean = (string) $h;
                $clean = preg_replace('/[\xEF\xBB\xBF]/', '', $clean);
                return strtolower(trim($clean));
            }, $header);
            

            $columnMap = $this->getColumnMapping($header);

            //Pre-cargar catálogos
            $catalogs = $this->preloadCatalogs();

            // Verificar códigos existentes en batch
            $existingCodes = $this->getExistingCodes($rows, $columnMap);

            // Cache para suppliers y brands nuevos
            $createdSuppliers = [];
            $createdBrands = [];
            $seenCodes = [];

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $rowNumber = 2;

            foreach ($rows as $row) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $data = $this->mapRowData($row, $columnMap);

                // Verificar duplicados dentro del mismo archivo
                $code = $data['code'] ?? null;
                $duplicateInFile = false;
                if (!empty($code)) {
                    if (isset($seenCodes[$code])) {
                        $duplicateInFile = true;
                    }
                    $seenCodes[$code] = $rowNumber;
                }

                $validation = $this->validateRowOptimized(
                    $data,
                    $rowNumber,
                    $catalogs,
                    $existingCodes,
                    $duplicateInFile
                );

                if ($validation['valid']) {
                    try {
                        $processedData = $validation['processed'];

                        // Crear supplier si es nuevo (con cache local)
                        if (!empty($processedData['_new_supplier'])) {
                            $supplierName = $processedData['_new_supplier'];
                            $supplierKey = strtolower(trim($supplierName));

                            if (isset($createdSuppliers[$supplierKey])) {
                                $processedData['supplier_id'] = $createdSuppliers[$supplierKey];
                            } else {
                                $supplier = Supplier::firstOrCreate(
                                    ['name' => $supplierName],
                                    [
                                        'code'      => 'SUP-' . strtoupper(substr($supplierName, 0, 3)) . '-' . rand(1000, 9999),
                                        'is_active' => true,
                                    ]
                                );
                                $processedData['supplier_id'] = $supplier->id;
                                $createdSuppliers[$supplierKey] = $supplier->id;

                                // Actualizar catálogo en memoria
                                $catalogs['suppliers']->put($supplierKey, $supplier);
                            }
                        }

                        // Crear brand si es nuevo (con cache local)
                        if (!empty($processedData['_new_brand'])) {
                            $brandName = $processedData['_new_brand'];
                            $brandKey = strtolower(trim($brandName));

                            if (isset($createdBrands[$brandKey])) {
                                $processedData['brand_id'] = $createdBrands[$brandKey];
                            } else {
                                $brand = Brand::firstOrCreate(
                                    ['name' => $brandName],
                                    ['is_active' => true]
                                );
                                $processedData['brand_id'] = $brand->id;
                                $createdBrands[$brandKey] = $brand->id;

                                // Actualizar catálogo en memoria
                                $catalogs['brands']->put($brandKey, $brand);
                            }
                        }

                        // Limpiar campos temporales
                        unset($processedData['_new_supplier'], $processedData['_new_brand']);

                        Product::create($processedData);
                        $imported++;

                        // Agregar código al set de existentes para detectar duplicados
                        if (!empty($processedData['code'])) {
                            $existingCodes->put($processedData['code'], true);
                        }
                    } catch (\Exception $e) {
                        dd('Error fatal en Fila ' . $rowNumber, $e->getMessage(), 'Datos que intentó guardar:', $processedData);
                        $errors[] = "Fila {$rowNumber}: " . $e->getMessage();
                        $skipped++;
                    }
                } else {
                    $errors[] = "Fila {$rowNumber}: " . implode(', ', $validation['errors']);
                    $skipped++;
                }

                $rowNumber++;
            }

            DB::commit();

            $message = "Importación completada: {$imported} productos importados";
            if ($skipped > 0) {
                $message .= ", {$skipped} omitidos";
            }

            return redirect()
                ->route('products.index')
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error en la importación: ' . $e->getMessage());
        }
    }

   
    /**
     * Mapeo de columnas con limpieza "Nuclear" (A prueba de Excel y CSV)
     */
    private function getColumnMapping($header)
    {
        $map = [];

        $expectedColumns = [
            'code'                    => ['code', 'codigo', 'sku', 'clave'],
            'name'                    => ['name', 'nombre', 'productname', 'producto'],
            'tracking_type'           => ['trackingtype', 'tiporastreo', 'rastreo'],
            'supplier_name'           => ['suppliername', 'proveedor', 'supplier'],
            'product_type_name'       => ['producttypename', 'producttype', 'tipoproducto'],
            'category_name'           => ['categoryname', 'categoria', 'category'],
            'brand_name'              => ['brandname', 'marca', 'brand'],
            'list_price'              => ['listprice', 'precio', 'price', 'costo'],
            'cost_price'              => ['costprice', 'costo', 'cost'],
            'is_composite'            => ['iscomposite', 'escompuesto', 'esset', 'eskit', 'compuesto'],
            'has_expiration_date'     => ['hasexpirationdate', 'tienecaducidad', 'caduca', 'caducidad'],
            'requires_sterilization'  => ['requiressterilization', 'esterilizacion'],
            'requires_refrigeration'  => ['requiresrefrigeration', 'refrigeracion'],
            'requires_temperature'    => ['requirestemperature', 'temperatura'],
            'status'                  => ['status', 'estado'],
        ];

        foreach ($header as $index => $columnName) {
            $normalized = strtolower(trim((string) $columnName));
            $normalized = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $normalized);
            
            $normalized = preg_replace('/[^a-z0-9]/', '', $normalized);

            if (empty($normalized)) continue;

            foreach ($expectedColumns as $field => $aliases) {
                foreach ($aliases as $alias) {
                    $normalizedAlias = strtolower(preg_replace('/[^a-z0-9]/', '', $alias));

                    if ($normalized === $normalizedAlias) {
                        $map[$field] = $index;
                        break 2; 
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Mapear datos de fila según el mapeo de columnas
     */
    private function mapRowData($row, $columnMap)
    {
        $data = [];

        foreach ($columnMap as $field => $index) {
            $data[$field] = isset($row[$index]) ? trim($row[$index]) : null;
        }

        return $data;
    }

    /**
     * Verificar si fila está vacía
     */
    private function isEmptyRow($row)
    {
        return empty(array_filter($row, function ($cell) {
            return !empty(trim($cell ?? ''));
        }));
    }

    /**
     * Validar fila OPTIMIZADO - Sin queries a BD
     * Usa catálogos pre-cargados en memoria
     */
    private function validateRowOptimized(
        array $data,
        int $rowNumber,
        array &$catalogs,
        \Illuminate\Support\Collection $existingCodes,
        bool $duplicateInFile = false
    ): array {
        $errors = [];
        $processed = [];
        $relations = [
            'supplier_name'     => null,
            'brand_name'        => null,
            'product_type_name' => null,
            'category_name'     => null,
        ];

        // ──────────────────────────────────────────
        // CAMPOS OBLIGATORIOS
        // ──────────────────────────────────────────

        // Code (obligatorio, único en BD y en archivo)
        if (empty($data['code'])) {
            $errors[] = 'El código es obligatorio';
        } elseif ($existingCodes->has($data['code'])) {
            $errors[] = "El código '{$data['code']}' ya existe en el sistema";
        } elseif ($duplicateInFile) {
            $errors[] = "El código '{$data['code']}' está duplicado en el archivo";
        } else {
            $processed['code'] = $data['code'];
        }

        // Name (obligatorio)
        if (empty($data['name'])) {
            $errors[] = 'El nombre es obligatorio';
        } else {
            $processed['name'] = $data['name'];
        }

        // Tracking type (obligatorio)
        $validTrackingTypes = ['code', 'rfid', 'lote'];
        $trackingType = strtolower(trim($data['tracking_type'] ?? ''));

        if (empty($trackingType)) {
            $errors[] = 'El tipo de rastreo es obligatorio';
        } elseif (!in_array($trackingType, $validTrackingTypes)) {
            $errors[] = "Tipo de rastreo '{$trackingType}' inválido. Use: code, rfid o lote";
        } else {
            $processed['tracking_type'] = $trackingType;
        }

        // Product Type (obligatorio)
        if (empty($data['product_type_name'])) {
            $errors[] = 'El tipo de producto es obligatorio (Consumible / Instrumental)';
        } else {
            $name = strtolower(trim($data['product_type_name']));

            $productType = $catalogs['product_types']->first(function ($pt) use ($name) {
                return strtolower($pt->name) === $name;
            });

            // Búsqueda parcial si no hay match exacto
            if (!$productType) {
                $productType = $catalogs['product_types']->first(function ($pt) use ($name) {
                    return str_contains(strtolower($pt->name), $name);
                });
            }

            if ($productType) {
                $processed['product_type_id'] = $productType->id;
                $relations['product_type_name'] = $productType->name;
            } else {
                $available = $catalogs['product_types']->pluck('name')->implode(', ');
                $errors[] = "Tipo de producto '{$data['product_type_name']}' no encontrado. Disponibles: {$available}";
            }
        }

        // ──────────────────────────────────────────
        // CAMPOS OPCIONALES - RELACIONES
        // ──────────────────────────────────────────

        // Supplier (lookup en memoria, marcar como nuevo si no existe)
        $processed['supplier_id'] = null;
        if (!empty($data['supplier_name'])) {
            $key = strtolower(trim($data['supplier_name']));
            $supplier = $catalogs['suppliers']->get($key);

            if ($supplier) {
                $processed['supplier_id'] = $supplier->id;
                $relations['supplier_name'] = $supplier->name;
            } else {
                // Se creará al confirmar importación
                $processed['_new_supplier'] = $data['supplier_name'];
                $relations['supplier_name'] = $data['supplier_name'] . ' (nuevo)';
            }
        }

        // Brand (lookup en memoria, marcar como nuevo si no existe)
        $processed['brand_id'] = null;
        if (!empty($data['brand_name'])) {
            $key = strtolower(trim($data['brand_name']));
            $brand = $catalogs['brands']->get($key);

            if ($brand) {
                $processed['brand_id'] = $brand->id;
                $relations['brand_name'] = $brand->name;
            } else {
                // Se creará al confirmar importación
                $processed['_new_brand'] = $data['brand_name'];
                $relations['brand_name'] = $data['brand_name'] . ' (nuevo)';
            }
        }

        // Category (búsqueda en colección en memoria)
        $processed['category_id'] = null;
        if (!empty($data['category_name'])) {
            $name = strtolower(trim($data['category_name']));

            // Match exacto primero
            $category = $catalogs['categories']->first(function ($c) use ($name) {
                return strtolower($c->name) === $name;
            });

            // Match parcial si no hay exacto
            if (!$category) {
                $category = $catalogs['categories']->first(function ($c) use ($name) {
                    return str_contains(strtolower($c->name), $name);
                });
            }

            if ($category) {
                $processed['category_id'] = $category->id;
                $relations['category_name'] = $category->name;
            } else {
                $available = $catalogs['categories']->pluck('name')->sort()->take(10)->implode(', ');
                $total = $catalogs['categories']->count();
                $moreText = $total > 10 ? " (y " . ($total - 10) . " más)" : "";
                $errors[] = "Categoría '{$data['category_name']}' no encontrada. Disponibles: {$available}{$moreText}";
            }
        }

        

        // ──────────────────────────────────────────
        // CAMPOS NUMÉRICOS Y BOOLEANOS
        // ──────────────────────────────────────────
        $processed['list_price'] = !empty($data['list_price']) ? (float) $data['list_price'] : 0;
        $processed['cost_price'] = !empty($data['cost_price']) ? (float) $data['cost_price'] : 0;
        
        // Trazabilidad (Ajustado para incluir 'serial' y 'lote')
        $validTrackingTypes = ['code', 'rfid', 'lote', 'serial'];
        $trackingType = strtolower(trim($data['tracking_type'] ?? ''));
        if (empty($trackingType)) {
            $errors[] = 'El tipo de rastreo es obligatorio';
        } elseif (!in_array($trackingType, $validTrackingTypes)) {
            $errors[] = "Tipo de rastreo '{$trackingType}' inválido. Use: code, rfid, lote o serial";
        } else {
            $processed['tracking_type'] = $trackingType;
        }

        // Parseo estricto de Booleanos
        $processed['is_composite'] = $this->parseBoolean($data['is_composite'] ?? 0);
        $processed['has_expiration_date'] = $this->parseBoolean($data['has_expiration_date'] ?? 0);
        $processed['requires_sterilization'] = $this->parseBoolean($data['requires_sterilization'] ?? 0);
        $processed['requires_refrigeration'] = $this->parseBoolean($data['requires_refrigeration'] ?? 0);
        $processed['requires_temperature'] = $this->parseBoolean($data['requires_temperature'] ?? 0);

        // Status (Mapeo seguro de Español a la DB en Inglés)
        $statusMap = [
            'activo' => 'active', 'active' => 'active',
            'inactivo' => 'inactive', 'inactive' => 'inactive',
            'descontinuado' => 'discontinued', 'obsoleto' => 'discontinued', 'discontinued' => 'discontinued'
        ];
        $inputStatus = strtolower(trim($data['status'] ?? 'activo'));
        $processed['status'] = $statusMap[$inputStatus] ?? 'activo';

        return [
            'valid'     => empty($errors),
            'errors'    => $errors,
            'processed' => $processed,
            'relations' => $relations,
        ];
    }

    /**
     * Convertir valor a booleano
     */
    private function parseBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        $value = strtolower(trim($value ?? ''));
        return in_array($value, ['1', 'true', 'yes', 'si', 'sí']);
    }
}