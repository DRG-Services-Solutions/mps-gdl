<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'full_name',
        'specialty',
        'license_number',
        'phone',
        'mobile',
        'email',
        'primary_hospital_id',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ═══════════════════════════════════════════════════════════
    // BOOT - Generar nombre completo automáticamente
    // ═══════════════════════════════════════════════════════════

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($doctor) {
            if (empty($doctor->full_name)) {
                $doctor->full_name = trim("{$doctor->first_name} {$doctor->last_name}");
            }
        });
    }

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
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('specialty', 'like', "%{$search}%")
              ->orWhere('license_number', 'like', "%{$search}%");
        });
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
     * Obtener nombre con especialidad
     */
    public function getNameWithSpecialtyAttribute(): string
    {
        return $this->specialty 
            ? "Dr(a). {$this->full_name} - {$this->specialty}" 
            : "Dr(a). {$this->full_name}";
    }
}