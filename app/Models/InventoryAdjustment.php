<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'adjustment_number',
        'inventory_count_id',
        'inventory_count_item_id',
        'product_id',
        'product_unit_id',
        'adjustment_type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'legal_entity_id',
        'sub_warehouse_id',
        'storage_location_id',
        'reason',
        'reference_document',
        'status',
        'created_by',
        'approved_by',
        'rejected_by',
        'approved_at',
        'rejected_at',
        'applied_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    // ==================== CONSTANTES ====================

    // Tipos de ajuste
    const TYPE_SURPLUS = 'surplus';
    const TYPE_SHORTAGE = 'shortage';
    const TYPE_DAMAGED = 'damaged';
    const TYPE_EXPIRED = 'expired';
    const TYPE_LOST = 'lost';
    const TYPE_FOUND = 'found';
    const TYPE_CORRECTION = 'correction';
    const TYPE_TRANSFER = 'transfer';

    // Estados
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVERSED = 'reversed';

    // ==================== RELACIONES ====================

    public function inventoryCount(): BelongsTo
    {
        return $this->belongsTo(InventoryCount::class);
    }

    public function inventoryCountItem(): BelongsTo
    {
        return $this->belongsTo(InventoryCountItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function subWarehouse(): BelongsTo
    {
        return $this->belongsTo(SubWarehouse::class);
    }

    public function storageLocation(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeByLegalEntity($query, $legalEntityId)
    {
        return $query->where('legal_entity_id', $legalEntityId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('adjustment_type', $type);
    }

    public function scopeFromInventoryCount($query, $inventoryCountId)
    {
        return $query->where('inventory_count_id', $inventoryCountId);
    }

    // ==================== ATRIBUTOS ====================

    public function getTypeLabelAttribute(): string
    {
        $labels = [
            self::TYPE_SURPLUS => 'Sobrante',
            self::TYPE_SHORTAGE => 'Faltante',
            self::TYPE_DAMAGED => 'Dañado',
            self::TYPE_EXPIRED => 'Caducado',
            self::TYPE_LOST => 'Extraviado',
            self::TYPE_FOUND => 'Encontrado',
            self::TYPE_CORRECTION => 'Corrección',
            self::TYPE_TRANSFER => 'Transferencia',
        ];

        return $labels[$this->adjustment_type] ?? $this->adjustment_type;
    }

    public function getTypeColorAttribute(): string
    {
        $colors = [
            self::TYPE_SURPLUS => 'blue',
            self::TYPE_SHORTAGE => 'red',
            self::TYPE_DAMAGED => 'orange',
            self::TYPE_EXPIRED => 'orange',
            self::TYPE_LOST => 'red',
            self::TYPE_FOUND => 'green',
            self::TYPE_CORRECTION => 'purple',
            self::TYPE_TRANSFER => 'indigo',
        ];

        return $colors[$this->adjustment_type] ?? 'gray';
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_REJECTED => 'Rechazado',
            self::STATUS_REVERSED => 'Revertido',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_PENDING => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_REVERSED => 'gray',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    public function getIsPositiveAttribute(): bool
    {
        return $this->quantity > 0;
    }

    public function getFormattedQuantityAttribute(): string
    {
        $prefix = $this->quantity > 0 ? '+' : '';
        return $prefix . $this->quantity;
    }

    // ==================== MÉTODOS ====================

    /**
     * Generar número de ajuste único
     */
    public static function generateAdjustmentNumber(): string
    {
        $date = now()->format('Ymd');
        
        $lastAdjustment = self::where('adjustment_number', 'like', "ADJ-{$date}-%")
            ->orderBy('adjustment_number', 'desc')
            ->first();

        if ($lastAdjustment) {
            $lastNumber = (int) substr($lastAdjustment->adjustment_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "ADJ-{$date}-{$newNumber}";
    }

    /**
     * Aprobar ajuste y aplicarlo al inventario
     */
    public function approve(int $userId): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        \DB::beginTransaction();

        try {
            // Obtener cantidad actual
            $currentStock = ProductUnit::where('product_id', $this->product_id)
                ->where('legal_entity_id', $this->legal_entity_id)
                ->where('status', 'available')
                ->count();

            $this->quantity_before = $currentStock;

            // Aplicar ajuste según tipo
            $this->applyAdjustment();

            // Calcular cantidad después
            $this->quantity_after = ProductUnit::where('product_id', $this->product_id)
                ->where('legal_entity_id', $this->legal_entity_id)
                ->where('status', 'available')
                ->count();

            // Actualizar estado
            $this->status = self::STATUS_APPROVED;
            $this->approved_by = $userId;
            $this->approved_at = now();
            $this->applied_at = now();
            $this->save();

            \DB::commit();
            return true;

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al aprobar ajuste: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Aplicar el ajuste al inventario
     */
    protected function applyAdjustment(): void
    {
        switch ($this->adjustment_type) {
            case self::TYPE_SURPLUS:
            case self::TYPE_FOUND:
                // Crear unidades nuevas
                $this->createUnits(abs($this->quantity));
                break;

            case self::TYPE_SHORTAGE:
            case self::TYPE_LOST:
                // Marcar unidades como perdidas
                $this->markUnitsAsLost(abs($this->quantity));
                break;

            case self::TYPE_DAMAGED:
                // Marcar unidades como dañadas
                $this->markUnitsAsDamaged(abs($this->quantity));
                break;

            case self::TYPE_EXPIRED:
                // Marcar unidades como caducadas
                $this->markUnitsAsExpired(abs($this->quantity));
                break;

            case self::TYPE_CORRECTION:
                // Corrección puede ser positiva o negativa
                if ($this->quantity > 0) {
                    $this->createUnits($this->quantity);
                } else {
                    $this->markUnitsAsLost(abs($this->quantity));
                }
                break;
        }

        // Registrar movimiento de inventario
        $this->recordInventoryMovement();
    }

    /**
     * Crear unidades de producto
     */
    protected function createUnits(int $quantity): void
    {
        $product = Product::find($this->product_id);

        for ($i = 0; $i < $quantity; $i++) {
            ProductUnit::create([
                'product_id' => $this->product_id,
                'serial_number' => 'ADJ-' . strtoupper(\Str::random(10)),
                'status' => 'available',
                'legal_entity_id' => $this->legal_entity_id,
                'sub_warehouse_id' => $this->sub_warehouse_id,
                'current_location_id' => $this->storage_location_id,
                'acquisition_date' => now(),
                'notes' => "Creado por ajuste de inventario: {$this->adjustment_number}",
                'created_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Marcar unidades como perdidas
     */
    protected function markUnitsAsLost(int $quantity): void
    {
        $units = ProductUnit::where('product_id', $this->product_id)
            ->where('legal_entity_id', $this->legal_entity_id)
            ->where('status', 'available')
            ->limit($quantity)
            ->get();

        foreach ($units as $unit) {
            $unit->update([
                'status' => 'lost',
                'notes' => ($unit->notes ? $unit->notes . ' | ' : '') . 
                          "Marcado como perdido por ajuste: {$this->adjustment_number}",
            ]);
        }
    }

    /**
     * Marcar unidades como dañadas
     */
    protected function markUnitsAsDamaged(int $quantity): void
    {
        $units = ProductUnit::where('product_id', $this->product_id)
            ->where('legal_entity_id', $this->legal_entity_id)
            ->where('status', 'available')
            ->limit($quantity)
            ->get();

        foreach ($units as $unit) {
            $unit->update([
                'status' => 'damaged',
                'damage_description' => $this->reason ?? 'Daño detectado en inventario',
            ]);
        }
    }

    /**
     * Marcar unidades como caducadas
     */
    protected function markUnitsAsExpired(int $quantity): void
    {
        $units = ProductUnit::where('product_id', $this->product_id)
            ->where('legal_entity_id', $this->legal_entity_id)
            ->where('status', 'available')
            ->limit($quantity)
            ->get();

        foreach ($units as $unit) {
            $unit->update(['status' => 'expired']);
        }
    }

    /**
     * Registrar movimiento de inventario
     */
    protected function recordInventoryMovement(): void
    {
        // Si tienes un modelo InventoryMovement, registrar aquí
        // InventoryMovement::create([...]);
    }

    /**
     * Rechazar ajuste
     */
    public function reject(int $userId, string $reason): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_by' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Verificar si puede ser aprobado
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->adjustment_number)) {
                $model->adjustment_number = self::generateAdjustmentNumber();
            }
            if (empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }
}
