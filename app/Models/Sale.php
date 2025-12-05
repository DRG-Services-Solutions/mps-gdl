<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'sale_number',
        'quotation_id',
        'quotation_item_id',
        'billing_legal_entity_id',
        'source_legal_entity_id',
        'source_sub_warehouse_id',
        'product_unit_id',
        'product_id',
        'quantity',
        'hospital_id',
        'sale_type',
        'cost_price',
        'sale_price',
        'sale_date',
        'invoice_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'margin' => 'decimal:2',
        'sale_date' => 'date',
    ];

    protected $appends = [
        'margin', // Se calcula automáticamente
    ];

    // ═══════════════════════════════════════════════════════════
    // BOOT - Generar número automático
    // ═══════════════════════════════════════════════════════════

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->sale_number)) {
                $sale->sale_number = self::generateSaleNumber();
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════════════════════

    /**
     * Cotización relacionada
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Item de cotización relacionado
     */
    public function quotationItem(): BelongsTo
    {
        return $this->belongsTo(QuotationItem::class);
    }

    /**
     * Hospital cliente
     */
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Razón social que factura
     */
    public function billingLegalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class, 'billing_legal_entity_id');
    }

    /**
     * Razón social de origen del producto
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
     * Producto específico
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
     * Usuario que creó la venta
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    /**
     * Ventas de tipo renta
     */
    public function scopeRental($query)
    {
        return $query->where('sale_type', 'rental');
    }

    /**
     * Ventas de tipo consignación
     */
    public function scopeConsignmentUsed($query)
    {
        return $query->where('sale_type', 'consignment_used');
    }

    /**
     * Filtrar por hospital
     */
    public function scopeByHospital($query, $hospitalId)
    {
        return $query->where('hospital_id', $hospitalId);
    }

    /**
     * Filtrar por razón social facturadora
     */
    public function scopeByBillingEntity($query, $legalEntityId)
    {
        return $query->where('billing_legal_entity_id', $legalEntityId);
    }

    /**
     * Filtrar por razón social de origen
     */
    public function scopeBySourceEntity($query, $legalEntityId)
    {
        return $query->where('source_legal_entity_id', $legalEntityId);
    }

    /**
     * Filtrar por rango de fechas
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS DE NEGOCIO
    // ═══════════════════════════════════════════════════════════

    /**
     * Generar número de venta único
     * Formato: VEN-2024-000001
     */
    public static function generateSaleNumber(): string
    {
        $year = date('Y');
        
        $lastSale = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastSale 
            ? (int)substr($lastSale->sale_number, -6) + 1 
            : 1;
        
        return 'VEN-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener margen calculado
     */
    public function getMarginAttribute(): float
    {
        return $this->sale_price - $this->cost_price;
    }

    /**
     * Obtener porcentaje de margen
     */
    public function getMarginPercentage(): float
    {
        if ($this->cost_price == 0) {
            return 0;
        }

        return ($this->getMarginAttribute() / $this->cost_price) * 100;
    }

    /**
     * Obtener badge de tipo de venta
     */
    public function getSaleTypeBadge(): string
    {
        $badges = [
            'rental' => '<span class="badge badge-info">RENTA</span>',
            'consignment_used' => '<span class="badge badge-success">CONSIGNACIÓN</span>',
        ];

        return $badges[$this->sale_type] ?? $this->sale_type;
    }

    /**
     * Verificar si origen y facturación son diferentes
     */
    public function isCrossEntity(): bool
    {
        return $this->billing_legal_entity_id !== $this->source_legal_entity_id;
    }
}
$hola = null ;