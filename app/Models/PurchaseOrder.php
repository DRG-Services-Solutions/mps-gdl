<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'supplier_id',
        'destination_warehouse_id',
        'created_by',
        'status',
        'order_date',
        'expected_date',
        'received_date',
        'subtotal',
        'tax',
        'total',
        'is_paid',
        'paid_date',
        'notes',
        'cancellation_reason',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'paid_date' => 'date',
        'is_paid' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relaciones
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(StorageLocation::class, 'destination_warehouse_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    // Métodos auxiliares
    public function canBeEdited(): bool
    {
        return $this->status !== 'cancelled';
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity_ordered');
    }

    public function getTotalReceivedAttribute()
    {
        return $this->items->sum('quantity_received');
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Pendiente',
            'received' => 'Recibida',
            'partial' => 'Parcial',
            'cancelled' => 'Cancelada',
            'in_return' => 'En Devolución',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'pending' => 'yellow',
            'received' => 'green',
            'partial' => 'blue',
            'cancelled' => 'red',
            'in_return' => 'orange',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    // Generar número de orden automáticamente
    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $lastOrder = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastOrder ? (int) substr($lastOrder->order_number, -4) + 1 : 1;

        return 'PO-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Calcular totales
    public function calculateTotals()
    {
        $subtotal = $this->items->sum('subtotal');
        $tax = $subtotal * 0.16; // IVA 16%
        $total = $subtotal + $tax;

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
            if (empty($order->order_date)) {
                $order->order_date = now();
            }
            $order->created_by = auth()->id();
        });
    }
}