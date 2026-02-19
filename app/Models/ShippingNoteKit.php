<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ShippingNoteKit extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_note_id',
        'surgical_kit_id',
        'rental_price',
        'exclude_from_invoice',
        'status',
        'notes',
    ];

    protected $casts = [
        'rental_price' => 'decimal:2',
        'exclude_from_invoice' => 'boolean',
    ];

    // ═══════════════════════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════════════════════

    /**
     * Remisión a la que pertenece
     */
    public function shippingNote(): BelongsTo
    {
        return $this->belongsTo(ShippingNote::class);
    }

    /**
     * Kit quirúrgico de instrumental
     */
    public function surgicalKit(): BelongsTo
    {
        return $this->belongsTo(SurgicalKit::class);
    }

    /**
     * Items de la remisión que vienen de este kit
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShippingNoteItem::class);
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    public function scopeAssigned(Builder $query): Builder
    {
        return $query->where('status', 'assigned');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    public function scopeInSurgery(Builder $query): Builder
    {
        return $query->where('status', 'in_surgery');
    }

    public function scopeReturned(Builder $query): Builder
    {
        return $query->where('status', 'returned');
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->where('status', 'incomplete');
    }

    public function scopeBillable(Builder $query): Builder
    {
        return $query->where('exclude_from_invoice', false);
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS DE NEGOCIO
    // ═══════════════════════════════════════════════════════════

    /**
     * ¿Todos los instrumentales de este kit regresaron?
     */
    public function isCompleteReturn(): bool
    {
        $totalItems = $this->items()->count();
        $returnedItems = $this->items()->where('status', 'returned')->count();

        return $totalItems > 0 && $totalItems === $returnedItems;
    }

    /**
     * Obtener instrumentales faltantes
     */
    public function getMissingItems()
    {
        return $this->items()
            ->whereIn('status', ['sent', 'in_surgery', 'used'])
            ->with('product')
            ->get();
    }

    /**
     * Total de items en el kit
     */
    public function getTotalItems(): int
    {
        return $this->items()->count();
    }

    /**
     * Items retornados
     */
    public function getReturnedItems(): int
    {
        return $this->items()->where('status', 'returned')->count();
    }

    /**
     * Marcar resultado del retorno
     */
    public function evaluateReturn(): void
    {
        if ($this->isCompleteReturn()) {
            $this->update(['status' => 'returned']);
        } else {
            $this->update(['status' => 'incomplete']);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // LABELS
    // ═══════════════════════════════════════════════════════════

    public static function getStatusLabels(): array
    {
        return [
            'assigned' => 'Asignado',
            'sent' => 'Enviado',
            'in_surgery' => 'En Cirugía',
            'returned' => 'Retornado Completo',
            'incomplete' => 'Retornado Incompleto',
            'reviewed' => 'Revisado',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }
}