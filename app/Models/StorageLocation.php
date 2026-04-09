<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{
    // Si el nombre de la tabla respeta convención (storage_locations) puedes omitir esto.
    protected $table = 'storage_locations';

    protected $fillable = [
        'name',
        'description',
        'code',
    ];


    /* ============================
     Relaciones
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

    public function productLayouts()
    {
        return $this->hasMany(ProductLayout::class);
    }

}
