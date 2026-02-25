{{-- resources/views/invoices/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remisión {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .company-info {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        
        .company-details {
            font-size: 10px;
            color: #666;
            line-height: 1.6;
        }
        
        .invoice-info {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 10px;
        }
        
        .invoice-details {
            font-size: 11px;
        }
        
        .invoice-details strong {
            color: #4F46E5;
        }
        
        /* Client Info */
        .client-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #F9FAFB;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .client-section h3 {
            font-size: 14px;
            color: #4F46E5;
            margin-bottom: 10px;
            border-bottom: 2px solid #E5E7EB;
            padding-bottom: 5px;
        }
        
        .client-grid {
            display: table;
            width: 100%;
        }
        
        .client-col {
            display: table-cell;
            width: 50%;
            padding-right: 15px;
        }
        
        .info-row {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 10px;
        }
        
        .info-value {
            color: #333;
            font-size: 11px;
        }
        
        /* Products Table */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .products-table thead {
            background-color: #4F46E5;
            color: white;
        }
        
        .products-table th {
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        
        .products-table th.text-center {
            text-align: center;
        }
        
        .products-table th.text-right {
            text-align: right;
        }
        
        .products-table tbody tr {
            border-bottom: 1px solid #E5E7EB;
        }
        
        .products-table tbody tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        
        .products-table td {
            padding: 10px 8px;
            font-size: 10px;
        }
        
        .products-table td.text-center {
            text-align: center;
        }
        
        .products-table td.text-right {
            text-align: right;
        }
        
        .product-name {
            font-weight: bold;
            color: #333;
            font-size: 11px;
        }
        
        .product-code {
            color: #666;
            font-size: 9px;
        }
        
        /* Totals */
        .totals-section {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table tr {
            border-bottom: 1px solid #E5E7EB;
        }
        
        .totals-table td {
            padding: 8px;
            font-size: 11px;
        }
        
        .totals-table td:first-child {
            text-align: right;
            font-weight: bold;
            color: #666;
        }
        
        .totals-table td:last-child {
            text-align: right;
            font-weight: bold;
            width: 120px;
        }
        
        .totals-table .total-row {
            background-color: #FEF3C7;
            border-top: 2px solid #D97706;
        }
        
        .totals-table .total-row td {
            font-size: 14px;
            font-weight: bold;
            color: #D97706;
            padding: 12px 8px;
        }
        
        /* Notes */
        .notes-section {
            clear: both;
            margin-top: 30px;
            padding: 15px;
            background-color: #EFF6FF;
            border-left: 4px solid #3B82F6;
            border-radius: 4px;
        }
        
        .notes-section h4 {
            font-size: 12px;
            color: #3B82F6;
            margin-bottom: 8px;
        }
        
        .notes-section p {
            font-size: 10px;
            color: #666;
            line-height: 1.6;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
        
        .footer-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        
        .footer-col {
            display: table-cell;
            width: 33.33%;
            text-align: center;
        }
        
        /* Page break */
        .page-break {
            page-break-after: always;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-blue {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        @media print {
    .print-instructions {
        display: none !important;
    }
}

kbd {
    background: #f4f4f4;
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 2px 6px;
    font-family: monospace;
}
    </style>
</head>
<body>
    <div class="print-instructions" style="background: #EFF6FF; border: 2px solid #3B82F6; padding: 15px; margin: 20px; border-radius: 8px; text-align: center;">
        <p style="margin: 0; color: #1E40AF; font-weight: bold; font-size: 14px;">
            <i class="fas fa-info-circle"></i> 
            Para guardar como PDF: Presiona <kbd style="background: #fff; padding: 2px 6px; border: 1px solid #ccc; border-radius: 3px;">Ctrl + P</kbd> 
            y selecciona "Guardar como PDF" en el destino
        </p>
    </div>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="company-info">
                    <div class="company-name">DRG Services & Solutions</div>
                    <div class="company-details">
                        RFC: DRG123456ABC<br>
                        Dirección: Av. Principal #123, Col. Centro<br>
                        Tel: (999) 123-4567<br>
                        Email: contacto@drg.com
                    </div>
                </div>
                <div class="invoice-info">
                    <div class="invoice-title">REMISIÓN</div>
                    <div class="invoice-details">
                        <strong>No. Remisión:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>Folio:</strong> {{ $invoice->folio }}<br>
                        <strong>Fecha:</strong> {{ $invoice->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Client Information -->
        <div class="client-section">
            <h3>INFORMACIÓN DEL HOSPITAL</h3>
            <div class="client-grid">
                <div class="client-col">
                    <div class="info-row">
                        <div class="info-label">Razón Social:</div>
                        <div class="info-value">{{ $invoice->hospital->business_name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">RFC:</div>
                        <div class="info-value">{{ $invoice->hospital->rfc }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Dirección:</div>
                        <div class="info-value">{{ $invoice->hospital->address }}</div>
                    </div>
                </div>
                <div class="client-col">
                    <div class="info-row">
                        <div class="info-label">Código de Cirugía:</div>
                        <div class="info-value">{{ $invoice->surgery->code }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Paciente:</div>
                        <div class="info-value">{{ $invoice->surgery->patient_name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Doctor:</div>
                        <div class="info-value">{{ $invoice->surgery->doctor->name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tipo de Cirugía:</div>
                        <div class="info-value">{{ $invoice->surgery->checklist->name }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 50%">PRODUCTO</th>
                    <th class="text-center" style="width: 15%">CANTIDAD</th>
                    <th class="text-right" style="width: 17.5%">PRECIO UNIT.</th>
                    <th class="text-right" style="width: 17.5%">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <div class="product-name">{{ $item->product->name }}</div>
                        <div class="product-code">{{ $item->product->code }}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-blue">{{ $item->quantity }}</span>
                    </td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td>${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>IVA (16%):</td>
                    <td>${{ number_format($invoice->iva, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td>${{ number_format($invoice->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes-section">
            <h4>NOTAS / OBSERVACIONES</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>¡GRACIAS POR SU PREFERENCIA!</strong></p>
            <div class="footer-grid">
                <div class="footer-col">
                    <strong>Generado por:</strong><br>
                    {{ $invoice->creator->name }}
                </div>
                <div class="footer-col">
                    <strong>Fecha de impresión:</strong><br>
                    {{ now()->format('d/m/Y H:i') }}
                </div>
                <div class="footer-col">
                    <strong>Sistema MPS</strong><br>
                    Medical Products System
                </div>
            </div>
        </div>
    </div>
</body>
</html>