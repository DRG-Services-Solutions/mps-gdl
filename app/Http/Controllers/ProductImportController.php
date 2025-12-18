<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Brand;
use App\Models\Category;
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
     * Descargar template de Excel
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
            'category_name',
            'subcategory_name',
            'specialty_name',
            'layout_name',
            'sku',
            'list_price',
            'requires_sterilization',
            'requires_refrigeration',
            'requires_temperature',
            'status',
            'brand_name',
        ];

        // Escribir encabezados
        $sheet->fromArray($headers, null, 'A1');

        // Estilo para encabezados
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);

        // Agregar filas de ejemplo
        $examples = [
            [
                'PROD-001',                              // code*
                'Bisturí Quirúrgico N°15',              // name*
                'rfid',                                  // tracking_type* (code/rfid/serial)
                'Medtronic',                             // supplier_name
                'Consumibles Quirúrgicos',               // category_name
                '',                                      // subcategory_name (vacío)
                '',                                      // specialty_name (vacío)
                '',                                      // layout_name (vacío)
                '',                                      // sku (vacío)
                150.50,                                  // list_price
                0,                                       // requires_sterilization (0/1)
                0,                                       // requires_refrigeration (0/1)
                0,                                       // requires_temperature (0/1)
                'active',                                // status (active/inactive/discontinued)
                'Stryker',                               // brand_name
            ],
            [
                'PROD-002',
                'Pinza Kelly Recta 14cm',
                'serial',
                'Johnson & Johnson',
                'Instrumental Quirúrgico',
                '',
                '',
                '',
                '',
                450.00,
                1,
                0,
                0,
                'active',
                'Aesculap',
            ],
        ];

        $sheet->fromArray($examples, null, 'A2');

        // Ajustar ancho de columnas
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Agregar hoja de instrucciones
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instrucciones');
        
        $instructions = [
            ['INSTRUCCIONES DE IMPORTACIÓN DE PRODUCTOS'],
            [''],
            ['CAMPOS OBLIGATORIOS (marcados con *):'],
            ['- code: Código único del producto (ej: PROD-001)'],
            ['- name: Nombre descriptivo del producto'],
            ['- tracking_type: Tipo de rastreo (code/rfid/serial)'],
            [''],
            ['VALORES PERMITIDOS:'],
            [''],
            ['tracking_type:'],
            ['  - code: Control numérico sin identificadores individuales'],
            ['  - rfid: Cada unidad tendrá etiqueta RFID'],
            ['  - serial: Instrumental con número de serie de fábrica'],
            [''],
            ['category_name:'],
            ['  - Consumibles Quirúrgicos'],
            ['  - Instrumental Quirúrgico'],
            [''],
            ['status:'],
            ['  - active (por defecto)'],
            ['  - inactive'],
            ['  - discontinued'],
            [''],
            ['requires_sterilization / requires_refrigeration / requires_temperature:'],
            ['  - 0 = No'],
            ['  - 1 = Sí'],
            [''],
            ['NOTAS IMPORTANTES:'],
            ['- Si supplier_name no existe, se creará automáticamente'],
            ['- Si brand_name no existe, se creará automáticamente'],
            ['- Los campos vacíos se llenarán con valores por defecto'],
            ['- El código (code) debe ser único en el sistema'],
            ['- layout_name, subcategory_name, specialty_name pueden quedar vacíos'],
        ];

        $instructionsSheet->fromArray($instructions, null, 'A1');
        $instructionsSheet->getColumnDimension('A')->setWidth(80);
        $instructionsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Descargar archivo
        $filename = 'template_importacion_productos_' . now()->format('Y-m-d') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Preview de importación (validar sin guardar)
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

            // Validar y procesar filas
            $validRows = [];
            $invalidRows = [];
            $rowNumber = 2; // Empezar en 2 porque la 1 es encabezado

            foreach ($rows as $row) {
                // Saltar filas vacías
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

            return view('products.import-preview', compact('validRows', 'invalidRows'));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Importar productos (guardar en BD)
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

            // Quitar encabezado
            $header = array_shift($rows);
            
            // Normalizar encabezados
            $header = array_map(function($h) {
                return strtolower(trim($h));
            }, $header);

            // Mapeo de columnas
            $columnMap = $this->getColumnMapping($header);

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $rowNumber = 2;

            foreach ($rows as $row) {
                // Saltar filas vacías
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

    /**
     * Obtener mapeo de columnas
     */
    private function getColumnMapping($header)
    {
        $map = [];
        
        $expectedColumns = [
            'code' => ['code', 'codigo', 'sku'],
            'name' => ['name', 'nombre', 'product_name'],
            'tracking_type' => ['tracking_type', 'tipo_rastreo', 'rastreo'],
            'supplier_name' => ['supplier_name', 'proveedor', 'supplier'],
            'category_name' => ['category_name', 'categoria', 'category'],
            'brand_name' => ['brand_name', 'marca', 'brand', 'categoria 1 (marca)'],
            'list_price' => ['list_price', 'precio', 'price', 'costo'],
            'requires_sterilization' => ['requires_sterilization', 'esterilizacion', 'sterilization'],
            'requires_refrigeration' => ['requires_refrigeration', 'refrigeracion', 'refrigeration'],
            'requires_temperature' => ['requires_temperature', 'temperatura', 'temperature'],
            'status' => ['status', 'estado', 'state'],
        ];

        foreach ($header as $index => $columnName) {
            $normalized = strtolower(trim($columnName));
            
            foreach ($expectedColumns as $field => $aliases) {
                if (in_array($normalized, $aliases)) {
                    $map[$field] = $index;
                    break;
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
     * Validar fila
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
        $trackingType = strtolower($data['tracking_type'] ?? '');
        
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

        // Procesar category
        if (!empty($data['category_name'])) {
            $categoryName = trim($data['category_name']);
            
            // Mapeo de nombres del Excel a nombres del sistema
            $categoryMap = [
                'consumible' => 'Consumibles Quirúrgicos',
                'consumibles' => 'Consumibles Quirúrgicos',
                'consumibles quirurgicos' => 'Consumibles Quirúrgicos',
                'instrumental' => 'Instrumental Quirúrgico',
                'instrumental quirurgico' => 'Instrumental Quirúrgico',
            ];
            
            $normalizedName = strtolower($categoryName);
            $systemCategoryName = $categoryMap[$normalizedName] ?? $categoryName;
            
            $category = Category::where('name', $systemCategoryName)->first();
            
            if ($category) {
                $processed['category_id'] = $category->id;
            } else {
                $errors[] = "Categoría '{$categoryName}' no encontrada. Use: Consumibles Quirúrgicos o Instrumental Quirúrgico";
            }
        } else {
            $processed['category_id'] = null;
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

        // Campos que quedan vacíos
        $processed['specialty_id'] = null;
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