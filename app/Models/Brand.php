<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ==================== RELACIONES ====================
    
    /**
     * Productos de esta marca
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // ==================== SCOPES ====================
    
    /**
     * Scope para marcas activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordenado por nombre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}