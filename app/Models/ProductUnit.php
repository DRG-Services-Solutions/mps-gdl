<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'unique_identifier',
        'identifier_type',
        'current_location_id',
        'status',
        'lot_number',
        'expiration_date',
        'received_date',
        'sterilization_cycles',
        'last_sterilization_date',
        'next_sterilization_due',
        'max_sterilization_cycles',
        'assigned_to_surgery_id',
        'assigned_to_patient_id',
        'assigned_at',
        'acquisition_cost',
        'notes',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'received_date' => 'date',
        'last_sterilization_date' => 'date',
        'next_sterilization_due' => 'date',
        'assigned_at' => 'datetime',
        'acquisition_cost' => 'decimal:2',
    ];

    // Relaciones
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function currentLocation()
    {
        return $this->belongsTo(StorageLocation::class, 'current_location_id');
    }

    public function assignedToSurgery()
    {
        return $this->belongsTo(Surgery::class, 'assigned_to_surgery_id');
    }

    public function assignedToPatient()
    {
        return $this->belongsTo(Patient::class, 'assigned_to_patient_id');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeInUse($query)
    {
        return $query->where('status', 'in_use');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiration_date')
                    ->whereDate('expiration_date', '<=', now()->addDays($days));
    }
}