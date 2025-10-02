<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorageLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'parent_location_id',
        'building',
        'floor',
        'room',
        'area',
        'shelf',
        'description',
        'requires_authorization',
        'is_active',
        'responsible_user_id',
    ];

    protected $casts = [
        'requires_authorization' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function parentLocation()
    {
        return $this->belongsTo(StorageLocation::class, 'parent_location_id');
    }

    public function childLocations()
    {
        return $this->hasMany(StorageLocation::class, 'parent_location_id');
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class, 'current_location_id');
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}