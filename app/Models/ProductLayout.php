<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductLayout extends Model
{
    protected $fillable = [
        'storage_location_id',
        'shelf',
        'level',
        'position',
    ];

    public function storageLocation()
    {
        return $this->belongsTo(StorageLocation::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
