<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'pre_assembled_package_id',
        'product_id',
        'product_unit_id',
        'quantity',
        'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
        'quantity' => 'integer',
    ];

    /**
     * Paquete al que pertenece
     */
    public function preAssembledPackage()
    {
        return $this->belongsTo(PreAssembledPackage::class, 'pre_assembled_package_id');
    }

    /**
     * Producto (tipo)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Unidad física específica (con EPC)
     */
    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    /**
     * Verificar si está vencido
     */
    public function isExpired()
    {
        if (!$this->productUnit) {
            return false;
        }

        return $this->productUnit->isExpired();
    }

    /**
     * Verificar si está próximo a vencer
     */
    public function isExpiringSoon($days = 30)
    {
        if (!$this->productUnit) {
            return false;
        }

        return $this->productUnit->isExpiringSoon($days);
    }

    /**
     * Eventos del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Al crear, establecer fecha de agregado
        static::creating(function ($content) {
            if (!$content->added_at) {
                $content->added_at = now();
            }
        });
    }
}