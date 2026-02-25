<?php

namespace App\Http\Controllers;

use App\Services\ChecklistImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Font, Alignment, Border};

class ChecklistImportController extends Controller
{
    public function __construct(
        protected ChecklistImportService $importService
    ) {}

    /**
     * Mostrar formulario de carga
     */
    public function showForm()
    {
        return view('checklists.import');
    }

    /**
     * Parsear Excel y mostrar preview
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120', // Max 5MB
        ], [
            'file.required' => 'Debes seleccionar un archivo Excel.',
            'file.mimes' => 'El archivo debe ser .xlsx o .xls.',
            'file.max' => 'El archivo no puede pesar más de 5MB.',
        ]);

        $file = $request->file('file');

        // Guardar temporalmente
        $tempPath = $file->store('temp/checklist-imports');
        $fullPath = Storage::path($tempPath);

        try {
            $result = $this->importService->parseAndValidate($fullPath);
        } catch (\Exception $e) {
            Storage::delete($tempPath);
            return back()->with('error', 'Error al leer el archivo: ' . $e->getMessage());
        }

        // Guardar path en sesión para el confirm
        session(['checklist_import_path' => $tempPath]);
        session(['checklist_import_data' => $result]);

        return view('checklists.import-preview', [
            'result' => $result,
            'fileName' => $file->getClientOriginalName(),
        ]);
    }

    /**
     * Confirmar y ejecutar la importación
     */
    public function confirm(Request $request)
    {
        $importData = session('checklist_import_data');

        if (!$importData || empty($importData['checklists'])) {
            return redirect()->route('checklists.import.form')
                ->with('error', 'No hay datos de importación. Sube el archivo nuevamente.');
        }

        if (!$importData['success']) {
            return redirect()->route('checklists.import.form')
                ->with('error', 'La importación tiene errores que deben corregirse primero.');
        }

        $result = $this->importService->executeImport($importData['checklists']);

        // Limpiar sesión y archivo temporal
        $tempPath = session('checklist_import_path');
        if ($tempPath) {
            Storage::delete($tempPath);
        }
        session()->forget(['checklist_import_path', 'checklist_import_data']);

        if ($result['success']) {
            $message = sprintf(
                'Importación exitosa: %d checklists creados, %d items agregados.',
                $result['created'],
                $result['items_created']
            );

            if ($result['skipped'] > 0) {
                $message .= " ({$result['skipped']} checklists omitidos por ya existir)";
            }

            return redirect()->route('checklists.index')
                ->with('success', $message);
        }

        return redirect()->route('checklists.import.form')
            ->with('error', $result['error']);
    }

    /**
     * Descargar plantilla Excel de ejemplo
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Checklists');

        // Headers
        $headers = [
            'A1' => 'checklist_code',
            'B1' => 'surgery_type',
            'C1' => 'product_sku',
            'D1' => 'quantity',
            'E1' => 'is_mandatory',
            'F1' => 'notes',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Estilo de headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        // Datos de ejemplo
        $examples = [
            ['CHK-ORTOPEDIA-001', 'Ortopedia - Rodilla', 'PROD-BISTURI-10', 2, 'Sí', 'Bisturí estándar'],
            ['CHK-ORTOPEDIA-001', 'Ortopedia - Rodilla', 'PROD-GASA-50', 10, 'Sí', ''],
            ['CHK-ORTOPEDIA-001', 'Ortopedia - Rodilla', 'PROD-SUTURA-3', 5, 'Sí', 'Sutura absorbible'],
            ['CHK-ORTOPEDIA-001', 'Ortopedia - Rodilla', 'PROD-CLAMP-M', 3, 'No', 'Opcional según doctor'],
            ['CHK-CARDIO-001', 'Cardiovascular', 'PROD-CATETER-7F', 1, 'Sí', ''],
            ['CHK-CARDIO-001', 'Cardiovascular', 'PROD-GASA-50', 20, 'Sí', 'Mayor cantidad para cardio'],
            ['CHK-CARDIO-001', 'Cardiovascular', 'PROD-MONITOR-EKG', 1, 'Sí', ''],
        ];

        $row = 2;
        foreach ($examples as $data) {
            $sheet->setCellValue("A{$row}", $data[0]);
            $sheet->setCellValue("B{$row}", $data[1]);
            $sheet->setCellValue("C{$row}", $data[2]);
            $sheet->setCellValue("D{$row}", $data[3]);
            $sheet->setCellValue("E{$row}", $data[4]);
            $sheet->setCellValue("F{$row}", $data[5]);
            $row++;
        }

        // Estilo filas ejemplo (fondo amarillo claro)
        $sheet->getStyle('A2:F8')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF9C3']],
            'font' => ['italic' => true, 'color' => ['rgb' => '92400E']],
        ]);

        // Hoja de instrucciones
        $instrSheet = $spreadsheet->createSheet();
        $instrSheet->setTitle('Instrucciones');

        $instructions = [
            ['INSTRUCCIONES DE LLENADO', ''],
            ['', ''],
            ['Columna', 'Descripción'],
            ['checklist_code', 'Código único del checklist. Todas las filas con el mismo código se agrupan en un solo checklist. Ejemplo: CHK-ORTOPEDIA-001'],
            ['surgery_type', 'Tipo de cirugía. Solo necesita estar en la primera fila de cada grupo. Ejemplo: Ortopedia - Rodilla'],
            ['product_sku', 'SKU/Código del producto tal como está registrado en el sistema. DEBE existir en el catálogo.'],
            ['quantity', 'Cantidad base requerida. Debe ser un número entero mayor a 0.'],
            ['is_mandatory', 'Indica si el producto es obligatorio. Valores aceptados: Sí, Si, Yes, 1, No, 0. Si se deja vacío, se asume Sí.'],
            ['notes', 'Notas opcionales sobre el item.'],
            ['', ''],
            ['NOTAS IMPORTANTES', ''],
            ['1', 'Los datos en amarillo de la hoja "Plantilla Checklists" son EJEMPLOS. Elimínalos antes de llenar tus datos.'],
            ['2', 'Si un checklist_code ya existe en el sistema, se omitirá (no se duplica).'],
            ['3', 'Si un product_sku no existe en el catálogo, la importación marcará error en esa fila.'],
            ['4', 'No cambies los nombres de las columnas en la fila 1.'],
            ['5', 'Puedes incluir múltiples checklists en un mismo archivo.'],
        ];

        foreach ($instructions as $i => $data) {
            $instrSheet->setCellValue('A' . ($i + 1), $data[0]);
            $instrSheet->setCellValue('B' . ($i + 1), $data[1]);
        }

        $instrSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '4F46E5']],
        ]);
        $instrSheet->getStyle('A3:B3')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']],
        ]);
        $instrSheet->getStyle('A11')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'DC2626']],
        ]);

        // Anchos de columnas
        foreach (['A' => 22, 'B' => 20, 'C' => 22, 'D' => 12, 'E' => 15, 'F' => 30] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $instrSheet->getColumnDimension('A')->setWidth(22);
        $instrSheet->getColumnDimension('B')->setWidth(80);

        // Activar primera hoja
        $spreadsheet->setActiveSheetIndex(0);

        // Generar archivo
        $fileName = 'plantilla_checklists_' . date('Ymd') . '.xlsx';
        $tempPath = storage_path("app/temp/{$fileName}");

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
}
