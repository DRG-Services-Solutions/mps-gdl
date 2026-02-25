<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ShippingNoteRentalConcept extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_note_id',
        'concept',
        'quantity',
        'unit_price',
        'total_price',
        'exclude_from_invoice',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'exclude_from_invoice' => 'boolean',
    ];

    // ═══════════════════════════════════════════════════════════
    // BOOT - Auto-calcular total
    // ═══════════════════════════════════════════════════════════

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($concept) {
            $concept->total_price = $concept->quantity * $concept->unit_price;
        });
    }

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

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    public function scopeBillable(Builder $query): Builder
    {
        return $query->where('exclude_from_invoice', false);
    }

    public function scopeCourtesy(Builder $query): Builder
    {
        return $query->where('exclude_from_invoice', true);
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS
    // ═══════════════════════════════════════════════════════════

    /**
     * Recalcular total
     */
    public function recalculateTotal(): void
    {
        $this->update([
            'total_price' => $this->quantity * $this->unit_price,
        ]);
    }

    /**
     * ¿Debe facturarse?
     */
    public function shouldBeInvoiced(): bool
    {
        return !$this->exclude_from_invoice && $this->total_price > 0;
    }
}