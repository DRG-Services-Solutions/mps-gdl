<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity_ordered',
        'quantity_received',
        'unit_price',
        'subtotal',
        'product_code',
        'product_name',
        'description',
        'received_by',
        'received_at',
        'batch_number',
        'expiration_date',
        'manufacture_date',
        'status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'received_at' => 'datetime',
        'expiration_date' => 'date',
        'manufacture_date' => 'date',
    ];

    // Relaciones
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }

    // Atributos calculados
    public function getPendingQuantityAttribute(): int
    {
        return max(0, $this->quantity_ordered - $this->quantity_received);
    }

    public function isFullyReceived(): bool
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }

    public function getReceiptProgressAttribute(): float
    {
        if ($this->quantity_ordered == 0) return 0;
        return round(($this->quantity_received / $this->quantity_ordered) * 100, 2);
    }

    // Obtener todas las recepciones de este item
    public function getReceiptsHistoryAttribute()
    {
        return $this->receiptItems()
            ->with('receipt.receivedBy')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateStatus(): void
    {
        if ($this->quantity_received == 0) {
            $this->status = 'pending';
        } elseif ($this->quantity_received < $this->quantity_ordered) {
            $this->status = 'partial';
        } elseif ($this->quantity_received >= $this->quantity_ordered) {
            $this->status = 'received';
        }
        $this->save();
    }
}