<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\PurchaseOrderReceipt;
use App\Services\RfidLabelService;
use Illuminate\Http\Request;

class PrintJobMonitorController extends Controller
{
    /**
     * Mostrar trabajos de impresión de una recepción
     */
    public function show(PurchaseOrderReceipt $receipt)
    {
        $printJobs = PrintJob::where('receipt_id', $receipt->id)
            ->with('productUnit.product')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $stats = [
            'pending' => PrintJob::where('receipt_id', $receipt->id)->where('status', 'pending')->count(),
            'printing' => PrintJob::where('receipt_id', $receipt->id)->where('status', 'printing')->count(),
            'completed' => PrintJob::where('receipt_id', $receipt->id)->where('status', 'completed')->count(),
            'failed' => PrintJob::where('receipt_id', $receipt->id)->where('status', 'failed')->count(),
            'total' => $printJobs->total(),
        ];

        return view('print-jobs.show', compact('receipt', 'printJobs', 'stats'));
    }

    /**
     * Reintentar trabajos fallidos
     */
    public function retry(PurchaseOrderReceipt $receipt)
    {
        $rfidService = new RfidLabelService();
        $retriedCount = $rfidService->retryFailedJobs($receipt);

        return back()->with('success', "{$retriedCount} trabajos marcados para reintento.");
    }

    /**
     * Cancelar trabajos pendientes
     */
    public function cancel(PurchaseOrderReceipt $receipt)
    {
        $cancelledCount = PrintJob::where('receipt_id', $receipt->id)
            ->whereIn('status', ['pending', 'failed'])
            ->update(['status' => 'cancelled']);

        return back()->with('success', "{$cancelledCount} trabajos cancelados.");
    }
}