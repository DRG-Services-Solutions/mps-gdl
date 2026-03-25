<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospital extends Model
{
    protected $fillable = [
        'name',
        'rfc',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    // Relaciones pivote
    public function configs()
    {
        return $this->hasMany(HospitalModalityConfig::class);
    }

    public function modalities()
    {
        return $this->belongsToMany(Modality::class, 'hospital_modality_configs')
                            ->withPivot('legal_entity_id')
                            ->withTimestamps();    
    }

    /**
     * RELACIONES
     */

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function legalEntity()
    {
        return $this->belongsTo(LegalEntity::class);
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

    // Agregar esta relación al modelo Hospital

    /**
     * Cirugías programadas en este hospital (a través de configs)
     */
    public function surgeries()
    {
        return $this->hasManyThrough(
            ScheduledSurgery::class,
            HospitalModalityConfig::class,
            'hospital_id',              // FK en hospital_modality_configs
            'hospital_modality_config_id', // FK en scheduled_surgeries
            'id',                       // PK en hospitals
            'id'                        // PK en hospital_modality_configs
        );
    }

}
