<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class SubWareHouse extends Model
{
    protected $fillable = [
        'legal_entity_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    
    public function purchaseOrders(): HasMany
    {   
        return $this->hasMany(PurchaseOrder::class);
    }   

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }

    //Obtenemos todos los productos asignados a este sub almacen
    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    //Obtenemos todos los movimientos de inventario asignados a este sub almacen
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

   

    // ========================================
    // SCOPES
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLegalEntity($query, $legalEntityId)
    {
        return $query->where('legal_entity_id', $legalEntityId);
    }

    // ========================================
    // Metodos utiles
    // ========================================

    /**
     * Obtener conteo de unidades en este sub-almacén
     */
    public function getTotalUnits(): int
    {
        return $this->productUnits()->count();
    }

    /**
     * Obtener valor total del inventario
     */
    public function getTotalValue(): float
    {
        return $this->productUnits()->sum('acquisition_cost') ?? 0;
    }


}
