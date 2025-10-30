<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrintJobController extends Controller
{
    /**
     * Obtener trabajos pendientes de impresión
     * GET /api/print-jobs/pending
     */
    public function pending(): JsonResponse
    {
        $jobs = PrintJob::with(['receipt', 'productUnit.product'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'job_number' => $job->job_number,
                    'epc_code' => $job->epc_code,
                    'zpl_commands' => $job->zpl_commands,
                    'label_data' => $job->label_data,
                    'retry_count' => $job->retry_count,
                    'created_at' => $job->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $jobs->count(),
            'jobs' => $jobs,
        ]);
    }

    /**
     * Marcar trabajo como "imprimiendo"
     * POST /api/print-jobs/{id}/printing
     */
    public function markAsPrinting(PrintJob $printJob): JsonResponse
    {
        $printJob->markAsPrinting();

        return response()->json([
            'success' => true,
            'message' => 'Job marked as printing',
            'job' => [
                'id' => $printJob->id,
                'status' => $printJob->status,
            ],
        ]);
    }

    /**
     * Marcar trabajo como completado
     * POST /api/print-jobs/{id}/complete
     */
    public function markAsCompleted(PrintJob $printJob): JsonResponse
    {
        $printJob->markAsCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Job completed successfully',
            'job' => [
                'id' => $printJob->id,
                'status' => $printJob->status,
                'printed_at' => $printJob->printed_at,
            ],
        ]);
    }

    /**
     * Marcar trabajo como fallido
     * POST /api/print-jobs/{id}/fail
     */
    public function markAsFailed(Request $request, PrintJob $printJob): JsonResponse
    {
        $request->validate([
            'error_message' => 'required|string|max:1000',
        ]);

        $printJob->markAsFailed($request->error_message);

        return response()->json([
            'success' => true,
            'message' => 'Job marked as failed',
            'job' => [
                'id' => $printJob->id,
                'status' => $printJob->status,
                'retry_count' => $printJob->retry_count,
                'can_retry' => $printJob->canRetry(),
            ],
        ]);
    }

    /**
     * Obtener estadísticas de impresión
     * GET /api/print-jobs/stats
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'pending' => PrintJob::where('status', 'pending')->count(),
            'printing' => PrintJob::where('status', 'printing')->count(),
            'completed' => PrintJob::where('status', 'completed')->count(),
            'failed' => PrintJob::where('status', 'failed')->count(),
            'total' => PrintJob::count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Health check del servicio
     * GET /api/print-jobs/health
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => 'online',
            'timestamp' => now()->toISOString(),
        ]);
    }
}