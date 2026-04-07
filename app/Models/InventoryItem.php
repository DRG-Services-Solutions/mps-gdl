<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'parent_item_id',
        'serial_number',
        'lot_number',
        'rfid_tag',
        'expiration_date',
        'quantity',
        'status'
    ];

    // Relaciones

    // Cada pieza física pertenece a un producto del catálogo
    public function product()
        {
            return $this->belongsTo(Product::class);
        }

    public function parentContainer()
        {
            return $this->belongsTo(InventoryItem::class, 'parent_item_id');
        }

    public function containedItems()
    {
        return $this->hasMany(InventoryItem::class, 'parent_item_id');
    }
}
