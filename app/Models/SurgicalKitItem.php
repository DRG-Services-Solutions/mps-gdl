<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgicalKitItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'surgical_kit_id',
        'product_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Kit al que pertenece
     */
    public function surgicalKit(): BelongsTo
    {
        return $this->belongsTo(SurgicalKit::class);
    }

    /**
     * Producto del catálogo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}