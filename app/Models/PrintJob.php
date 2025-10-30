<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends Model
{
    protected $fillable = [
        'job_number',
        'receipt_id',
        'product_unit_id',
        'printer_name',
        'zpl_commands',
        'epc_code',
        'label_data',
        'status',
        'retry_count',
        'error_message',
        'sent_at',
        'printed_at',
    ];

    protected $casts = [
        'label_data' => 'array',
        'sent_at' => 'datetime',
        'printed_at' => 'datetime',
    ];

    // Relaciones
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderReceipt::class, 'receipt_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Métodos de estado
    public function markAsPrinting(): void
    {
        $this->update([
            'status' => 'printing',
            'sent_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'printed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'retry_count' => $this->retry_count + 1,
            'error_message' => $errorMessage,
        ]);
    }

    public function canRetry(): bool
    {
        return $this->retry_count < 3; // Máximo 3 reintentos
    }

    // Generar número de trabajo
    //Necesita referencia (Erick)
    public static function generateJobNumber(): string
    {
        $date = now()->format('Ymd');
        $lastJob = self::where('job_number', 'like', "PRINT-{$date}-%")
            ->orderBy('job_number', 'desc')
            ->first();

        if (!$lastJob) {
            return "PRINT-{$date}-001";
        }

        $lastNumber = (int) substr($lastJob->job_number, -3);
        return "PRINT-{$date}-" . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}