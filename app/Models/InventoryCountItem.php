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
        'product_unit_id',
        'product_id',
        'product_code',
        'product_name',
        'expected_epc',
        'expected_serial',
        'expected_batch',
        'scanned_epc',
        'scanned_serial',
        'scanned_barcode',
        'scanned_batch',
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
        'found_location_id',
        'notes',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'last_recount_at' => 'datetime',
        'discrepancy_justified' => 'boolean',
    ];

    // ==================== CONSTANTES ====================

    const STATUS_PENDING = 'pending';
    const STATUS_FOUND = 'found';           // EPC/Serial encontrado y coincide
    const STATUS_MATCHED = 'matched';       // Cantidad coincide (para conteo manual)
    const STATUS_SURPLUS = 'surplus';       // Encontrado pero no esperado
    const STATUS_MISSING = 'missing';       // Esperado pero no encontrado
    const STATUS_WRONG_LOCATION = 'wrong_location';
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

    public function foundLocation(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class, 'found_location_id');
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

    public function scopeFound($query)
    {
        return $query->whereIn('status', [self::STATUS_FOUND, self::STATUS_MATCHED]);
    }

    public function scopeWithDiscrepancy($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SURPLUS,
            self::STATUS_MISSING,
            self::STATUS_WRONG_LOCATION,
            self::STATUS_DAMAGED,
            self::STATUS_EXPIRED,
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

    public function scopeByEpc($query, $epc)
    {
        return $query->where('expected_epc', $epc);
    }

    // ==================== ATRIBUTOS ====================

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_FOUND => 'Encontrado',
            self::STATUS_MATCHED => 'Coincide',
            self::STATUS_SURPLUS => 'Sobrante',
            self::STATUS_MISSING => 'Faltante',
            self::STATUS_WRONG_LOCATION => 'Ubicación Incorrecta',
            self::STATUS_DAMAGED => 'Dañado',
            self::STATUS_EXPIRED => 'Caducado',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_PENDING => 'gray',
            self::STATUS_FOUND => 'green',
            self::STATUS_MATCHED => 'green',
            self::STATUS_SURPLUS => 'blue',
            self::STATUS_MISSING => 'red',
            self::STATUS_WRONG_LOCATION => 'yellow',
            self::STATUS_DAMAGED => 'orange',
            self::STATUS_EXPIRED => 'orange',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    public function getStatusIconAttribute(): string
    {
        $icons = [
            self::STATUS_PENDING => 'clock',
            self::STATUS_FOUND => 'check-circle',
            self::STATUS_MATCHED => 'check-circle',
            self::STATUS_SURPLUS => 'plus-circle',
            self::STATUS_MISSING => 'x-circle',
            self::STATUS_WRONG_LOCATION => 'exclamation-circle',
            self::STATUS_DAMAGED => 'exclamation-triangle',
            self::STATUS_EXPIRED => 'exclamation-triangle',
        ];

        return $icons[$this->status] ?? 'question-mark-circle';
    }

    public function getHasDiscrepancyAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_SURPLUS,
            self::STATUS_MISSING,
            self::STATUS_WRONG_LOCATION,
            self::STATUS_DAMAGED,
            self::STATUS_EXPIRED,
        ]);
    }

    public function getIdentifierAttribute(): string
    {
        return $this->expected_epc ?? $this->expected_serial ?? $this->product_code;
    }

    // ==================== MÉTODOS ====================

    /**
     * Marcar como encontrado (para RFID/Serial)
     */
    public function markAsFound(string $scannedCode, string $method = 'rfid', ?int $userId = null): void
    {
        $this->scanned_epc = ($method === 'rfid' || $method === 'epc') ? $scannedCode : null;
        $this->scanned_serial = ($method === 'serial') ? $scannedCode : null;
        $this->scanned_barcode = ($method === 'barcode') ? $scannedCode : null;
        $this->counted_quantity = 1;
        $this->difference = 0;
        $this->status = self::STATUS_FOUND;
        $this->scanned_at = now();
        $this->scanned_by = $userId ?? auth()->id();
        $this->scan_method = $method;
        $this->save();

        $this->inventoryCount->calculateSummary();
    }

    /**
     * Registrar conteo manual de cantidad
     */
    public function recordCount(int $quantity, ?int $userId = null, ?string $method = null): void
    {
        $this->counted_quantity = $quantity;
        $this->difference = $quantity - $this->expected_quantity;
        $this->scanned_at = now();
        $this->scanned_by = $userId ?? auth()->id();
        $this->scan_method = $method ?? 'manual';

        // Determinar estado
        if ($this->difference === 0) {
            $this->status = self::STATUS_MATCHED;
        } elseif ($this->difference > 0) {
            $this->status = self::STATUS_SURPLUS;
        } else {
            $this->status = self::STATUS_MISSING;
        }

        $this->save();

        $this->inventoryCount->calculateSummary();
    }

    /**
     * Marcar como faltante (no encontrado)
     */
    public function markAsMissing(?int $userId = null): void
    {
        $this->counted_quantity = 0;
        $this->difference = -$this->expected_quantity;
        $this->status = self::STATUS_MISSING;
        $this->scanned_at = now();
        $this->scanned_by = $userId ?? auth()->id();
        $this->save();

        $this->inventoryCount->calculateSummary();
    }

    /**
     * Marcar como dañado
     */
    public function markAsDamaged(string $description, ?int $userId = null): void
    {
        $this->counted_quantity = 1;
        $this->difference = 0;
        $this->status = self::STATUS_DAMAGED;
        $this->notes = $description;
        $this->scanned_at = now();
        $this->scanned_by = $userId ?? auth()->id();
        $this->save();

        $this->inventoryCount->calculateSummary();
    }

    /**
     * Marcar como caducado
     */
    public function markAsExpired(?int $userId = null): void
    {
        $this->counted_quantity = 1;
        $this->difference = 0;
        $this->status = self::STATUS_EXPIRED;
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
        $this->difference = -$this->expected_quantity;
        $this->status = self::STATUS_PENDING;
        $this->recount_number++;
        $this->last_recount_at = now();
        $this->scanned_epc = null;
        $this->scanned_serial = null;
        $this->scanned_barcode = null;
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
            self::STATUS_MISSING => 'shortage',
            self::STATUS_DAMAGED => 'damaged',
            self::STATUS_EXPIRED => 'expired',
            default => 'correction',
        };

        // Obtener la primera legal entity del inventario
        $legalEntityId = $this->inventoryCount->legalEntities->first()?->id;

        return InventoryAdjustment::create([
            'inventory_count_id' => $this->inventory_count_id,
            'inventory_count_item_id' => $this->id,
            'product_id' => $this->product_id,
            'product_unit_id' => $this->product_unit_id,
            'adjustment_type' => $type,
            'quantity' => $this->difference,
            'legal_entity_id' => $legalEntityId,
            'sub_warehouse_id' => $this->inventoryCount->sub_warehouse_id,
            'storage_location_id' => $this->inventoryCount->storage_location_id,
            'reason' => $this->discrepancy_reason ?? 'Discrepancia detectada en toma de inventario',
            'created_by' => auth()->id(),
        ]);
    }
}
