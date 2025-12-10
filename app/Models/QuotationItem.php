<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'product_unit_id',
        'product_id',
        'source_legal_entity_id',
        'source_sub_warehouse_id',
        'billing_mode',
        'rental_price',
        'sale_price',
        'quantity_sent',
        'quantity_returned',
        'status',
        'sent_at',
        'returned_at',
        'quantity',
    ];

    protected $casts = [
        'rental_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'quantity_sent' => 'integer',
        'quantity_returned' => 'integer',
        'sent_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════════════════════

    /**
     * Cotización a la que pertenece
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Producto específico asignado
     */
    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    /**
     * Producto genérico
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Razón social de origen
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

    /**
     * Venta generada (si existe)
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'id', 'quotation_item_id');
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    /**
     * Items en modalidad renta
     */
    public function scopeRental($query)
    {
        return $query->where('billing_mode', 'rental');
    }

    /**
     * Items en modalidad consignación
     */
    public function scopeConsignment($query)
    {
        return $query->where('billing_mode', 'consignment');
    }

    /**
     * Items enviados
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Items retornados
     */
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    /**
     * Items usados (no regresaron)
     */
    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    /**
     * Items facturados
     */
    public function scopeInvoiced($query)
    {
        return $query->where('status', 'invoiced');
    }

    /**
     * Items que deben facturarse
     */
    public function scopeShouldBeInvoiced($query)
    {
        return $query->where(function ($q) {
            // RENTA: siempre se factura
            $q->where('billing_mode', 'rental')
              ->whereIn('status', ['sent', 'returned']);
        })->orWhere(function ($q) {
            // CONSIGNACIÓN: solo si no regresó
            $q->where('billing_mode', 'consignment')
              ->where('status', 'sent')
              ->where('quantity_returned', 0);
        });
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS DE NEGOCIO
    // ═══════════════════════════════════════════════════════════

    /**
     * Marcar como retornado
     */
    public function markAsReturned(): void
    {
        $this->update([
            'quantity_returned' => 1,
            'status' => 'returned',
            'returned_at' => now(),
        ]);

        // Devolver producto a inventario
        $this->productUnit->update(['status' => 'available']);

        // Crear movimiento de retorno
        InventoryMovement::create([
            'type' => 'surgery_return',
            'product_id' => $this->product_id,
            'quantity' => 1,
            'legal_entity_id' => $this->source_legal_entity_id,
            'sub_warehouse_id' => $this->source_sub_warehouse_id,
            'reference_type' => Quotation::class,
            'reference_id' => $this->quotation_id,
            'user_id' => auth()->id(),
            'notes' => "Retorno de cirugía ({$this->billing_mode})",
        ]);
    }

    /**
     * Verificar si debe facturarse
     */
    public function shouldBeInvoiced(): bool
    {
        if ($this->billing_mode === 'rental') {
            // RENTA: siempre se factura
            return true;
        }

        if ($this->billing_mode === 'consignment') {
            // CONSIGNACIÓN: solo si NO regresó
            return $this->quantity_returned === 0;
        }

        return false;
    }

    /**
     * Obtener precio a facturar
     */
    public function getInvoicePrice(): float
    {
        if ($this->billing_mode === 'rental') {
            return $this->rental_price;
        }

        if ($this->billing_mode === 'consignment' && $this->quantity_returned === 0) {
            return $this->sale_price;
        }

        return 0;
    }

    /**
     * Obtener tipo de venta
     */
    public function getSaleType(): ?string
    {
        if ($this->billing_mode === 'rental') {
            return 'rental';
        }

        if ($this->billing_mode === 'consignment' && $this->quantity_returned === 0) {
            return 'consignment_used';
        }

        return null;
    }

    /**
     * Obtener badge de modalidad
     */
    public function getBillingModeBadge(): string
    {
        $badges = [
            'rental' => '<span class="badge badge-info">RENTA</span>',
            'consignment' => '<span class="badge badge-success">CONSIGNACIÓN</span>',
        ];

        return $badges[$this->billing_mode] ?? $this->billing_mode;
    }

    /**
     * Obtener badge de estado
     */
    public function getStatusBadge(): string
    {
        $badges = [
            'pending' => '<span class="badge badge-secondary">Pendiente</span>',
            'sent' => '<span class="badge badge-warning">Enviado</span>',
            'returned' => '<span class="badge badge-success">Retornado</span>',
            'used' => '<span class="badge badge-danger">Usado</span>',
            'invoiced' => '<span class="badge badge-primary">Facturado</span>',
        ];

        return $badges[$this->status] ?? $this->status;
    }
}