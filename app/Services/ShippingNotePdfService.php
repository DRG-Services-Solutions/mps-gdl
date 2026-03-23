<?php

namespace App\Services;

use App\Models\ShippingNote;
use App\Helpers\NumberToWordsHelper;

// FPDF instalado manualmente en app/Libs/fpdf/fpdf.php
// Descargar de: http://www.fpdf.org/en/dl.php?v=186&f=tgz
require_once app_path('Libs/fpdf/fpdf.php');

use FPDF;

class ShippingNotePdfService
{
    private FPDF $pdf;
    private ShippingNote $note;
    private float $pageWidth = 210;  // A4 mm
    private float $marginLeft = 12;
    private float $marginRight = 12;
    private float $contentWidth;

    // Colores MPS/DRG
    private array $cyan = [41, 157, 191];    // #299dbf
    private array $darkNav = [17, 49, 75];   // #11314b
    private array $grayBg = [245, 245, 245]; // #f5f5f5
    private array $white = [255, 255, 255];
    private array $black = [0, 0, 0];
    private array $grayText = [100, 100, 100];
    private array $grayLine = [200, 200, 200];
    private array $urgencyBg = [255, 243, 224]; // naranja suave
    private array $conditionalBg = [232, 245, 253]; // azul suave

    public function generate(ShippingNote $shippingNote): string
    {
        $this->note = $shippingNote;
        $this->note->load([
            'hospital',
            'doctor',
            'billingLegalEntity',
            'surgicalChecklist',
            'items.product',
            'items.productUnit',
            'items.checklistConditional',
            'kits.surgicalKit',
            'rentalConcepts',
            'createdBy',
        ]);

        $this->contentWidth = $this->pageWidth - $this->marginLeft - $this->marginRight;

        $this->pdf = new FPDF('P', 'mm', 'A4');
        $this->pdf->SetAutoPageBreak(true, 20);
        $this->pdf->SetMargins($this->marginLeft, 10, $this->marginRight);
        $this->pdf->AddPage();

        $this->drawHeader();
        $this->drawReceiverAndMeta();
        $this->drawItemsTable();
        $this->drawConditionalsSummary();
        $this->drawTotals();
        $this->drawNotes();
        $this->drawFooter();

        // Generar en memoria
        $filename = 'Remision_' . $this->note->shipping_number . '.pdf';
        $path = storage_path('app/public/remisiones/' . $filename);

        // Crear directorio si no existe
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->pdf->Output('F', $path);

        return $path;
    }

    /**
     * Generar y retornar el contenido para descarga directa
     */
    public function generateForDownload(ShippingNote $shippingNote): string
    {
        $this->note = $shippingNote;
        $this->note->load([
            'hospital',
            'doctor',
            'billingLegalEntity',
            'surgicalChecklist',
            'items.product',
            'items.productUnit',
            'items.checklistConditional',
            'kits.surgicalKit',
            'rentalConcepts',
            'createdBy',
        ]);

        $this->contentWidth = $this->pageWidth - $this->marginLeft - $this->marginRight;

        $this->pdf = new FPDF('P', 'mm', 'A4');
        $this->pdf->SetAutoPageBreak(true, 20);
        $this->pdf->SetMargins($this->marginLeft, 10, $this->marginRight);
        $this->pdf->AddPage();

        $this->drawHeader();
        $this->drawReceiverAndMeta();
        $this->drawItemsTable();
        $this->drawConditionalsSummary();
        $this->drawTotals();
        $this->drawNotes();
        $this->drawFooter();

        return $this->pdf->Output('S');
    }

    // ═══════════════════════════════════════════════════════════
    // ENCABEZADO CON LOGO + DATOS EMISOR
    // ═══════════════════════════════════════════════════════════

    private function drawHeader(): void
    {
        $y = $this->pdf->GetY();

        // Logo MPS (izquierda)
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            $this->pdf->Image($logoPath, $this->marginLeft, $y, 55);
        } else {
            // Fallback: texto
            $this->pdf->SetFont('Helvetica', 'B', 20);
            $this->setColor($this->cyan);
            $this->pdf->SetXY($this->marginLeft, $y);
            $this->pdf->Cell(55, 15, 'MPS', 0, 0, 'L');
        }

        // Recuadro "Remisión XXXXX" (derecha arriba)
        $boxW = 75;
        $boxX = $this->pageWidth - $this->marginRight - $boxW;

