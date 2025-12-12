<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgicalKit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'surgery_type',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Items del prearmado
     */
    public function items(): HasMany
    {
        return $this->hasMany(SurgicalKitItem::class);
    }

    /**
     * Usuario que creó el prearmado
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope para prearmados activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar por tipo de cirugía
     */
    public function scopeBySurgeryType($query, $surgeryType)
    {
        return $query->where('surgery_type', $surgeryType);
    }

    /**
     * Obtener total de productos en el kit
     */
    public function getTotalProductsAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Obtener cantidad total de piezas
     */
    public function getTotalPiecesAttribute(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Generar código automático
     */
    public static function generateCode(): string
    {
        $lastKit = self::withTrashed()->latest('id')->first();
        $nextNumber = $lastKit ? $lastKit->id + 1 : 1;
        
        return 'KIT-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kit) {
            if (!$kit->code) {
                $kit->code = self::generateCode();
            }
        });
    }
}