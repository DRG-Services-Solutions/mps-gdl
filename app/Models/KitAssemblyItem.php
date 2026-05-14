<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitAssemblyItem extends Model
{
    // 🚨 ACTUALIZADO: Cambiamos product_id por component_item_id
    protected $fillable = [
        'kit_assembly_id',
        'component_item_id', 
        'quantity_expected',
        'quantity_found',
        'serial_numbers'
    ];

    protected $casts = [
        'serial_numbers' => 'array',
    ];

    public function assembly()
    {
        return $this->belongsTo(KitAssembly::class, 'kit_assembly_id');
    }

    // 🚨 ACTUALIZADO: Ahora apunta al modelo Item (El Instrumental que metes a la charola)
    public function componentItem()
    {
        return $this->belongsTo(Item::class, 'component_item_id');
    }
}