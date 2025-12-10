<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospital extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * RELACIONES
     */

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Ventas de este hospital
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Doctores que tienen este hospital como principal
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'primary_hospital_id');
    }

    /**
     * Scope para hospitales activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por nombre o código
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        });
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS DE NEGOCIO
    // ═══════════════════════════════════════════════════════════

    /**
     * Obtener total de cotizaciones
     */
    public function getTotalQuotations(): int
    {
        return $this->quotations()->count();
    }

    /**
     * Obtener total de ventas
     */
    public function getTotalSales(): float
    {
        return $this->sales()->sum('sale_price');
    }

    /**
     * Obtener cotizaciones activas (no facturadas)
     */
    public function getActiveQuotations()
    {
        return $this->quotations()
            ->whereIn('status', ['draft', 'sent', 'in_surgery', 'completed'])
            ->get();
    }

}
