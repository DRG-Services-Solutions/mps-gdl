<?php

namespace App\Http\Controllers;

use App\Models\SurgicalKitTemplateItems;
use App\Models\SurgicalKitTemplate;
use App\Models\Product;
use App\Http\Requests\StoreSurgicalKitTemplateItemsRequest;
use App\Http\Requests\UpdateSurgicalKitTemplateItemsRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class SurgicalKitTemplateItemsController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    // AGREGAR ITEM
    // ═══════════════════════════════════════════════════════════

    public function store(StoreSurgicalKitTemplateItemsRequest $request)
    {
        $validated = $request->validated();

        $template = SurgicalKitTemplate::findOrFail($validated['surgical_kit_template_id']);

        // Si el producto ya existe en el kit, suma la cantidad en lugar de duplicar
        $existing = SurgicalKitTemplateItems::where('surgical_kit_template_id', $template->id)
            ->where('product_id', $validated['product_id'])
            ->first();
                
        if ($existing) {
            $existing->increment('quantity_required', $validated['quantity_required']);
        } else {
            SurgicalKitTemplateItems::create([
                'surgical_kit_template_id' => $template->id,
                'product_id'               => $validated['product_id'],
                'quantity_required'        => $validated['quantity_required'],
            ]);
        }

        return redirect()
            ->route('surgical_kit_templates.show', ['surgical_kit_template' => $template->id])
            ->with('success', 'Artículo agregado al kit.');
    }

    // ═══════════════════════════════════════════════════════════
    // ACTUALIZAR CANTIDAD (inline autosubmit desde la tabla)
    // ═══════════════════════════════════════════════════════════

    public function update(UpdateSurgicalKitTemplateItemsRequest $request, SurgicalKitTemplateItems $surgicalKitTemplateItems)
    {
        $validated = $request->validated();

        $surgicalKitTemplateItems->update([
            'quantity' => $validated['quantity'],
        ]);

        return redirect()
            ->route('surgical_kit_templates.show', $surgicalKitTemplateItems->surgical_kit_template_id)
            ->with('success', 'Cantidad actualizada.');
    }

    // ═══════════════════════════════════════════════════════════
    // ELIMINAR ITEM
    // ═══════════════════════════════════════════════════════════

    public function destroy(SurgicalKitTemplateItems $surgicalKitTemplateItems)
    {
        $templateId = $surgicalKitTemplateItems->surgical_kit_template_id;
        $surgicalKitTemplateItems->delete();

        return redirect()
            ->route('surgical_kit_templates.show', $templateId)
            ->with('success', 'Artículo eliminado del kit.');
    }

    // ═══════════════════════════════════════════════════════════
    // CARGA MASIVA — DESCARGAR PLANTILLA .xlsx
    // ═══════════════════════════════════════════════════════════

    public function bulkTemplate(SurgicalKitTemplate $surgicalKitTemplate)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'product_sku');
        $sheet->setCellValue('B1', 'quantity');
        $sheet->setCellValue('C1', 'notes');

        // Fila de ejemplo
        $sheet->setCellValue('A2', 'INST-001');
        $sheet->setCellValue('B2', 1);
        $sheet->setCellValue('C2', 'Opcional');

        // Estilo encabezados
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '6366F1'],
            ],
        ]);

        foreach (['A', 'B', 'C'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'plantilla_kit_' . ($surgicalKitTemplate->code ?? $surgicalKitTemplate->id) . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    // ═══════════════════════════════════════════════════════════
    // CARGA MASIVA — PROCESAR ARCHIVO .xlsx
    // ═══════════════════════════════════════════════════════════

    public function bulkImport(\Illuminate\Http\Request $request, SurgicalKitTemplate $surgicalKitTemplate)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        try {
            $path        = $request->file('file')->store('temp');
            $fullPath    = storage_path('app/' . $path);
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
            $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            array_shift($rows); // quitar fila de encabezados

            $created = 0;
            $skipped = 0;
            $errors  = [];
            $preview = [];

            DB::transaction(function () use (
                $rows, $surgicalKitTemplate,
                &$created, &$skipped, &$errors, &$preview
            ) {
                foreach ($rows as $index => $row) {
                    $rowNum = $index + 2;
                    $sku    = trim((string) ($row['A'] ?? ''));
                    $qty    = (int) ($row['B'] ?? 0);

                    if (empty($sku)) {
                        continue; // fila vacía, ignorar
                    }

                    if ($qty < 1) {
                        $errors[]  = "Fila {$rowNum}: cantidad inválida para SKU '{$sku}'.";
                        $preview[] = ['sku' => $sku, 'qty' => $qty, 'status' => 'error', 'reason' => 'Cantidad inválida'];
                        continue;
                    }

                    $product = Product::where('code', $sku)->first();

                    if (! $product) {
                        $errors[]  = "Fila {$rowNum}: SKU '{$sku}' no encontrado en catálogo.";
                        $preview[] = ['sku' => $sku, 'qty' => $qty, 'status' => 'error', 'reason' => 'SKU no encontrado'];
                        continue;
                    }

                    $existing = SurgicalKitTemplateItems::where('surgical_kit_template_id', $surgicalKitTemplate->id)
                        ->where('product_id', $product->id)
                        ->first();

                    if ($existing) {
                        $skipped++;
                        $preview[] = ['sku' => $sku, 'qty' => $qty, 'status' => 'skipped', 'reason' => 'Ya existe en el kit'];
                        continue;
                    }

                    SurgicalKitTemplateItems::create([
                        'surgical_kit_template_id' => $surgicalKitTemplate->id,
                        'product_id'               => $product->id,
                        'quantity'                 => $qty,
                        'notes'                    => trim((string) ($row['C'] ?? '')) ?: null,
                    ]);

                    $created++;
                    $preview[] = ['sku' => $sku, 'qty' => $qty, 'status' => 'created', 'reason' => 'Agregado'];
                }
            });

            Storage::delete($path);

            return response()->json([
                'success' => $created > 0 || empty($errors),
                'message' => $created > 0
                    ? "{$created} artículo(s) importado(s) correctamente."
                    : 'No se importó ningún artículo nuevo.',
                'created' => $created,
                'skipped' => $skipped,
                'errors'  => $errors,
                'preview' => $preview,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage(),
                'created' => 0,
                'skipped' => 0,
                'errors'  => [$e->getMessage()],
                'preview' => [],
            ], 422);
        }
    }
}