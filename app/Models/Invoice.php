<?php
// app/Models/Invoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'scheduled_surgery_id',
        'hospital_id',
        'hospital_name',
        'hospital_address',
        'hospital_rfc',
        'invoice_date',
        'subtotal',
        'iva',
        'total',
        'status',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * RELACIONES
     */
    
    // Cirugía asociada
    public function scheduledSurgery()
    {
        return $this->belongsTo(ScheduledSurgery::class, 'scheduled_surgery_id');
    }

    // Hospital (cliente)
    public function hospital()
    {
        return $this->belongsTo(LegalEntity::class, 'hospital_id');
    }

    // Usuario que creó
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Líneas de la remisión
    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Generar folio único
    public static function generateInvoiceNumber()
    {
        $date = now()->format('Ymd');
        $random = rand(1000, 9999);
        $number = "REM-{$date}-{$random}";

        while (self::where('invoice_number', $number)->exists()) {
            $random = rand(1000, 9999);
            $number = "REM-{$date}-{$random}";
        }

        return $number;
    }

    // Calcular totales
    public function calculateTotals()
    {
        $subtotal = $this->items->sum('subtotal');
        $iva = $subtotal * 0.16; // IVA 16%
        $total = $subtotal + $iva;

        $this->update([
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total,
        ]);
    }

    // Emitir remisión
    public function issue()
    {
        $this->update(['status' => 'issued']);
    }

    // Cancelar remisión
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    // Marcar como pagada
    public function markAsPaid()
    {
        $this->update(['status' => 'paid']);
    }
}