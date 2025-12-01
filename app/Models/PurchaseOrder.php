<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'supplier_id',
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
        'created_by',
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

    // ========================================
    // RELACIONES
    // ========================================

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class, 'destination_warehouse_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // ⬇️ NUEVA: Relación con recepciones
    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseOrderReceipt::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // ⬇️ NUEVO: Órdenes que pueden ser recibidas
    public function scopeCanBeReceived($query)
    {
        return $query->whereIn('status', ['pending', 'partial'])
                     ->where('status', '!=', 'cancelled');
    }

    // ========================================
    // MÉTODOS DE ESTADO Y VALIDACIÓN
    // ========================================

    public function canBeEdited(): bool
    {
        return !in_array($this->status, ['cancelled', 'received']);
    }

    // ⬇️ NUEVO: Verificar si puede recibirse
    public function canBeReceived(): bool
    {
        return in_array($this->status, ['pending', 'partial']) 
               && $this->status !== 'cancelled' 
               && !$this->isFullyReceived();
    }

    // ⬇️ NUEVO: Verificar si está completamente recibida
    public function isFullyReceived(): bool
    {
        // Si no hay items, no está recibida
        if ($this->items->count() === 0) {
            return false;
        }

        // Verificar que todos los items estén completamente recibidos
        foreach ($this->items as $item) {
            if (!$item->isFullyReceived()) {
                return false;
            }
        }

        return true;
    }

    // ⬇️ NUEVO: Verificar si tiene alguna recepción parcial
    public function hasPartialReceipt(): bool
    {
        return $this->items->sum('quantity_received') > 0 && !$this->isFullyReceived();
    }

    // ========================================
    // ATRIBUTOS CALCULADOS
    // ========================================

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity_ordered');
    }

    public function getTotalReceivedAttribute(): int
    {
        return $this->items->sum('quantity_received');
    }

    // ⬇️ NUEVO: Cantidad pendiente de recibir
    public function getTotalPendingAttribute(): int
    {
        return $this->total_items - $this->total_received;
    }

    // ⬇️ NUEVO: Porcentaje de progreso de recepción
    public function getReceiptProgressAttribute(): float
    {
        $total = $this->total_items;
        if ($total == 0) return 0;
        return round(($this->total_received / $total) * 100, 2);
    }

    // ⬇️ NUEVO: Última recepción registrada
    public function getLatestReceiptAttribute(): ?PurchaseOrderReceipt
    {
        return $this->receipts()->latest('received_at')->first();
    }

    // ⬇️ NUEVO: Total de recepciones realizadas
    public function getTotalReceiptsAttribute(): int
    {
        return $this->receipts()->count();
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Pendiente',
            'received' => 'Recibida',
            'partial' => 'Parcial',
            'cancelled' => 'Cancelada',
            'in_return' => 'En Devolución',
            'approved' => 'Aprobada', // Por si lo usas
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'pending' => 'yellow',
            'received' => 'green',
            'partial' => 'blue',
            'cancelled' => 'red',
            'in_return' => 'orange',
            'approved' => 'indigo',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    // ========================================
    // MÉTODOS ESTÁTICOS
    // ========================================

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $lastOrder = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastOrder ? (int) substr($lastOrder->order_number, -4) + 1 : 1;

        return 'PO-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // ========================================
    // MÉTODOS DE CÁLCULO
    // ========================================

    public function calculateTotals(): void
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

    // ⬇️ NUEVO: Actualizar estado según recepciones
    public function updateStatusBasedOnReceipts(): void
    {
        if ($this->isFullyReceived()) {
            $this->update([
                'status' => 'received',
                'received_date' => now(),
            ]);
        } elseif ($this->total_received > 0) {
            $this->update([
                'status' => 'partial',
            ]);
        }
    }

    // ========================================
    // BOOT
    // ========================================

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
            if (empty($order->created_by)) {
                $order->created_by = auth()->id();
            }
        });

        // ⬇️ NUEVO: Al actualizar, verificar estado de recepción
        static::updating(function ($order) {
            // Si se está marcando como recibida, actualizar fecha
            if ($order->isDirty('status') && $order->status === 'received' && empty($order->received_date)) {
                $order->received_date = now();
            }
        });
    }
}