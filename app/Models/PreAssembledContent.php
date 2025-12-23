<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreAssembledContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'product_id',
        'product_unit_id',
        'quantity',
        'added_at',
        'added_by',
        'expiration_date',
        'entry_date',
    ];

    protected $casts = [
        'added_at' => 'datetime',
        'expiration_date' => 'date',
        'entry_date' => 'date',
        'quantity' => 'integer',
    ];

    /**
     * RELACIONES
     */
    
    // Paquete al que pertenece
    public function package()
    {
        return $this->belongsTo(PreAssembledPackage::class, 'package_id');
    }

    // Producto
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Unidad física (EPC específico)
    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    // Usuario que agregó el item
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Verificar si está caducado
    public function isExpired()
    {
        return $this->expiration_date && $this->expiration_date < now();
    }

    // Verificar si está próximo a caducar
    public function isExpiringSoon($days = 30)
    {
        return $this->expiration_date && 
               $this->expiration_date <= now()->addDays($days) &&
               !$this->isExpired();
    }

    // Días hasta caducidad
    public function daysUntilExpiration()
    {
        if (!$this->expiration_date) return null;
        return now()->diffInDays($this->expiration_date, false);
    }
}