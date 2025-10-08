<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'warehouse_id',
        'received_by',
        'received_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    // Relaciones
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class, 'warehouse_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceiptItem::class, 'receipt_id');
    }

    // Generar número de recepción automático
    public static function generateReceiptNumber(): string
    {
        $year = date('Y');
        $lastReceipt = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastReceipt ? intval(substr($lastReceipt->receipt_number, -4)) + 1 : 1;
        
        return 'REC-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Calcular total de piezas recibidas
    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity_received');
    }

    // Calcular total de productos diferentes
    public function getTotalProductsAttribute(): int
    {
        return $this->items->count();
    }

    // Verificar si hay productos dañados
    public function hasDamagedItemsAttribute(): bool
    {
        return $this->items()->where('condition', '!=', 'good')->exists();
    }
}