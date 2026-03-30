<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InstrumentKit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'serial_number',
        'template_id',
        'status',
        'expected_count',
        'notes',
    ];

    // ═══════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════

    /**
     * Plantilla en la que se basa este kit (opcional)
     */
    public function template()
    {
        return $this->belongsTo(SurgicalKitTemplate::class, 'template_id');
    }

    /**
     * Instrumentos que contiene este kit
     */
    public function instruments()
    {
        return $this->hasMany(Instrument::class, 'kit_id');
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeAvailable(Builder $query)
    {
        return $query->where('status', 'available');
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('serial_number', 'like', "%{$search}%");
        });
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    /**
     * Cantidad actual de instrumentos en el kit
     */
    public function getCurrentCountAttribute(): int
    {
        return $this->relationLoaded('instruments')
            ? $this->instruments->count()
            : $this->instruments()->count();
    }

    /**
     * ¿Está completo? (tiene todas las piezas esperadas)
     */
    public function getIsCompleteAttribute(): bool
    {
        return $this->current_count >= $this->expected_count && $this->expected_count > 0;
    }

    /**
     * Piezas faltantes
     */
    public function getMissingCountAttribute(): int
    {
        return max(0, $this->expected_count - $this->current_count);
    }

    /**
     * Porcentaje de completitud
     */
    public function getCompletenessAttribute(): float
    {
        if ($this->expected_count <= 0) return 0;
        return round(($this->current_count / $this->expected_count) * 100, 1);
    }

    // ═══════════════════════════════════════════
    // MÉTODOS DE NEGOCIO
    // ═══════════════════════════════════════════

    /**
     * Asignar un instrumento a este kit
     */
    public function assignInstrument(Instrument $instrument): void
    {
        if ($instrument->kit_id && $instrument->kit_id !== $this->id) {
            throw new \RuntimeException("El instrumento {$instrument->serial_number} ya pertenece a otro kit.");
        }

        $instrument->update([
            'kit_id' => $this->id,
            'status' => 'in_kit',
        ]);
    }

    /**
     * Remover un instrumento del kit
     */
    public function removeInstrument(Instrument $instrument): void
    {
        if ($instrument->kit_id !== $this->id) {
            throw new \RuntimeException("El instrumento no pertenece a este kit.");
        }

        $instrument->update([
            'kit_id' => null,
            'status' => 'available',
        ]);
    }

    /**
     * Recalcular estado del kit según su contenido
     */
    public function refreshStatus(): void
    {
        if ($this->status === 'retired') return;

        $newStatus = $this->is_complete ? 'available' : 'incomplete';
        $this->update(['status' => $newStatus]);
    }

    /**
     * Generar código automático: KIT-001, KIT-002, etc.
     */
    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->value('code');

        if ($last && preg_match('/KIT-(\d+)/', $last, $matches)) {
            $next = intval($matches[1]) + 1;
        } else {
            $next = 1;
        }

        return 'KIT-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}