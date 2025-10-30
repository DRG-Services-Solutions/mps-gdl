<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PrintJobController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas API para el Print Agent RFID
|
*/

// Health check público (sin autenticación)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
    ]);
});

// Rutas para Print Agent (requiere IP whitelisting)
Route::middleware(['check.print.agent'])->prefix('print-jobs')->group(function () {
    Route::get('/pending', [PrintJobController::class, 'pending']);
    Route::post('/{printJob}/printing', [PrintJobController::class, 'markAsPrinting']);
    Route::post('/{printJob}/complete', [PrintJobController::class, 'markAsCompleted']);
    Route::post('/{printJob}/fail', [PrintJobController::class, 'markAsFailed']);
    Route::get('/stats', [PrintJobController::class, 'stats']);
    Route::get('/health', [PrintJobController::class, 'health']);
});