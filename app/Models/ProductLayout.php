<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ProductLayout extends Model
{
    protected $fillable = [
        'storage_location_id',
        'shelf',
        'level',
        'position',
        'product_id',

        
    ];

    public function storageLocation()
    {
        return $this->belongsTo(StorageLocation::class);
    }

    /**
     * Obtenemos el producto asociado a este layout.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function hasProduct(): bool
    {
        return !is_null($this->product_id);
    }

    public function assignProduct(int $productId): bool
    {
        $this->product_id = $productId;
        return $this->save();
    }
}
