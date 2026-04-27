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
use Illuminate\Support\Facades\Log;
use App\Models\ProductSubCategory;
use App\Models\ProductSubProduct;

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

        // Encabezados
        $headers = [
            'code', 'name', 'tracking_type', 'supplier_name', 
            'product_type_name', 'category_name', 'brand_name',  
            'list_price', 'cost_price',
            'sub_category_name', 'product_sub_product_name', 
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
            'suppliers' => Supplier::all()->keyBy(fn($s) => strtolower(trim($s->name))),
            'brands'    => Brand::all()->keyBy(fn($b) => strtolower(trim($b->name))),
            
            // Product Types: Asegúrate que el modelo apunte a 'product_types'
            'product_types' => DB::table('product_types')->get()->keyBy(fn($pt) => strtolower(trim($pt->name))),
            
            // Categories: Según tu migración es 'product_categories'
            'categories' => DB::table('product_categories')->get()->keyBy(fn($c) => strtolower(trim($c->name))),
            
            // Sub Categorías: CORRECCIÓN AQUÍ
            'sub_categories' => DB::table('product_sub_categories')->get()->keyBy(fn($sc) => strtolower(trim($sc->name))),
            
            // Sub Productos: Según tu migración es 'product_sub_products'
            'product_sub_products' => DB::table('product_sub_products')->get()->keyBy(fn($psp) => strtolower(trim($psp->name))),
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

        // Identificador único para rastrear esta sesión de importación en el log
        $importId = uniqid('imp_');

        try {
            $file = $request->file('file');
            Log::channel('import')->info("[$importId] Inicio de previsualización", [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);

            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $header = array_shift($rows);

            // --- Limpieza de Headers (Tu lógica actual mejorada con Log) ---
            if (isset($header[0])) {
                $header[0] = preg_replace('/^[\xEF\xBB\xBF\xE2\x80\x8B\s]+/', '', $header[0]);
            }

            if (count($header) === 1 && is_string($header[0])) {
                $delimiter = str_contains($header[0], ',') ? ',' : (str_contains($header[0], ';') ? ';' : null);
                if ($delimiter) {
                    $header = explode($delimiter, $header[0]);
                    Log::channel('import')->debug("[$importId] Delimitador detectado: $delimiter");
                }
            }

            $header = array_map(function($h) {
                $clean = preg_replace('/[\x00-\x1F\x7F\xA0\xE2\x80\x8B]/u', '', (string)$h); 
                return strtolower(trim($clean));
            }, $header);

            $columnMap = $this->getColumnMapping($header);

            // LOG CRÍTICO: Fallo de mapeo
            if (!isset($columnMap['code']) || !isset($columnMap['name'])) {
                Log::channel('import')->warning("[$importId] Fallo de mapeo de columnas clave", [
                    'headers_recibidos' => $header,
                    'mapa_generado' => $columnMap
                ]);
                // Opcionalmente lanzar excepción para caer en el catch y avisar al usuario
                throw new \Exception('No se encontraron las columnas "code" o "name". Verifique los encabezados.');
            }

            $catalogs = $this->preloadCatalogs();
            $existingCodes = $this->getExistingCodes($rows, $columnMap);
            $seenCodes = [];
            $validRows = [];
            $invalidRows = [];
            $rowNumber = 2;

            foreach ($rows as $row) {
                if ($this->isEmptyRow($row)) continue;

                // Re-aplicar lógica de delimitador si es necesario
                if (count($row) === 1 && is_string($row[0])) {
                    if (str_contains($row[0], ',')) $row = explode(',', $row[0]);
                    elseif (str_contains($row[0], ';')) $row = explode(';', $row[0]);
                }

                $data = $this->mapRowData($row, $columnMap);
                $code = $data['code'] ?? null;
                $duplicateInFile = false;

                if (!empty($code)) {
                    if (isset($seenCodes[$code])) {
                        $duplicateInFile = true;
                    }
                    $seenCodes[$code] = $rowNumber;
                }

                $validation = $this->validateRowOptimized($data, $rowNumber, $catalogs, $existingCodes, $duplicateInFile);

                if ($validation['valid']) {
                    $validRows[] = [
                        'row' => $rowNumber,
                        'data' => $data,
                        'processed' => $validation['processed'],
                        'relations' => $validation['relations'],
                    ];
                } else {
                    $invalidRows[] = [
                        'row' => $rowNumber,
                        'data' => $data,
                        'errors' => $validation['errors'],
                    ];

                    // LOG DE FILA INVÁLIDA: Solo si quieres detalle exhaustivo en log
                    Log::channel('import')->debug("[$importId] Fila $rowNumber inválida", [
                        'errores' => $validation['errors'],
                        'data_parcial' => $data
                    ]);
                }
                $rowNumber++;
            }

            session([
                'import_preview_valid'   => $validRows,
                'import_preview_invalid' => $invalidRows,
            ]);

            Log::channel('import')->info("[$importId] Previsualización completada", [
                'validas' => count($validRows),
                'invalidas' => count($invalidRows)
            ]);

            return view('products.import-preview', compact('validRows', 'invalidRows'));

        } catch (\Exception $e) {
            // LOG DE ERROR DE SISTEMA: Crucial para debuggear fallos de PHP o Excel
            Log::channel('import')->critical("[$importId] ERROR CRÍTICO EN IMPORTACIÓN", [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

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

            // Cache para elementos nuevos ya creados en esta importación
            $createdSuppliers = [];
            $createdBrands = [];
            $createdCategories = [];
            $createdSubCategories = [];
            $createdSubProducts = [];

            foreach ($validRows as $row) {
                try {
                    $data = $row['processed'];

                    // 1. Crear Supplier
                    if (!empty($data['_new_supplier'])) {
                        $name = $data['_new_supplier'];
                        $key = strtolower(trim($name));

                        if (isset($createdSuppliers[$key])) {
                            $data['supplier_id'] = $createdSuppliers[$key];
                        } else {
                            $supplier = \App\Models\Supplier::firstOrCreate(
                                ['name' => $name],
                                [
                                    'code'      => 'SUP-' . strtoupper(substr($name, 0, 3)) . '-' . rand(1000, 9999),
                                    'is_active' => true,
                                ]
                            );
                            $data['supplier_id'] = $supplier->id;
                            $createdSuppliers[$key] = $supplier->id;
                        }
                    }

                    // 2. Crear Brand
                    if (!empty($data['_new_brand'])) {
                        $name = $data['_new_brand'];
                        $key = strtolower(trim($name));

                        if (isset($createdBrands[$key])) {
                            $data['brand_id'] = $createdBrands[$key];
                        } else {
                            $brand = \App\Models\Brand::firstOrCreate(
                                ['name' => $name],
                                ['is_active' => true]
                            );
                            $data['brand_id'] = $brand->id;
                            $createdBrands[$key] = $brand->id;
                        }
                    }

                    // 3. Crear Categoría (Usando DB Facade por seguridad de nombres de tabla)
                    if (!empty($data['_new_category'])) {
                        $name = $data['_new_category'];
                        $key = strtolower(trim($name));

                        if (isset($createdCategories[$key])) {
                            $data['category_id'] = $createdCategories[$key];
                        } else {
                            $id = DB::table('product_categories')->where('name', $name)->value('id');
                            if (!$id) {
                                $id = DB::table('product_categories')->insertGetId(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
                            }
                            $data['category_id'] = $id;
                            $createdCategories[$key] = $id;
                        }
                    }

                    // 4. Crear Subcategoría
                    if (!empty($data['_new_sub_category'])) {
                        $name = $data['_new_sub_category'];
                        $key = strtolower(trim($name));

                        if (isset($createdSubCategories[$key])) {
                            $data['sub_category_id'] = $createdSubCategories[$key];
                        } else {
                            $id = DB::table('product_sub_categories')->where('name', $name)->value('id');
                            if (!$id) {
                                $id = DB::table('product_sub_categories')->insertGetId(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
                            }
                            $data['sub_category_id'] = $id;
                            $createdSubCategories[$key] = $id;
                        }
                    }

                    // 5. Crear Producto/Subproducto
                    if (!empty($data['_new_product_sub_product'])) {
                        $name = $data['_new_product_sub_product'];
                        $key = strtolower(trim($name));

                        if (isset($createdSubProducts[$key])) {
                            $data['product_sub_product_id'] = $createdSubProducts[$key];
                        } else {
                            $id = DB::table('product_sub_products')->where('name', $name)->value('id');
                            if (!$id) {
                                $id = DB::table('product_sub_products')->insertGetId(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
                            }
                            $data['product_sub_product_id'] = $id;
                            $createdSubProducts[$key] = $id;
                        }
                    }

                    // --- LIMPIEZA DINÁMICA DE CAMPOS TEMPORALES ---
                    // Eliminamos cualquier llave que empiece con '_new_' para no causar errores en SQL
                    foreach ($data as $key => $value) {
                        if (str_starts_with($key, '_new_')) {
                            unset($data[$key]);
                        }
                    }

                    // Finalmente, creamos el producto maestro
                    \App\Models\Product::create($data);
                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Fila {$row['row']}: " . $e->getMessage();
                }
            }

            DB::commit();

            if ($imported === 0 && count($errors) > 0) {
                // Guardamos el error en Log en lugar de usar dd() para que no trabe la interfaz en producción
                \Illuminate\Support\Facades\Log::channel('import')->error('LA BASE DE DATOS RECHAZÓ LA INSERCIÓN', [
                    'Errores' => $errors,
                    'Ultima_Fila' => $data ?? 'Ninguna'
                ]);
                
                return redirect()->route('products.import.form')
                                ->with('error', 'Error crítico al guardar. Revise el log de importación para detalles.')
                                ->with('import_errors', $errors);
            }
            
            // Limpiar sesión
            session()->forget([
                'import_preview_valid',
                'import_preview_invalid',
                'import_debug_headers',
                'import_debug_mapping',
            ]);

            $message = "Importación completada: {$imported} productos importados y catálogos actualizados.";

            return redirect()
                ->route('products.index')
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::channel('import')->critical('Error de transacción en confirmImport', ['error' => $e->getMessage()]);
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
            'sub_category_name'      => ['subcategoryname', 'subcategoria', 'sub_category', 'subcategoria'],
            'product_sub_product_name' => ['productsubproductname', 'product_sub_product', 'producto_compuesto', 'producto_set'],
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
            $relations = [];

            // --- 1. VALIDACIÓN DE IDENTIDAD (Código y Nombre) ---
            $code = trim($data['code'] ?? '');
            if (empty($code)) {
                $errors[] = 'El código es obligatorio';
            } elseif ($existingCodes->has($code)) {
                $errors[] = "El código '{$code}' ya existe en el sistema";
            } elseif ($duplicateInFile) {
                $errors[] = "El código '{$code}' está duplicado en el archivo";
            }
            $processed['code'] = $code;
            $processed['name'] = trim($data['name'] ?? '');
            if (empty($processed['name'])) $errors[] = 'El nombre es obligatorio';


            // --- 2. VALIDACIÓN ESTRICTA (Solo campos obligatorios en BD) ---
            $productTypeName = strtolower(trim($data['product_type_name'] ?? ''));
            if (empty($productTypeName)) {
                $errors[] = 'El Tipo de producto es obligatorio';
            } else {
                $pt = $catalogs['product_types'][$productTypeName] ?? null;
                if ($pt) {
                    $processed['product_type_id'] = $pt->id;
                    $relations['product_type_name'] = $pt->name;
                } else {
                    $errors[] = "Tipo de producto '{$data['product_type_name']}' no encontrado.";
                }
            }


            // --- 3. RELACIONES FLEXIBLES (Opcionales y Creación Dinámica) ---
            $handleFlexibleRelation = function($field, $catalogKey) use ($data, &$catalogs, &$processed, &$relations) {
                $name = trim($data[$field] ?? '');
                $processed[str_replace('_name', '_id', $field)] = null; // Por defecto es null

                if (!empty($name)) {
                    $key = strtolower($name);
                    $item = $catalogs[$catalogKey][$key] ?? null;

                    if ($item) {
                        // Existe en BD
                        $processed[str_replace('_name', '_id', $field)] = $item->id;
                        $relations[$field] = $item->name;
                    } else {
                        // NO existe en BD -> Se marcará para crearse
                        $cleanField = str_replace('_name', '', $field);
                        $processed['_new_' . $cleanField] = $name;
                        $relations[$field] = $name . ' (nuevo)';
                    }
                }
            };

            // Aplicamos la flexibilidad a TODAS las relaciones opcionales
            $handleFlexibleRelation('category_name', 'categories');
            $handleFlexibleRelation('sub_category_name', 'sub_categories');
            $handleFlexibleRelation('product_sub_product_name', 'product_sub_products');
            $handleFlexibleRelation('supplier_name', 'suppliers');
            $handleFlexibleRelation('brand_name', 'brands');


            // --- 4. PRECIOS Y BOOLEANOS (Limpieza de datos) ---
            $cleanPrice = fn($val) => (float) preg_replace('/[^-0-9.]/', '', str_replace(',', '', $val));
            $processed['list_price'] = $cleanPrice($data['list_price'] ?? 0);
            $processed['cost_price'] = $cleanPrice($data['cost_price'] ?? 0);

            // Booleanos seguros
            $processed['is_composite'] = $this->parseBoolean($data['is_composite'] ?? 0);
            $processed['has_expiration_date'] = $this->parseBoolean($data['has_expiration_date'] ?? 0);
            $processed['requires_sterilization'] = $this->parseBoolean($data['requires_sterilization'] ?? 0);
            $processed['requires_refrigeration'] = $this->parseBoolean($data['requires_refrigeration'] ?? 0);
            $processed['requires_temperature'] = $this->parseBoolean($data['requires_temperature'] ?? 0);


            // --- 5. ENUMS (Tracking y Status) ---
            $trackingMap = ['code' => 'code', 'rfid' => 'rfid', 'lote' => 'lote', 'serial' => 'serial'];
            $processed['tracking_type'] = $trackingMap[strtolower(trim($data['tracking_type'] ?? ''))] ?? 'code';

            $statusMap = ['activo' => 'active', 'active' => 'active', 'inactivo' => 'inactive', 'inactive' => 'inactive'];
            $processed['status'] = $statusMap[strtolower(trim($data['status'] ?? ''))] ?? 'active';

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