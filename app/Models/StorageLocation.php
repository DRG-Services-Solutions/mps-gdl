<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{
    protected $fillable = [
        'area',
        'organizer',
        'shelf_level',
        'shelf_section',
        'description',
    ];

    protected $casts = [
        'shelf_level' => 'integer',
        'shelf_section' => 'integer',
    ];

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class, 'current_location_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'destination_warehouse_id');
    }

    public function scopeActive($query, string $area)
    {
        return $query->where('area', $area);
    }

    public function getFullLocationAttribute()
    {
        return "{$this->area}-{$this->organizer}-{$this->shelf_level}-{$this->shelf_section}";
    }
}