<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventorySummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_entity_id',
        'sub_warehouse_id',
        'product_id',
        'quantity_on_hand',
        'quantity_reserved',
        'batch_number',
        'expiration_date',
    ];

    protected $casts = [
        'quantity_on_hand' => 'float',
        'quantity_reserved' => 'float',
        'expiration_date'   => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function subWarehouse(): BelongsTo
    {
        return $this->belongsTo(SubWarehouse::class);
    }

    //scope de caducidad
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiration_date')
                    ->where('expiration_date', '<=', now()->addDays($days))
                    ->where('quantity_on_hand', '>', 0) 
                    ->orderBy('expiration_date', 'asc');
    }

    //Accessor para calcular la cantidad disponible
    public function getQuantityAvailableAttribute(): float
    {
        return max(0, $this->quantity_on_hand - $this->quantity_reserved);
    }

}
