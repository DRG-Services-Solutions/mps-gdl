<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalEntity extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'razon_social',
        'rfc',
        'address',
        'phone',
        'email',
        'is_active',

    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relaciones
     */

    public function getActiveSubWarehouses()
    {
        return $this->subWarehouses()->active()->orderBy('name')->get();
    }

    public function subWarehouses(): HasMany
    {
        return $this->hasMany(SubWarehouse::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

        /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Método para obtener valor total del inventario de esta entidad legal
     */
    public function getTotalInventoryValue()
    {
        return $this->productUnits()
            ->sum('acquisition_cost');
    }

    /**
     * Método para obtener cantidad de unidades en inventario
     */
    public function getTotalUnitsCount()
    {
        return $this->productUnits()->count();
    }

    /**
     * Método para obtener cantidad de órdenes de compra
     */
    public function getTotalPurchaseOrders()
    {
        return $this->purchaseOrders()->count();
    }





}
