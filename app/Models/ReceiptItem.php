<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptItem extends Model
{
    protected $fillable = [
        'receipt_id',
        'purchase_order_item_id',
        'product_id',
        'quantity_ordered',
        'quantity_received',
        'unit_price',
        'batch_number',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'unit_price' => 'decimal:2',
    ];

    // Relaciones
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderReceipt::class, 'receipt_id');
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Calcular subtotal
    public function getSubtotalAttribute(): float
    {
        return $this->quantity_received * $this->unit_price;
    }

    // Verificar si hay discrepancia
    public function hasDiscrepancyAttribute(): bool
    {
        return $this->quantity_received != $this->quantity_ordered;
    }

    // Verificar si está en buenas condiciones
    public function isGoodConditionAttribute(): bool
    {
        return $this->condition === 'good';
    }
}