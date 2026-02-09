<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCountItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_count_id',
        'product_id',
        'product_code',
        'product_name',
        'product_unit_id',
        'epc',
        'serial_number',
        'barcode_scanned',
        'batch_number',
        'expected_quantity',
        'counted_quantity',
        'difference',
        'status',
        'scanned_at',
        'scanned_by',
        'scan_method',
        'recount_number',
        'last_recount_at',
        'discrepancy_reason',
        'discrepancy_justified',
        'justified_by',
        'notes',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'last_recount_at' => 'datetime',
        'discrepancy_justified' => 'boolean',
    ];

    // ==================== CONSTANTES ====================

    const STATUS_PENDING = 'pending';
    const STATUS_MATCHED = 'matched';
    const STATUS_SURPLUS = 'surplus';
    const STATUS_SHORTAGE = 'shortage';
    const STATUS_NOT_FOUND = 'not_found';
    const STATUS_UNEXPECTED = 'unexpected';
    const STATUS_DAMAGED = 'damaged';
    const STATUS_EXPIRED = 'expired';

    // ==================== RELACIONES ====================

    public function inventoryCount(): BelongsTo
    {
        return $this->belongsTo(InventoryCount::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function justifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'justified_by');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class, 'inventory_count_item_id');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeMatched($query)
    {
        return $query->where('status', self::STATUS_MATCHED);
    }

    public function scopeWithDiscrepancy($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SURPLUS,
            self::STATUS_SHORTAGE,
            self::STATUS_NOT_FOUND,
            self::STATUS_UNEXPECTED,
        ]);
    }

    public function scopeNotJustified($query)
    {
        return $query->withDiscrepancy()->where('discrepancy_justified', false);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // ==================== ATRIBUTOS ====================

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_MATCHED => 'Coincide',
            self::STATUS_SURPLUS => 'Sobrante',
            self::STATUS_SHORTAGE => 'Faltante',
            self::STATUS_NOT_FOUND => 'No Encontrado',
            self::STATUS_UNEXPECTED => 'No Esperado',
            self::STATUS_DAMAGED => 'Dañado',
            self::STATUS_EXPIRED => 'Caducado',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_PENDING => 'gray',
            self::STATUS_MATCHED => 'green',
            self::STATUS_SURPLUS => 'blue',
            self::STATUS_SHORTAGE => 'red',
            self::STATUS_NOT_FOUND => 'red',
            self::STATUS_UNEXPECTED => 'yellow',
            self::STATUS_DAMAGED => 'orange',
            self::STATUS_EXPIRED => 'orange',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    public function getStatusIconAttribute(): string
    {
        $icons = [
            self::STATUS_PENDING => 'clock',
            self::STATUS_MATCHED => 'check-circle',
            self::STATUS_SURPLUS => 'plus-circle',
            self::STATUS_SHORTAGE => 'minus-circle',
            self::STATUS_NOT_FOUND => 'x-circle',
            self::STATUS_UNEXPECTED => 'question-mark-circle',
            self::STATUS_DAMAGED => 'exclamation-circle',
            self::STATUS_EXPIRED => 'exclamation-circle',
        ];

        return $icons[$this->status] ?? 'question-mark-circle';
    }

    public function getHasDiscrepancyAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_SURPLUS,
            self::STATUS_SHORTAGE,
            self::STATUS_NOT_FOUND,
            self::STATUS_UNEXPECTED,
        ]);
    }

    public function getAbsoluteDifferenceAttribute(): int
    {
        return abs($this->difference);
    }

    // ==================== MÉTODOS ====================

    /**
     * Registrar conteo
     */
    public function recordCount(int $quantity, ?int $userId = null, ?string $method = null): void
    {
        $this->counted_quantity = $quantity;
        $this->difference = $quantity - $this->expected_quantity;
        $this->scanned_at = now();
        $this->scanned_by = $userId ?? auth()->id();
        $this->scan_method = $method;

        // Determinar estado
        if ($this->difference === 0) {
            $this->status = self::STATUS_MATCHED;
        } elseif ($this->difference > 0) {
            $this->status = self::STATUS_SURPLUS;
        } else {
            $this->status = self::STATUS_SHORTAGE;
        }

        $this->save();

        // Actualizar resumen del inventario padre
        $this->inventoryCount->calculateSummary();
    }

    /**
     * Registrar escaneo individual (RFID/Barcode)
     */
    public function recordScan(string $scannedCode, string $type = 'barcode', ?int $userId = null): void
    {
        if ($type === 'rfid' || $type === 'epc') {
            $this->epc = $scannedCode;
        } else {
            $this->barcode_scanned = $scannedCode;
        }

        $this->counted_quantity++;
        $this->difference = $this->counted_quantity - $this->expected_quantity;
        $this->scanned_at = now();
        $this->scanned_by = $userId ?? auth()->id();
        $this->scan_method = $type;

        // Actualizar estado
        if ($this->difference === 0) {
            $this->status = self::STATUS_MATCHED;
        } elseif ($this->difference > 0) {
            $this->status = self::STATUS_SURPLUS;
        } else {
            $this->status = self::STATUS_SHORTAGE;
        }

        $this->save();
    }

    /**
     * Marcar como no encontrado
     */
    public function markAsNotFound(?int $userId = null): void
    {
        $this->counted_quantity = 0;
        $this->difference = 0 - $this->expected_quantity;
        $this->status = self::STATUS_NOT_FOUND;
        $this->scanned_at = now();
        $this->scanned_by = $userId ?? auth()->id();
        $this->save();

        $this->inventoryCount->calculateSummary();
    }

    /**
     * Recontar item
     */
    public function recount(): void
    {
        $this->counted_quantity = 0;
        $this->difference = 0 - $this->expected_quantity;
        $this->status = self::STATUS_PENDING;
        $this->recount_number++;
        $this->last_recount_at = now();
        $this->epc = null;
        $this->barcode_scanned = null;
        $this->save();
    }

    /**
     * Justificar discrepancia
     */
    public function justifyDiscrepancy(string $reason, ?int $userId = null): void
    {
        $this->discrepancy_reason = $reason;
        $this->discrepancy_justified = true;
        $this->justified_by = $userId ?? auth()->id();
        $this->save();
    }

    /**
     * Verificar si requiere ajuste
     */
    public function requiresAdjustment(): bool
    {
        return $this->has_discrepancy && !$this->adjustments()->where('status', 'approved')->exists();
    }

    /**
     * Crear ajuste a partir de esta discrepancia
     */
    public function createAdjustment(): ?InventoryAdjustment
    {
        if (!$this->has_discrepancy) {
            return null;
        }

        $type = match ($this->status) {
            self::STATUS_SURPLUS => 'surplus',
            self::STATUS_SHORTAGE => 'shortage',
            self::STATUS_NOT_FOUND => 'lost',
            default => 'correction',
        };

        return InventoryAdjustment::create([
            'inventory_count_id' => $this->inventory_count_id,
            'inventory_count_item_id' => $this->id,
            'product_id' => $this->product_id,
            'product_unit_id' => $this->product_unit_id,
            'adjustment_type' => $type,
            'quantity' => $this->difference,
            'legal_entity_id' => $this->inventoryCount->legal_entity_id,
            'sub_warehouse_id' => $this->inventoryCount->sub_warehouse_id,
            'storage_location_id' => $this->inventoryCount->storage_location_id,
            'reason' => $this->discrepancy_reason ?? 'Discrepancia detectada en toma de inventario',
            'created_by' => auth()->id(),
        ]);
    }
}
