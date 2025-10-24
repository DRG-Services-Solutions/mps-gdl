<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{
    // Si el nombre de la tabla respeta convención (storage_locations) puedes omitir esto.
    protected $table = 'storage_locations';

    protected $fillable = [
        'area',
        'organizer',
        'shelf_level',
        'shelf_section',
        'description',
        
    ];

    protected $casts = [
        'shelf_level'   => 'integer',
        'shelf_section' => 'integer',
       
    ];

    /* ============================
       📦 Relaciones
    ============================ */

    // Relación con unidades de producto (ubicaciones actuales)
    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class, 'current_location_id');
    }

    // Relación con órdenes de compra (destino del almacén)
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'destination_warehouse_id');
    }

    /* ============================
       🔍 Scopes (Filtros reutilizables)
    ============================ */

   

    // ✅ Si quieres filtrar por área: scope específico
    public function scopeByArea($query, string $area)
    {
        return $query->where('area', $area);
    }

   

    /* ============================
       🏷 Accesor (mostrar ubicación completa)
    ============================ */

    public function getFullLocationAttribute()
    {
        return "{$this->area}-{$this->organizer}-{$this->shelf_level}-{$this->shelf_section}";
    }
}
