<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'product_id',
        'product_unit_id',
        'quantity',
        'from_location_id',
        'to_location_id',
        'user_id',
        'reference_number',
        'notes',
        'reason',
        'surgery_id',
        'patient_id',
        'unit_cost',
        'total_cost',
        'lot_number',
        'expiration_date',
        'movement_date',
        'approved_at',
        'approved_by',
        'legal_entity_id',
        'sub_warehouse_id',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'movement_date' => 'datetime',
        'approved_at' => 'datetime',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // Relaciones
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }
    public function subWarehouse(): BelongsTo
    {
        return $this->belongsTo(SubWarehouse::class);
    }

    public function fromLocation()
    {
        return $this->belongsTo(StorageLocation::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(StorageLocation::class, 'to_location_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    
}