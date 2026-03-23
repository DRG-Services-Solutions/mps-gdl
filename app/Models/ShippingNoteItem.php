<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ShippingNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_note_id',
        'shipping_note_package_id',
        'shipping_note_kit_id',
        'item_origin',
        'product_id',
        'product_unit_id',
        'checklist_item_id',
        'checklist_conditional_id',
        'conditional_description',
        'source_legal_entity_id',
        'source_sub_warehouse_id',
        'quantity_required',
        'quantity_sent',
        'quantity_returned',
        'quantity_used',
        'billing_mode',
        'exclude_from_invoice',
        'is_urgency',
        'urgency_reason',
        'unit_price',
        'total_price',
        'status',
        'sent_at',
        'returned_at',
    ];

    protected $casts = [
        'quantity_required' => 'integer',
        'quantity_sent' => 'integer',
        'quantity_returned' => 'integer',
        'quantity_used' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'exclude_from_invoice' => 'boolean',
        'is_urgency' => 'boolean',
        'sent_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════════════════════

    /**
     * Remisión
     */
    public function shippingNote(): BelongsTo
    {
        return $this->belongsTo(ShippingNote::class);
    }

    /**
     * Paquete de la remisión (si viene de un paquete)
     */
    public function shippingNotePackage(): BelongsTo
    {
        return $this->belongsTo(ShippingNotePackage::class);
    }

    /**
     * Kit de la remisión (si viene de un kit)
     */
    public function shippingNoteKit(): BelongsTo
    {
        return $this->belongsTo(ShippingNoteKit::class);
    }

    /**
     * Producto genérico
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Unidad física específica (con EPC)
     */
    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    /**
     * Item del checklist que originó este producto
     */
    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class);
    }

    /**
     * Condicional que aplicó
     */
    public function checklistConditional(): BelongsTo
    {
        return $this->belongsTo(ChecklistConditional::class);
    }

    /**
     * Razón social dueña del producto
     */
    public function sourceLegalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class, 'source_legal_entity_id');
    }

    /**
     * Sub-almacén de origen
     */
    public function sourceSubWarehouse(): BelongsTo
    {
        return $this->belongsTo(SubWarehouse::class, 'source_sub_warehouse_id');
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    // — Por origen
    public function scopeFromPackage(Builder $query): Builder
    {
        return $query->where('item_origin', 'package');
    }

    public function scopeFromKit(Builder $query): Builder
    {
        return $query->where('item_origin', 'kit');
    }

    public function scopeStandalone(Builder $query): Builder
    {
        return $query->where('item_origin', 'standalone');
    }

    public function scopeFromConditional(Builder $query): Builder
    {
        return $query->where('item_origin', 'conditional');
    }

    public function scopeUrgency(Builder $query): Builder
    {
        return $query->where('is_urgency', true);
    }

    // — Por billing
    public function scopeSale(Builder $query): Builder
    {
        return $query->where('billing_mode', 'sale');
    }

    public function scopeRental(Builder $query): Builder
    {
        return $query->where('billing_mode', 'rental');
    }

    public function scopeNoCharge(Builder $query): Builder
    {
        return $query->where('billing_mode', 'no_charge');
    }

    public function scopeBillable(Builder $query): Builder
    {
        return $query->where('exclude_from_invoice', false)
            ->where('billing_mode', '!=', 'no_charge');
    }

    // — Por estado
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
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

    public function scopeUsed(Builder $query): Builder
    {
        return $query->where('status', 'used');
    }

    public function scopeInvoiced(Builder $query): Builder
    {
        return $query->where('status', 'invoiced');
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS DE NEGOCIO: RETORNO
    // ═══════════════════════════════════════════════════════════

    /**
     * Marcar como retornado (regresó de cirugía)
     */
    public function markAsReturned(int $quantityReturned = null): void
    {
        $qty = $quantityReturned ?? $this->quantity_sent;

        $this->update([
            'quantity_returned' => $qty,
            'quantity_used' => max(0, $this->quantity_sent - $qty),
            'status' => 'returned',
            'returned_at' => now(),
        ]);

        // Devolver product_unit a inventario
        if ($this->productUnit) {
            $this->productUnit->update(['status' => 'available']);
        }

        Log::info("Item {$this->id} retornado. Cantidad: {$qty}");
    }

    /**
     * Marcar como usado (no regresó de cirugía)
     */
    public function markAsUsed(): void
    {
        $this->update([
            'quantity_returned' => 0,
            'quantity_used' => $this->quantity_sent,
            'status' => 'used',
            'returned_at' => now(),
        ]);

        // Marcar product_unit como vendido/consumido
        if ($this->productUnit) {
            $this->productUnit->update(['status' => 'sold']);
        }

        // Si era rental, forzar a sale (no regresó = se vende)
        if ($this->billing_mode === 'rental') {
            $this->update(['billing_mode' => 'sale']);
            Log::warning("Item {$this->id} era renta pero no regresó. Cambiado a venta.");
        }

        Log::info("Item {$this->id} marcado como usado (no regresó)");
    }

    /**
     * Recalcular total_price basado en billing_mode y cantidades
     */
    public function recalculateTotal(): void
    {
        $quantity = $this->billing_mode === 'rental'
            ? $this->quantity_sent   // Renta: se cobra por envío
            : $this->quantity_used;  // Venta: se cobra lo usado

        $this->update([
            'total_price' => $this->unit_price * $quantity,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS DE CONSULTA
    // ═══════════════════════════════════════════════════════════

    /**
     * ¿Este item debe facturarse?
     */
    public function shouldBeInvoiced(): bool
    {
        if ($this->exclude_from_invoice) {
            return false;
        }

        if ($this->billing_mode === 'no_charge') {
            return false;
        }

        if ($this->billing_mode === 'rental') {
            return true; // Renta siempre se factura
        }

        if ($this->billing_mode === 'sale') {
            return $this->quantity_used > 0; // Venta solo si se usó
        }

        return false;
    }

    /**
     * ¿Viene de un paquete pre-armado?
     */
    public function isFromPackage(): bool
    {
        return $this->item_origin === 'package';
    }

    /**
     * ¿Viene de un kit quirúrgico?
     */
    public function isFromKit(): bool
    {
        return $this->item_origin === 'kit';
    }

    /**
     * ¿Es un producto individual suelto?
     */
    public function isStandalone(): bool
    {
        return $this->item_origin === 'standalone';
    }

    /**
     * ¿Fue agregado por un condicional?
     */
    public function isFromConditional(): bool
    {
        return $this->item_origin === 'conditional';
    }

    /**
     * ¿Tiene condicional aplicado?
     */
    public function hasConditional(): bool
    {
        return $this->checklist_conditional_id !== null;
    }

    /**
     * ¿Fue agregado como urgencia?
     */
    public function isUrgency(): bool
    {
        return (bool) $this->is_urgency;
    }

    // ═══════════════════════════════════════════════════════════
    // LABELS Y BADGES
    // ═══════════════════════════════════════════════════════════

    public static function getStatusLabels(): array
    {
        return [
            'pending' => 'Pendiente',
            'sent' => 'Enviado',
            'in_surgery' => 'En Cirugía',
            'returned' => 'Retornado',
            'used' => 'Usado',
            'invoiced' => 'Facturado',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public static function getBillingModeLabels(): array
    {
        return [
            'sale' => 'Venta',
            'rental' => 'Renta',
            'no_charge' => 'Sin Cargo',
        ];
    }

    public function getBillingModeLabelAttribute(): string
    {
        return self::getBillingModeLabels()[$this->billing_mode] ?? $this->billing_mode;
    }

    public static function getOriginLabels(): array
    {
        return [
            'package' => 'Paquete',
            'kit' => 'Kit',
            'standalone' => 'Individual',
            'conditional' => 'Condicional',
        ];
    }

    public function getOriginLabelAttribute(): string
    {
        return self::getOriginLabels()[$this->item_origin] ?? $this->item_origin;
    }
}