        $this->pdf->SetDrawColor(...$this->cyan);
        $this->pdf->SetLineWidth(0.6);
        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->setColor($this->black);
        $this->pdf->SetXY($boxX, $y);
        $this->pdf->Cell($boxW, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Remisión ' . $this->note->shipping_number), 1, 2, 'C');

        // Datos del emisor (razón social que factura) — debajo del recuadro
        $legalEntity = $this->note->billingLegalEntity;
        $this->pdf->SetFont('Helvetica', '', 7.5);
        $this->setColor($this->grayText);

        $emitterLines = [];
        if ($legalEntity) {
            $emitterLines[] = $legalEntity->razon_social ?? $legalEntity->name;
            if ($legalEntity->rfc) {
                $emitterLines[] = 'RFC: ' . $legalEntity->rfc;
            }
            if ($legalEntity->phone) {
                $emitterLines[] = 'Tel.: ' . $legalEntity->phone;
            }
            if ($legalEntity->address) {
                $emitterLines[] = $legalEntity->address;
            }
        }

        foreach ($emitterLines as $line) {
            $this->pdf->SetX($boxX);
            $this->pdf->Cell($boxW, 3.8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $line), 0, 2, 'R');
        }

        $this->pdf->Ln(4);
    }

    // ═══════════════════════════════════════════════════════════
    // RECEPTOR + META INFO
    // ═══════════════════════════════════════════════════════════

    private function drawReceiverAndMeta(): void
    {
        $y = $this->pdf->GetY();
        $halfW = $this->contentWidth / 2 - 2;

        // ── Receptor (izquierda) ──
        $this->pdf->SetDrawColor(...$this->grayLine);
        $this->pdf->SetLineWidth(0.3);

        // Título "Receptor"
        $this->pdf->SetXY($this->marginLeft, $y);
        $this->pdf->SetFillColor(...$this->grayBg);
        $this->pdf->SetFont('Helvetica', 'B', 8);
        $this->setColor($this->darkNav);
        $this->pdf->Cell($halfW, 6, 'Receptor', 1, 2, 'L', true);

        // Datos del hospital
        $this->pdf->SetFont('Helvetica', '', 7.5);
        $this->setColor($this->black);

        $hospital = $this->note->hospital;
        $receptorLines = [];
        if ($hospital) {
            $receptorLines[] = $hospital->name;
            if ($hospital->rfc) {
                $receptorLines[] = 'RFC: ' . $hospital->rfc;
            }
        }

        $receptorY = $this->pdf->GetY();
        foreach ($receptorLines as $line) {
            $this->pdf->SetX($this->marginLeft);
            $this->pdf->Cell($halfW, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $line), 'LR', 2, 'L');
        }

        // Padding inferior del cuadro
        $lineCount = count($receptorLines);
        for ($i = $lineCount; $i < 4; $i++) {
            $this->pdf->SetX($this->marginLeft);
            $this->pdf->Cell($halfW, 4, '', 'LR', 2, 'L');
        }
        $this->pdf->SetX($this->marginLeft);
        $this->pdf->Cell($halfW, 1, '', 'LRB', 2, 'L');

        $bottomLeft = $this->pdf->GetY();

        // ── Meta info (derecha) ──
        $metaX = $this->marginLeft + $halfW + 4;
        $metaW = $halfW;

        $metaData = [
            'Fecha de Remision:' => $this->note->surgery_date?->format('d/m/Y') ?? '-',
            'Elaborado por:' => $this->note->createdBy->name ?? '-',
            'Cirugia:' => $this->note->surgery_type ?? '-',
            'Doctor:' => $this->note->doctor->full_name ?? 'Sin asignar',
        ];

        $metaY = $y;
        foreach ($metaData as $label => $value) {
            // Label
            $this->pdf->SetXY($metaX, $metaY);
            $this->pdf->SetFont('Helvetica', 'B', 7);
            $this->setColor($this->grayText);
            $this->pdf->Cell($metaW * 0.45, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $label), 1, 0, 'L', true);

            // Value
            $this->pdf->SetFont('Helvetica', '', 7);
            $this->setColor($this->black);
            $this->pdf->Cell($metaW * 0.55, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value), 1, 0, 'R');

            $metaY += 6;
        }

        $bottomRight = $metaY;
        $this->pdf->SetY(max($bottomLeft, $bottomRight) + 4);
    }

    // ═══════════════════════════════════════════════════════════
    // TABLA DE ITEMS (PRODUCTOS)
    // ═══════════════════════════════════════════════════════════

    private function drawItemsTable(): void
    {
        // Columnas: Cód | Cant | Unidad | Descripción | P. Unit | Importe
        $colWidths = [22, 14, 14, $this->contentWidth - 22 - 14 - 14 - 28 - 28, 28, 28];
        $headers = ['Cod.', 'Cant.', 'Unidad', iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Descripción'), 'Precio Unit.', 'Importe'];

        // ── Header de tabla ──
        $this->pdf->SetFillColor(...$this->cyan);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('Helvetica', 'B', 7.5);
        $this->pdf->SetDrawColor(...$this->cyan);

        for ($i = 0; $i < count($headers); $i++) {
            $align = $i >= 4 ? 'C' : ($i <= 1 ? 'C' : 'C');
            $this->pdf->Cell($colWidths[$i], 7, $headers[$i], 1, 0, 'C', true);
        }
        $this->pdf->Ln();

        // ── Filas de items ──
        $this->pdf->SetDrawColor(...$this->grayLine);
        $this->pdf->SetFont('Helvetica', '', 7);
        $this->setColor($this->black);

        $items = $this->note->items->sortBy(function ($item) {
            $order = ['package' => 0, 'kit' => 1, 'conditional' => 2, 'standalone' => 3];
            return ($order[$item->item_origin] ?? 4) . '_' . ($item->product->code ?? '');
        });

        $currentOrigin = null;

        foreach ($items as $item) {
            // Verificar si necesitamos nueva página
            if ($this->pdf->GetY() > 255) {
                $this->pdf->AddPage();
                // Re-dibujar header de tabla
                $this->pdf->SetFillColor(...$this->cyan);
                $this->pdf->SetTextColor(255, 255, 255);
                $this->pdf->SetFont('Helvetica', 'B', 7.5);
                for ($i = 0; $i < count($headers); $i++) {
                    $this->pdf->Cell($colWidths[$i], 7, $headers[$i], 1, 0, 'C', true);
                }
                $this->pdf->Ln();
                $this->pdf->SetFont('Helvetica', '', 7);
                $this->setColor($this->black);
            }

            // Separador de origen si cambia el grupo
            if ($currentOrigin !== $item->item_origin) {
                $currentOrigin = $item->item_origin;
                $originLabel = $this->getOriginGroupLabel($currentOrigin);
                $this->pdf->SetFillColor(235, 235, 235);
                $this->pdf->SetFont('Helvetica', 'B', 6.5);
                $this->setColor($this->grayText);
                $this->pdf->Cell(array_sum($colWidths), 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $originLabel), 'TB', 1, 'L', true);
                $this->pdf->SetFont('Helvetica', '', 7);
                $this->setColor($this->black);
            }

            // Fondo especial para urgencias y condicionales
            if ($item->is_urgency) {
                $this->pdf->SetFillColor(...$this->urgencyBg);
                $fill = true;
            } elseif ($item->hasConditional()) {
                $this->pdf->SetFillColor(...$this->conditionalBg);
                $fill = true;
            } else {
                $fill = false;
            }

            $product = $item->product;
            $code = $product->code ?? '-';
            $qty = number_format($item->quantity_required, 2);
            $unit = '1'; // Unidad = pieza
            $unitPrice = number_format((float) $item->unit_price, 2);
            $importe = number_format((float) $item->total_price, 2);

            // Descripción con indicadores
            $desc = $product->name ?? 'Producto';
            if ($item->is_urgency) {
                $desc = '[URGENCIA] ' . $desc;
            }
            if ($item->hasConditional() && $item->conditional_description) {
                $desc .= ' *';
            }

            $desc = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $desc);

            // Calcular alto necesario para descripción (multilinea)
            $descWidth = $colWidths[3] - 2;
            $lineHeight = 4;
            $lines = $this->pdf->GetStringWidth($desc) / $descWidth;
            $cellHeight = max(6, ceil($lines + 0.5) * $lineHeight);

            $y = $this->pdf->GetY();
            $x = $this->marginLeft;

            // Código
            $this->pdf->SetXY($x, $y);
            $this->pdf->SetFont('Helvetica', '', 6.5);
            $this->pdf->Cell($colWidths[0], $cellHeight, $code, 'LTB', 0, 'C', $fill);
            $x += $colWidths[0];

            // Cantidad
            $this->pdf->Cell($colWidths[1], $cellHeight, $qty, 'TB', 0, 'C', $fill);
            $x += $colWidths[1];

            // Unidad
            $this->pdf->Cell($colWidths[2], $cellHeight, $unit, 'TB', 0, 'C', $fill);
            $x += $colWidths[2];

            // Descripción (multilínea)
            $this->pdf->SetFont('Helvetica', '', 7);
            $xDesc = $this->pdf->GetX();
            if ($fill) {
                $this->pdf->SetFillColor(...($item->is_urgency ? $this->urgencyBg : $this->conditionalBg));
            }
            // Dibujar celda de fondo
            $this->pdf->Cell($colWidths[3], $cellHeight, '', 'TB', 0, 'L', $fill);
            // Escribir texto dentro
            $this->pdf->SetXY($xDesc + 1, $y + 1);
            $this->pdf->MultiCell($colWidths[3] - 2, $lineHeight, $desc, 0, 'L');
            $this->pdf->SetXY($xDesc + $colWidths[3], $y);

            // Precio unitario
            $this->pdf->SetFont('Helvetica', '', 7);
            $this->pdf->Cell($colWidths[4], $cellHeight, $unitPrice, 'TB', 0, 'R', $fill);

            // Importe
            $this->pdf->Cell($colWidths[5], $cellHeight, '$' . $importe, 'RTB', 1, 'R', $fill);
        }

        // ── Kits quirúrgicos como conceptos de renta ──
        $kits = $this->note->kits->where('exclude_from_invoice', false);
        if ($kits->isNotEmpty()) {
            $this->pdf->SetFillColor(235, 235, 235);
            $this->pdf->SetFont('Helvetica', 'B', 6.5);
            $this->setColor($this->grayText);
            $this->pdf->Cell(array_sum($colWidths), 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '── Kits Quirúrgicos (Renta) ──'), 'TB', 1, 'L', true);
            $this->pdf->SetFont('Helvetica', '', 7);
            $this->setColor($this->black);

            foreach ($kits as $kit) {
                $kitName = $kit->surgicalKit->name ?? $kit->surgicalKit->code ?? 'Kit';
                $rentalPrice = number_format((float) $kit->rental_price, 2);

                $this->pdf->Cell($colWidths[0], 6, '-', 'LTB', 0, 'C');
                $this->pdf->Cell($colWidths[1], 6, '1.00', 'TB', 0, 'C');
                $this->pdf->Cell($colWidths[2], 6, 'SRV', 'TB', 0, 'C');
                $this->pdf->Cell($colWidths[3], 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Renta: ' . $kitName), 'TB', 0, 'L');
                $this->pdf->Cell($colWidths[4], 6, $rentalPrice, 'TB', 0, 'R');
                $this->pdf->Cell($colWidths[5], 6, '$' . $rentalPrice, 'RTB', 1, 'R');
            }
        }

        // ── Conceptos de renta adicionales ──
        $concepts = $this->note->rentalConcepts->where('exclude_from_invoice', false);
        if ($concepts->isNotEmpty()) {
            $this->pdf->SetFillColor(235, 235, 235);
            $this->pdf->SetFont('Helvetica', 'B', 6.5);
            $this->setColor($this->grayText);
            $this->pdf->Cell(array_sum($colWidths), 5, '── Conceptos de Renta ──', 'TB', 1, 'L', true);
            $this->pdf->SetFont('Helvetica', '', 7);
            $this->setColor($this->black);

            foreach ($concepts as $concept) {
                $total = number_format((float) $concept->total_price, 2);
                $unitP = number_format((float) $concept->unit_price, 2);

                $this->pdf->Cell($colWidths[0], 6, '-', 'LTB', 0, 'C');
                $this->pdf->Cell($colWidths[1], 6, number_format($concept->quantity, 2), 'TB', 0, 'C');
                $this->pdf->Cell($colWidths[2], 6, 'SRV', 'TB', 0, 'C');
                $this->pdf->Cell($colWidths[3], 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $concept->concept), 'TB', 0, 'L');
                $this->pdf->Cell($colWidths[4], 6, $unitP, 'TB', 0, 'R');
                $this->pdf->Cell($colWidths[5], 6, '$' . $total, 'RTB', 1, 'R');
            }
        }

        $this->pdf->Ln(2);
    }

    // ═══════════════════════════════════════════════════════════
    // RESUMEN DE CONDICIONALES APLICADOS
    // ═══════════════════════════════════════════════════════════

    private function drawConditionalsSummary(): void
    {
        $evaluation = $this->note->checklist_evaluation ?? [];
        $conditionals = collect($evaluation)->where('has_conditional', true);

        if ($conditionals->isEmpty()) {
            return;
        }

        // Verificar espacio
        if ($this->pdf->GetY() > 230) {
            $this->pdf->AddPage();
        }

        $this->pdf->SetFont('Helvetica', 'B', 8);
        $this->setColor($this->darkNav);
        $this->pdf->Cell($this->contentWidth, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Condicionales Aplicados'), 0, 1, 'L');

        $this->pdf->SetDrawColor(...$this->cyan);
        $this->pdf->SetLineWidth(0.4);
        $this->pdf->Line($this->marginLeft, $this->pdf->GetY(), $this->marginLeft + $this->contentWidth, $this->pdf->GetY());
        $this->pdf->Ln(1);

        $this->pdf->SetFont('Helvetica', '', 6.5);
        $this->setColor($this->grayText);

        $colW = [50, 20, 20, $this->contentWidth - 50 - 20 - 20];

        // Header
        $this->pdf->SetFillColor(...$this->grayBg);
        $this->pdf->SetFont('Helvetica', 'B', 6.5);
        $this->pdf->Cell($colW[0], 5, 'Producto', 'B', 0, 'L', true);
        $this->pdf->Cell($colW[1], 5, 'Cant. Base', 'B', 0, 'C', true);
        $this->pdf->Cell($colW[2], 5, 'Cant. Final', 'B', 0, 'C', true);
        $this->pdf->Cell($colW[3], 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Descripción del Condicional'), 'B', 1, 'L', true);

        $this->pdf->SetFont('Helvetica', '', 6.5);
        $this->setColor($this->black);

        foreach ($conditionals as $c) {
            $productName = $c['product_name'] ?? '-';
            $base = $c['base_quantity'] ?? 0;
            $final = $c['final_quantity'] ?? 0;
            $desc = $c['conditional_description'] ?? '-';
            $action = $c['action_type'] ?? '';

            // Icono de acción
            $actionIcon = match($action) {
                'adjust_quantity' => '[Ajuste]',
                'exclude' => '[Excluido]',
                'replace' => '[Reemplazo]',
                'add_product' => '[Adicional]',
                'add_dependency' => '[Dependencia]',
                default => '',
            };

            $fullDesc = $actionIcon . ' ' . $desc;

            $this->pdf->Cell($colW[0], 4.5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_substr($productName, 0, 35)), 0, 0, 'L');
            $this->pdf->Cell($colW[1], 4.5, $base, 0, 0, 'C');
            $this->pdf->Cell($colW[2], 4.5, $final, 0, 0, 'C');
            $this->pdf->Cell($colW[3], 4.5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_substr($fullDesc, 0, 70)), 0, 1, 'L');
        }

        $this->pdf->Ln(3);
    }

    // ═══════════════════════════════════════════════════════════
    // TOTALES (SUBTOTAL + IVA + TOTAL)
    // ═══════════════════════════════════════════════════════════

    private function drawTotals(): void
    {
        // Verificar espacio
        if ($this->pdf->GetY() > 245) {
            $this->pdf->AddPage();
        }

        $y = $this->pdf->GetY();

        // ── Importe con letra (izquierda) ──
        $leftW = $this->contentWidth * 0.55;
        $this->pdf->SetXY($this->marginLeft, $y);
        $this->pdf->SetFont('Helvetica', '', 6.5);
        $this->setColor($this->grayText);

        $amountWords = NumberToWordsHelper::convert((float) $this->note->grand_total);
        $this->pdf->MultiCell($leftW, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Importe con letra: ' . $amountWords), 0, 'L');

        $bottomWords = $this->pdf->GetY();

        // ── Cuadro de totales (derecha) ──
        $rightW = $this->contentWidth * 0.40;
        $rightX = $this->pageWidth - $this->marginRight - $rightW;
        $labelW = $rightW * 0.5;
        $valueW = $rightW * 0.5;

        $this->pdf->SetXY($rightX, $y);

        // Subtotal
        $this->pdf->SetFont('Helvetica', 'B', 8);
        $this->setColor($this->black);
        $this->pdf->Cell($labelW, 7, 'Subtotal:', 1, 0, 'R');
        $this->pdf->SetFont('Helvetica', '', 8);
        $this->pdf->Cell($valueW, 7, '$' . number_format((float) $this->note->subtotal, 2), 1, 1, 'R');

        // IVA
        $this->pdf->SetX($rightX);
        $this->pdf->SetFont('Helvetica', 'B', 8);
        $taxPct = round((float) $this->note->tax_rate * 100, 0);
        $this->pdf->Cell($labelW, 7, 'I.V.A. (' . $taxPct . '%):', 1, 0, 'R');
        $this->pdf->SetFont('Helvetica', '', 8);
        $this->pdf->Cell($valueW, 7, '$' . number_format((float) $this->note->tax_amount, 2), 1, 1, 'R');

        // Total con barra cyan
        $this->pdf->SetX($rightX);
        $this->pdf->SetFont('Helvetica', 'B', 9);
        $this->pdf->SetFillColor(...$this->cyan);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->Cell($labelW, 8, 'Total:', 1, 0, 'R', true);
        $this->pdf->Cell($valueW, 8, '$' . number_format((float) $this->note->grand_total, 2), 1, 1, 'R', true);

        $this->setColor($this->black);
        $this->pdf->SetY(max($bottomWords, $this->pdf->GetY()) + 4);
    }

    // ═══════════════════════════════════════════════════════════
    // NOTAS
    // ═══════════════════════════════════════════════════════════

    private function drawNotes(): void
    {
        // Verificar espacio
        if ($this->pdf->GetY() > 255) {
            $this->pdf->AddPage();
        }

        $this->pdf->SetFont('Helvetica', 'B', 8);
        $this->setColor($this->cyan);
        $this->pdf->Cell(15, 5, 'Notas', 0, 1, 'L');

        $this->pdf->SetFont('Helvetica', '', 7);
        $this->setColor($this->black);

        $notes = [];
        if ($this->note->doctor) {
            $notes[] = $this->note->doctor->full_name ?? '';
        }
        if ($this->note->hospital) {
            $notes[] = $this->note->hospital->name ?? '';
        }
        $notes[] = $this->note->surgery_type ?? '';
        $notes[] = $this->note->shipping_number;

        if ($this->note->notes) {
            $notes[] = '';
            $notes[] = $this->note->notes;
        }

        // Items de urgencia
        $urgencyItems = $this->note->items->where('is_urgency', true);
        if ($urgencyItems->isNotEmpty()) {
            $notes[] = '';
            $notes[] = 'ITEMS DE URGENCIA:';
            foreach ($urgencyItems as $item) {
                $reason = $item->urgency_reason ? " - {$item->urgency_reason}" : '';
                $notes[] = '  * ' . ($item->product->name ?? 'Producto') . " (x{$item->quantity_required})" . $reason;
            }
        }

        foreach ($notes as $line) {
            $this->pdf->SetX($this->marginLeft);
            $this->pdf->MultiCell($this->contentWidth, 3.8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $line), 0, 'L');
        }

        $this->pdf->Ln(3);
    }

    // ═══════════════════════════════════════════════════════════
    // FOOTER
    // ═══════════════════════════════════════════════════════════

    private function drawFooter(): void
    {
        $this->pdf->SetY(-15);
        $this->pdf->SetFont('Helvetica', 'B', 7);
        $this->setColor($this->grayText);
        $this->pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Página ') . $this->pdf->PageNo(), 0, 0, 'R');
    }

    // ═══════════════════════════════════════════════════════════
    // UTILIDADES
    // ═══════════════════════════════════════════════════════════

    private function setColor(array $rgb): void
    {
        $this->pdf->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
    }

    private function getOriginGroupLabel(string $origin): string
    {
        return match($origin) {
            'package' => '── Paquete Pre-armado (Consumibles) ──',
            'kit' => '── Kit Quirúrgico (Instrumental) ──',
            'conditional' => '── Productos por Condicional ──',
            'standalone' => '── Productos Individuales ──',
            default => '── Otros ──',
        };
    }
}
