<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SurgicalKit;

use Illuminate\Support\Facades\DB;

class Quotation extends Model
{
    protected $fillable = [
        'quotation_number',
        'hospital_id',
        'doctor_id',
        'surgery_type',
        'surgical_kit_id',
        'surgery_date',
        'billing_legal_entity_id',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'surgery_date' => 'date',
    ];

    // ═══════════════════════════════════════════════════════════
    // BOOT - Generar número automático
    // ═══════════════════════════════════════════════════════════

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $quotation->quotation_number = self::generateQuotationNumber();
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════════════════════

    /**
     * Hospital de la cotización
     */
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Doctor de la cotización
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Razón social que facturará
     */
    public function billingLegalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class, 'billing_legal_entity_id');
    }

    /**
     * Productos de la cotización
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    /**
     * Ventas generadas de esta cotización
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Usuario que creó la cotización
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    /**
     * Kit quirúrgico asociado a la cotización
     */

    public function surgicalKit(): BelongsTo
{
    return $this->belongsTo(SurgicalKit::class);
}

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    /**
     * Cotizaciones en borrador
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Cotizaciones en cirugía
     */
    public function scopeInSurgery($query)
    {
        return $query->where('status', 'in_surgery');
    }

    /**
     * Cotizaciones completadas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Cotizaciones facturadas
     */
    public function scopeInvoiced($query)
    {
        return $query->where('status', 'invoiced');
    }

    /**
     * Filtrar por hospital
     */
    public function scopeByHospital($query, $hospitalId)
    {
        return $query->where('hospital_id', $hospitalId);
    }

    /**
     * Filtrar por razón social
     */
    public function scopeByLegalEntity($query, $legalEntityId)
    {
        return $query->where('billing_legal_entity_id', $legalEntityId);
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS DE NEGOCIO
    // ═══════════════════════════════════════════════════════════

    /**
     * Generar número de cotización único
     * Formato: COT-2024-000001
     */
    public static function generateQuotationNumber(): string
    {
        $year = date('Y');
        
        $lastQuotation = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastQuotation 
            ? (int)substr($lastQuotation->quotation_number, -6) + 1 
            : 1;
        
        return 'COT-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Enviar productos a cirugía
     */
    public function sendToSurgery(): void
    {
        DB::transaction(function () {
            foreach ($this->items as $item) {
                $item->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'quantity_sent' => 1,
                ]);

                // Actualizar status del producto
                $item->productUnit->update(['status' => 'in_surgery']);

                // Crear movimiento de inventario
                InventoryMovement::create([
                    'type' => 'surgery_out',
                    'product_id' => $item->product_id,
                    'quantity' => 1,
                    'legal_entity_id' => $item->source_legal_entity_id,
                    'sub_warehouse_id' => $item->source_sub_warehouse_id,
                    'reference_type' => self::class,
                    'reference_id' => $this->id,
                    'user_id' => auth()->id(),
                    'notes' => "Envío a cirugía ({$item->billing_mode})",
                ]);
            }

            $this->update(['status' => 'in_surgery']);
        });
    }

    /**
     * Generar ventas automáticamente según retorno
     */
    public function generateSales(): int
    {
        $salesCount = 0;

        DB::transaction(function () use (&$salesCount) {
            $items = $this->items()->where('status', '!=', 'invoiced')->get();

            foreach ($items as $item) {
                $shouldCreateSale = false;
                $saleType = null;
                $salePrice = 0;

                // LÓGICA DE VENTAS
                if ($item->billing_mode === 'rental') {
                    // RENTA: SIEMPRE se crea venta
                    $shouldCreateSale = true;
                    $saleType = 'rental';
                    $salePrice = $item->rental_price;
                    $item->update(['status' => 'invoiced']);

                } elseif ($item->billing_mode === 'consignment') {
                    // CONSIGNACIÓN: SOLO si NO regresó
                    if ($item->quantity_returned === 0) {
                        $shouldCreateSale = true;
                        $saleType = 'consignment_used';
                        $salePrice = $item->sale_price;
                        
                        $item->productUnit->update(['status' => 'sold']);
                        $item->update(['status' => 'invoiced']);
                    } else {
                        $item->update(['status' => 'returned']);
                    }
                }

                // CREAR VENTA
                if ($shouldCreateSale) {
                    Sale::create([
                        'sale_number' => Sale::generateSaleNumber(),
                        'quotation_id' => $this->id,
                        'quotation_item_id' => $item->id,
                        'billing_legal_entity_id' => $this->billing_legal_entity_id,
                        'source_legal_entity_id' => $item->source_legal_entity_id,
                        'source_sub_warehouse_id' => $item->source_sub_warehouse_id,
                        'product_unit_id' => $item->product_unit_id,
                        'product_id' => $item->product_id,
                        'quantity' => 1,
                        'hospital_id' => $this->hospital_id,
                        'sale_type' => $saleType,
                        'cost_price' => $item->productUnit->acquisition_cost,
                        'sale_price' => $salePrice,
                        'sale_date' => now(),
                        'created_by' => auth()->id(),
                    ]);

                    // Movimiento de venta
                    InventoryMovement::create([
                        'type' => 'sale',
                        'product_id' => $item->product_id,
                        'quantity' => 1,
                        'legal_entity_id' => $item->source_legal_entity_id,
                        'sub_warehouse_id' => $item->source_sub_warehouse_id,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'user_id' => auth()->id(),
                        'notes' => "Venta {$saleType}",
                    ]);

                    $salesCount++;
                }
            }

            $this->update(['status' => 'invoiced']);
        });

        return $salesCount;
    }

    /**
     * Obtener total de productos
     */
    public function getTotalItems(): int
    {
        return $this->items()->count();
    }

    /**
     * Obtener productos enviados
     */
    public function getSentItems(): int
    {
        return $this->items()->where('status', 'sent')->count();
    }

    /**
     * Obtener productos retornados
     */
    public function getReturnedItems(): int
    {
        return $this->items()->where('quantity_returned', '>', 0)->count();
    }

    /**
     * Obtener productos faltantes (usados)
     */
    public function getMissingItems(): int
    {
        return $this->items()
            ->where('status', 'sent')
            ->where('quantity_returned', 0)
            ->count();
    }
}