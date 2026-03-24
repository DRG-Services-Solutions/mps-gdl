<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'phone',
        'is_active',
        
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

 

    // ═══════════════════════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════════════════════

    /**
     * Hospital principal del doctor
     */
    public function primaryHospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'primary_hospital_id');
    }

    /**
     * Cotizaciones de este doctor
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    /**
     * Scope para doctores activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por nombre o especialidad
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%");
        });
    }

    public function getFullNameAttribute()
    {
        $name = $this->first_name;

        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }

        $name .= ' ' . $this->last_name;

        return $name;
    }

    /**
     * Scope para filtrar por especialidad
     */
    public function scopeBySpecialty($query, $specialty)
    {
        return $query->where('specialty', $specialty);
    }

    /**
     * Scope para filtrar por hospital
     */
    public function scopeByHospital($query, $hospitalId)
    {
        return $query->where('primary_hospital_id', $hospitalId);
    }

    // ═══════════════════════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════════════════════

    /**
     * Nombre completo con especialidad para selects de búsqueda
     */
    public function getNameWithSpecialtyAttribute(): string
    {
        $name = 'Dr. ' . $this->first_name;

        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }

        $name .= ' ' . $this->last_name;

        if ($this->specialty) {
            $name .= ' - ' . $this->specialty;
        }

        return $name;
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

   
}