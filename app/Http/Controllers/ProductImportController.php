<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductType;
use App\Models\MedicalSpecialty;
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
     * Descargar template de Excel ACTUALIZADO
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar encabezados
        $headers = [
            'code',
            'name',
            'tracking_type',
            'supplier_name',
            'product_type_name',
            'category_name',
            'specialty_name',
            'brand_name',
            'list_price',
            'requires_sterilization',
            'requires_refrigeration',
            'requires_temperature',
            'status',
        ];

        // Escribir encabezados
        $sheet->fromArray($headers, null, 'A1');

        // Estilo para encabezados
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

        // Agregar filas de ejemplo
        $examples = [
            [
                '16310199',                              // code*
                'Bloque de iliaco tricortical 20-27mm', // name*
                'rfid',                                  // tracking_type* (code/rfid/serial)
                'Biograft',                              // supplier_name
                'Consumible',                            // product_type_name (Consumible/Instrumental)
                'OSTEOSINTESIS',                         // category_name
                'TRAUMATOLOGIA',                         // specialty_name
                'BIOGRAFT',                              // brand_name
                1500.50,                                 // list_price
                1,                                       // requires_sterilization (0/1)
                0,                                       // requires_refrigeration (0/1)
                0,                                       // requires_temperature (0/1)
                'active',                                // status
            ],
            [
                'KELLY-14',
                'Pinza Kelly Recta 14cm',
                'serial',
                'Aesculap',
                'Instrumental',
                'INSTRUMENTAL GENERAL',
                'CIRUGIA GENERAL',
                'Aesculap',
                450.00,
                1,
                0,
                0,
                'active',
            ],
        ];

        $sheet->fromArray($examples, null, 'A2');

        // Ajustar ancho de columnas
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Agregar hoja de instrucciones
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instrucciones');
        
        $instructions = [
            ['INSTRUCCIONES DE IMPORTACIÓN DE PRODUCTOS - ACTUALIZADO'],
            [''],
            ['CAMPOS OBLIGATORIOS:'],
            ['- code: Código único (ej: 16310199, KELLY-14)'],
            ['- name: Nombre del producto'],
            ['- tracking_type: code, rfid o serial'],
            [''],
            ['PRODUCT_TYPE_NAME (Tipo de Producto):'],
            ['  - Consumible: Productos de un solo uso'],
            ['  - Instrumental: Herramientas quirúrgicas reutilizables'],
            [''],
            ['CATEGORY_NAME (Categoría Anatómica):'],
            ['  Ejemplos: OSTEOSINTESIS, CADERA, RODILLA, ARTROSCOPIA, etc.'],
            ['  (Usa los nombres exactos de tu sistema)'],
            [''],
            ['SPECIALTY_NAME (Especialidad Médica):'],
            ['  Ejemplos: TRAUMATOLOGIA, CIRUGIA GENERAL, ORTOPEDIA, etc.'],
            [''],
            ['TRACKING_TYPE:'],
            ['  - code: Control numérico'],
            ['  - rfid: Etiquetas RFID'],
            ['  - serial: Número de serie de fábrica'],
            [''],
            ['STATUS:'],
            ['  - active (por defecto)'],
            ['  - inactive'],
            ['  - discontinued'],
            [''],
            ['CAMPOS BOOLEANOS (0 o 1):'],
            ['  requires_sterilization: ¿Requiere esterilización?'],
            ['  requires_refrigeration: ¿Requiere refrigeración?'],
            ['  requires_temperature: ¿Requiere control de temperatura?'],
            [''],
            ['IMPORTANTE:'],
            ['- Suppliers y Brands se crean automáticamente si no existen'],
            ['- Categories y Specialties deben existir previamente'],
            ['- El código debe ser único'],
            ['- Todos los campos son opcionales excepto: code, name, tracking_type'],
        ];

        $instructionsSheet->fromArray($instructions, null, 'A1');
        $instructionsSheet->getColumnDimension('A')->setWidth(90);
        $instructionsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Descargar archivo
        $filename = 'template_productos_' . now()->format('d-m-Y') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Preview de importación
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

            // Quitar encabezado
            $header = array_shift($rows);
            
            // Normalizar encabezados
            $header = array_map(function($h) {
                return strtolower(trim($h));
            }, $header);

            // Mapeo de columnas
            $columnMap = $this->getColumnMapping($header);

            // Guardar info de debug
            session(['import_debug_headers' => $header]);
            session(['import_debug_mapping' => $columnMap]);

            // Validar y procesar filas
            $validRows = [];
            $invalidRows = [];
            $rowNumber = 2;

            foreach ($rows as $row) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $data = $this->mapRowData($row, $columnMap);
                $validation = $this->validateRow($data, $rowNumber);

                if ($validation['valid']) {
                    $validRows[] = [
                        'row' => $rowNumber,
                        'data' => $data,
                        'processed' => $validation['processed'],
                        'relations' => [
                            'category_name' => $validation['processed']['category_id'] 
                                ? Category::find($validation['processed']['category_id'])?->name 
                                : null,
                            'supplier_name' => $validation['processed']['supplier_id']
                                ? Supplier::find($validation['processed']['supplier_id'])?->name
                                : null,
                            'brand_name' => $validation['processed']['brand_id']
                                ? Brand::find($validation['processed']['brand_id'])?->name
                                : null,
                            'product_type_name' => $validation['processed']['product_type_id']
                                ? ProductType::find($validation['processed']['product_type_id'])?->name
                                : null,
                            'specialty_name' => $validation['processed']['specialty_id']
                                ? MedicalSpecialty::find($validation['processed']['specialty_id'])?->name
                                : null,
                        ],
                    ];
                } else {
                    $invalidRows[] = [
                        'row' => $rowNumber,
                        'data' => $data,
                        'errors' => $validation['errors'],
                    ];
                }

                $rowNumber++;
            }

            // Guardar en sesión
            session([
                'import_preview_valid' => $validRows,
                'import_preview_invalid' => $invalidRows,
            ]);

            return view('products.import-preview', compact('validRows', 'invalidRows'));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar importación desde preview
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

            foreach ($validRows as $row) {
                try {
                    $this->createProduct($row['processed']);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Fila {$row['row']}: " . $e->getMessage();
                }
            }

            DB::commit();

            // Limpiar sesión
            session()->forget(['import_preview_valid', 'import_preview_invalid']);

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
     * Importar productos directamente
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
                return strtolower(trim($h));
            }, $header);

            $columnMap = $this->getColumnMapping($header);

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $rowNumber = 2;

            foreach ($rows as $row) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $data = $this->mapRowData($row, $columnMap);
                $validation = $this->validateRow($data, $rowNumber);

                if ($validation['valid']) {
                    try {
                        $this->createProduct($validation['processed']);
                        $imported++;
                    } catch (\Exception $e) {
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

    private function getColumnMapping($header)
    {
        $map = [];

        $expectedColumns = [
            'code' => ['code', 'codigo', 'sku', 'clave'],
            'name' => ['name', 'nombre', 'product name', 'producto'],
            'tracking_type' => ['tracking_type', 'tipo rastreo', 'rastreo'],
            'supplier_name' => ['supplier_name', 'proveedor', 'supplier'],
            'product_type_name' => ['product_type_name', 'product type', 'tipo producto'],
            'category_name' => ['category_name', 'categoria', 'category'],
            'specialty_name' => ['specialty_name', 'especialidad', 'specialty'],
            'brand_name' => ['brand_name', 'marca', 'brand'],
            'list_price' => ['list_price', 'precio', 'price', 'costo'],
            'requires_sterilization' => ['requires sterilization', 'esterilizacion'],
            'requires_refrigeration' => ['requires refrigeration', 'refrigeracion'],
            'requires_temperature' => ['requires temperature', 'temperatura'],
            'status' => ['status', 'estado'],
        ];

        foreach ($header as $index => $columnName) {
            $normalized = strtolower(trim($columnName));
            $normalized = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $normalized);
            $normalized = str_replace(['_', '-'], ' ', $normalized);

            foreach ($expectedColumns as $field => $aliases) {
                foreach ($aliases as $alias) {
                    $normalizedAlias = strtolower($alias);
                    $normalizedAlias = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $normalizedAlias);
                    $normalizedAlias = str_replace(['_', '-'], ' ', $normalizedAlias);

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
     * Mapear datos de fila
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
        return empty(array_filter($row, function($cell) {
            return !empty(trim($cell));
        }));
    }

    /**
     * Validar fila ACTUALIZADO
     */
    private function validateRow($data, $rowNumber)
    {
        $errors = [];
        $processed = [];

        // Validar code (obligatorio y único)
        if (empty($data['code'])) {
            $errors[] = 'El código es obligatorio';
        } elseif (Product::where('code', $data['code'])->exists()) {
            $errors[] = 'El código ya existe en el sistema';
        } else {
            $processed['code'] = $data['code'];
        }

        // Validar name (obligatorio)
        if (empty($data['name'])) {
            $errors[] = 'El nombre es obligatorio';
        } else {
            $processed['name'] = $data['name'];
        }

        // Validar tracking_type (obligatorio)
        $validTrackingTypes = ['code', 'rfid', 'serial'];
        $trackingType = strtolower(trim($data['tracking_type'] ?? ''));
        
        if (empty($trackingType)) {
            $errors[] = 'El tipo de rastreo es obligatorio';
        } elseif (!in_array($trackingType, $validTrackingTypes)) {
            $errors[] = 'Tipo de rastreo inválido. Use: code, rfid o serial';
        } else {
            $processed['tracking_type'] = $trackingType;
        }

        // Procesar supplier (crear si no existe)
        if (!empty($data['supplier_name'])) {
            $supplier = Supplier::firstOrCreate(
                ['name' => $data['supplier_name']],
                [
                    'code' => 'SUP-' . strtoupper(substr($data['supplier_name'], 0, 3)) . '-' . rand(1000, 9999),
                    'is_active' => true,
                ]
            );
            $processed['supplier_id'] = $supplier->id;
        } else {
            $processed['supplier_id'] = null;
        }

        // Procesar brand (crear si no existe)
        if (!empty($data['brand_name'])) {
            $brand = Brand::firstOrCreate(
                ['name' => $data['brand_name']],
                ['is_active' => true]
            );
            $processed['brand_id'] = $brand->id;
        } else {
            $processed['brand_id'] = null;
        }

        if (empty($data['product_type_name'])) {
            $errors[] = 'El tipo de producto es obligatorio (Consumible / Instrumental)';
        } else {
            $productTypeName = trim($data['product_type_name']);

            $productType = ProductType::whereRaw('LOWER(name) = ?', [strtolower($productTypeName)])
                ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($productTypeName) . '%'])
                ->first();

            if ($productType) {
                $processed['product_type_id'] = $productType->id;
            } else {
                $errors[] = "Tipo de producto '{$productTypeName}' no encontrado";
            }
        }


        // Procesar category (buscar por nombre)
        if (!empty($data['category_name'])) {
            $categoryName = trim($data['category_name']);
            
            $category = Category::whereRaw('LOWER(name) = ?', [strtolower($categoryName)])->first();
            
            if (!$category) {
                $category = Category::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($categoryName) . '%'])->first();
            }
            
            if ($category) {
                $processed['category_id'] = $category->id;
            } else {
                $availableCategories = Category::orderBy('name')->pluck('name')->take(10)->implode(', ');
                $totalCategories = Category::count();
                $moreText = $totalCategories > 10 ? " (y " . ($totalCategories - 10) . " más)" : "";
                
                $errors[] = "Categoría '{$categoryName}' no encontrada. Algunas disponibles: {$availableCategories}{$moreText}";
            }
        } else {
            $processed['category_id'] = null;
        }

        // Procesar specialty (buscar por nombre)
        if (!empty($data['specialty_name'])) {
            $specialtyName = trim($data['specialty_name']);
            
            $specialty = MedicalSpecialty::whereRaw('LOWER(name) = ?', [strtolower($specialtyName)])->first();
            
            if (!$specialty) {
                $specialty = MedicalSpecialty::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($specialtyName) . '%'])->first();
            }
            
            if ($specialty) {
                $processed['specialty_id'] = $specialty->id;
            }
            // Si no se encuentra, dejarlo null (no es crítico)
        } else {
            $processed['specialty_id'] = null;
        }

        // Procesar campos numéricos
        $processed['list_price'] = !empty($data['list_price']) ? (float)$data['list_price'] : 0;
        $processed['minimum_stock'] = 0;

        // Procesar booleanos
        $processed['requires_sterilization'] = $this->parseBoolean($data['requires_sterilization'] ?? 0);
        $processed['requires_refrigeration'] = $this->parseBoolean($data['requires_refrigeration'] ?? 0);
        $processed['requires_temperature'] = $this->parseBoolean($data['requires_temperature'] ?? 0);

        // Procesar status
        $validStatuses = ['active', 'inactive', 'discontinued'];
        $status = strtolower($data['status'] ?? 'active');
        $processed['status'] = in_array($status, $validStatuses) ? $status : 'active';

        // Campo description (vacío por ahora)
        $processed['description'] = null;

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'processed' => $processed,
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
        
        $value = strtolower(trim($value));
        return in_array($value, ['1', 'true', 'yes', 'si', 'sí']);
    }

    /**
     * Crear producto en BD
     */
    private function createProduct($data)
    {
        return Product::create($data);
    }
}