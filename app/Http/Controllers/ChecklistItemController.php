<?php

namespace App\Http\Controllers;

use App\Models\SurgicalChecklist;
use App\Models\ChecklistItem;
use App\Models\ChecklistConditional;
use App\Models\Product;
use App\Models\LegalEntity;
use Illuminate\Http\Request;

class ChecklistItemController extends Controller
{
    /**
     * Agregar item al check list
     */
    public function store(Request $request, SurgicalChecklist $checklist)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        // Verificar que no exista el producto
        if ($checklist->items()->where('product_id', $validated['product_id'])->exists()) {
            return back()->with('error', 'Este producto ya existe en el check list.');
        }

        $item = $checklist->items()->create($validated);

        return back()->with('success', 'Producto agregado al check list exitosamente.');
    }

    /**
     * Actualizar item
     */
    public function update(Request $request, ChecklistItem $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'is_mandatory' => 'required|boolean',
            'order' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $item->update($validated);

        return back()->with('success', 'Item actualizado exitosamente.');
    }

    /**
     * Eliminar item
     */
    public function destroy(ChecklistItem $item)
    {
        $item->delete();

        return back()->with('success', 'Item eliminado del check list exitosamente.');
    }

    /**
     * Agregar condicional a un item
     */
    public function addConditional(Request $request, ChecklistItem $item)
    {
        $validated = $request->validate([
            'legal_entity_id' => 'nullable|exists:legal_entities,id',
            'payment_mode' => 'nullable|in:particular,aseguradora',
            'condition_type' => 'required|in:required,excluded,optional',
            'quantity_multiplier' => 'required|numeric|min:0|max:10',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['legal_entity_id']) && empty($validated['payment_mode'])) {
            return back()->with('error', 'Debe seleccionar al menos Hospital/Doctor o Modalidad.');
        }

        $item->conditionals()->create($validated);

        return back()->with('success', 'Condicional agregado exitosamente.');
    }

    /**
     * Eliminar condicional
     */
    public function removeConditional(ChecklistConditional $conditional)
    {
        $conditional->delete();

        return back()->with('success', 'Condicional eliminado exitosamente.');
    }

    /**
     * Reordenar items
     */
    public function reorder(Request $request, SurgicalChecklist $checklist)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:checklist_items,id',
            'items.*.order' => 'required|integer',
        ]);

        foreach ($validated['items'] as $itemData) {
            ChecklistItem::where('id', $itemData['id'])
                ->update(['order' => $itemData['order']]);
        }

        return response()->json(['success' => true]);
    }

    public function bulkTemplate(SurgicalChecklist $checklist)
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Items');

    // Headers
    $sheet->setCellValue('A1', 'product_sku');
    $sheet->setCellValue('B1', 'quantity');
    $sheet->setCellValue('C1', 'notes');

    // Estilo headers
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
    ];
    $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

    // Ejemplos
    $sheet->setCellValue('A2', 'PROD-BISTURI-10');
    $sheet->setCellValue('B2', 2);
    $sheet->setCellValue('C2', 'Ejemplo - eliminar esta fila');
    $sheet->setCellValue('A3', 'PROD-GASA-50');
    $sheet->setCellValue('B3', 10);
    $sheet->setCellValue('C3', '');

    $sheet->getStyle('A2:C3')->applyFromArray([
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF9C3']],
        'font' => ['italic' => true, 'color' => ['rgb' => '92400E']],
    ]);

    $sheet->getColumnDimension('A')->setWidth(22);
    $sheet->getColumnDimension('B')->setWidth(12);
    $sheet->getColumnDimension('C')->setWidth(30);

    $fileName = "plantilla_items_{$checklist->code}.xlsx";
    $tempPath = storage_path("app/temp/{$fileName}");
    if (!is_dir(dirname($tempPath))) mkdir(dirname($tempPath), 0755, true);

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save($tempPath);

    return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
}

/**
 * Carga masiva de items a un checklist existente
 */
public function bulkImport(Request $request, SurgicalChecklist $checklist)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls|max:5120',
    ]);

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('file')->getPathname());
    $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    if (count($rows) < 2) {
        return response()->json([
            'success' => false,
            'message' => 'El archivo está vacío o solo tiene encabezados.',
        ]);
    }

    // Quitar header
    array_shift($rows);

    $errors = [];
    $created = 0;
    $skipped = 0;
    $preview = [];

    // Recolectar todos los SKUs para una sola consulta
    $allSkus = collect($rows)
        ->map(fn($r) => trim((string)($r['A'] ?? '')))
        ->filter()
        ->unique();

    $existingProducts = Product::whereIn('code', $allSkus)->pluck('id', 'code');
    $existingItems = $checklist->items()->pluck('product_id')->toArray();

    \DB::beginTransaction();

    try {
        $order = $checklist->items()->max('order') ?? 0;

        foreach ($rows as $index => $row) {
            $rowNum = $index;
            $sku = trim((string)($row['A'] ?? ''));
            $qty = (int)($row['B'] ?? 0);
            $notes = trim((string)($row['C'] ?? ''));

            // Saltar filas vacías
            if (empty($sku)) continue;

            // Validar cantidad
            if ($qty < 1) {
                $errors[] = "Fila {$rowNum}: Cantidad inválida para SKU '{$sku}'.";
                $preview[] = ['sku' => $sku, 'qty' => $qty, 'status' => 'error', 'reason' => 'Cantidad inválida'];
                continue;
            }

            // Validar producto existe
            $productId = $existingProducts->get($sku);
            if (!$productId) {
                $errors[] = "Fila {$rowNum}: SKU '{$sku}' no existe en el catálogo.";
                $preview[] = ['sku' => $sku, 'qty' => $qty, 'status' => 'error', 'reason' => 'SKU no encontrado'];
                continue;
            }

            // Validar no duplicado en checklist
            if (in_array($productId, $existingItems)) {
                $skipped++;
                $preview[] = ['sku' => $sku, 'qty' => $qty, 'status' => 'skipped', 'reason' => 'Ya existe en el checklist'];
                continue;
            }

            $checklist->items()->create([
                'product_id' => $productId,
                'quantity' => $qty,
                'is_mandatory' => true,
                'order' => ++$order,
                'notes' => $notes ?: null,
            ]);

            $existingItems[] = $productId; // Evitar duplicados dentro del mismo archivo
            $created++;
            $preview[] = ['sku' => $sku, 'qty' => $qty, 'status' => 'created', 'reason' => 'Agregado'];
        }

        if (!empty($errors) && $created === 0) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'No se pudo agregar ningún item.',
                'errors' => $errors,
                'preview' => $preview,
            ]);
        }

        \DB::commit();

        return response()->json([
            'success' => true,
            'message' => "{$created} items agregados exitosamente." .
                ($skipped > 0 ? " {$skipped} omitidos por duplicados." : '') .
                (!empty($errors) ? " " . count($errors) . " con errores." : ''),
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'preview' => $preview,
        ]);

    } catch (\Exception $e) {
        \DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error durante la importación: ' . $e->getMessage(),
        ], 500);
    }
}
}