<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class InventoryCount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'count_number',
        'type',
        'method',
        'status',
        'sub_warehouse_id',
        'storage_location_id',
        'scheduled_at',
        'started_at',
        'completed_at',
        'approved_at',
        'created_by',
        'assigned_to',
        'approved_by',
        'total_expected',
        'total_counted',
        'total_matched',
        'total_discrepancies',
        'accuracy_percentage',
        'notes',
        'cancellation_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'accuracy_percentage' => 'decimal:2',
    ];

    // ==================== CONSTANTES ====================

    // Tipos de inventario
    const TYPE_FULL = 'full';
    const TYPE_PARTIAL = 'partial';
    const TYPE_CYCLIC = 'cyclic';
    const TYPE_SPOT_CHECK = 'spot_check';

    // Métodos de conteo
    const METHOD_RFID_BULK = 'rfid_bulk';
    const METHOD_RFID_HANDHELD = 'rfid_handheld';
    const METHOD_BARCODE = 'barcode_scan';
    const METHOD_MANUAL = 'manual';

    // Estados
    const STATUS_DRAFT = 'draft';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_PENDING_REVIEW = 'pending_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';

    // ==================== RELACIONES ====================

    /**
     * Múltiples Legal Entities (relación muchos a muchos)
     */
    public function legalEntities(): BelongsToMany
    {
        return $this->belongsToMany(LegalEntity::class, 'inventory_count_legal_entity')
                    ->withTimestamps();
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

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryCountItem::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    // ==================== SCOPES ====================

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeByLegalEntity($query, $legalEntityId)
    {
        return $query->whereHas('legalEntities', function ($q) use ($legalEntityId) {
            $q->where('legal_entities.id', $legalEntityId);
        });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeScheduledBetween($query, $start, $end)
    {
        return $query->whereBetween('scheduled_at', [$start, $end]);
    }

    // ==================== ATRIBUTOS ====================

    public function getTypeLabelAttribute(): string
    {
        $labels = [
            self::TYPE_FULL => 'Inventario Completo',
            self::TYPE_PARTIAL => 'Inventario Parcial',
            self::TYPE_CYCLIC => 'Inventario Cíclico',
            self::TYPE_SPOT_CHECK => 'Verificación Aleatoria',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    public function getMethodLabelAttribute(): string
    {
        $labels = [
            self::METHOD_RFID_BULK => 'RFID Masivo',
            self::METHOD_RFID_HANDHELD => 'RFID Portátil',
            self::METHOD_BARCODE => 'Código de Barras',
            self::METHOD_MANUAL => 'Manual',
        ];

        return $labels[$this->method] ?? $this->method;
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_PENDING_REVIEW => 'Pendiente Revisión',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_CANCELLED => 'Cancelado',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_DRAFT => 'gray',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_PENDING_REVIEW => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_CANCELLED => 'red',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Obtener nombres de Legal Entities seleccionadas
     */
    public function getLegalEntitiesNamesAttribute(): string
    {
        $names = $this->legalEntities->pluck('name')->toArray();
        
        if (count($names) === 0) {
            return 'Sin asignar';
        }
        
        if (count($names) <= 2) {
            return implode(', ', $names);
        }
        
        return $names[0] . ' y ' . (count($names) - 1) . ' más';
    }

    public function getLocationNameAttribute(): string
    {
        if ($this->storageLocation) {
            return $this->storageLocation->name;
        }
        if ($this->subWarehouse) {
            return $this->subWarehouse->name;
        }
        return 'Todas las ubicaciones';
    }

    public function getProgressPercentageAttribute(): float
    {
        $total = $this->items()->count();
        
        if ($total == 0) {
            return 0;
        }
        
        $counted = $this->items()->where('status', '!=', 'pending')->count();
        
        return round(($counted / $total) * 100, 2);
    }

    // ==================== MÉTODOS ====================

    /**
     * Generar número de inventario único
     */
    public static function generateCountNumber(): string
    {
        $date = now()->format('Ymd');
        
        $lastCount = self::where('count_number', 'like', "INV-{$date}-%")
            ->orderBy('count_number', 'desc')
            ->first();

        if ($lastCount) {
            $lastNumber = (int) substr($lastCount->count_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "INV-{$date}-{$newNumber}";
    }

    /**
     * Iniciar el conteo
     */
    public function start(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        return true;
    }

    /**
     * Marcar como completado (pendiente de revisión)
     */
    public function complete(): bool
    {
        if ($this->status !== self::STATUS_IN_PROGRESS) {
            return false;
        }

        $this->calculateSummary();

        $this->update([
            'status' => self::STATUS_PENDING_REVIEW,
            'completed_at' => now(),
        ]);

        return true;
    }

    /**
     * Aprobar el inventario
     */
    public function approve(int $userId): bool
    {
        if ($this->status !== self::STATUS_PENDING_REVIEW) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $userId,
        ]);

        return true;
    }

    /**
     * Cancelar el inventario
     */
    public function cancel(string $reason): bool
    {
        if (in_array($this->status, [self::STATUS_APPROVED, self::STATUS_CANCELLED])) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancellation_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Calcular resumen del inventario
     */
    public function calculateSummary(): void
    {
        $items = $this->items;

        $totalExpected = $items->sum('expected_quantity');
        $totalCounted = $items->sum('counted_quantity');
        
        // Para RFID: contar items encontrados vs esperados
        $totalMatched = $items->whereIn('status', ['found', 'matched'])->count();
        $totalDiscrepancies = $items->whereIn('status', ['surplus', 'missing', 'wrong_location', 'damaged', 'expired'])->count();

        $totalItems = $items->count();
        $accuracy = $totalItems > 0 
            ? round(($totalMatched / $totalItems) * 100, 2) 
            : 0;

        $this->update([
            'total_expected' => $totalExpected,
            'total_counted' => $totalCounted,
            'total_matched' => $totalMatched,
            'total_discrepancies' => $totalDiscrepancies,
            'accuracy_percentage' => $accuracy,
        ]);
    }

    /**
     * Verificar si puede ser editado
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Verificar si puede ser aprobado
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING_REVIEW;
    }

    /**
     * Generar items esperados basados en ProductUnits
     * Cada ProductUnit disponible genera un item a verificar
     */
    public function generateExpectedItems(): int
    {
        // Obtener IDs de legal entities seleccionadas
        $legalEntityIds = $this->legalEntities->pluck('id')->toArray();

        if (empty($legalEntityIds)) {
            return 0;
        }

        // Query base: ProductUnits disponibles
        $query = ProductUnit::with('product')
            ->whereIn('status', ['available', 'in_stock', 'reserved'])
            ->whereIn('legal_entity_id', $legalEntityIds);

        // Filtrar por ubicación si se especifica
        if ($this->storage_location_id) {
            $query->where('current_location_id', $this->storage_location_id);
        } elseif ($this->sub_warehouse_id) {
            $query->where('sub_warehouse_id', $this->sub_warehouse_id);
        }

        $productUnits = $query->get();

        $itemsCreated = 0;

        foreach ($productUnits as $unit) {
            $this->items()->create([
                'product_unit_id' => $unit->id,
                'product_id' => $unit->product_id,
                'product_code' => $unit->product->code,
                'product_name' => $unit->product->name,
                'expected_epc' => $unit->epc,
                'expected_serial' => $unit->serial_number,
                'expected_batch' => $unit->batch_number,
                'expected_quantity' => 1, // Cada ProductUnit es 1 unidad
                'counted_quantity' => 0,
                'difference' => -1,
                'status' => 'pending',
            ]);

            $itemsCreated++;
        }

        // Actualizar total esperado
        $this->update(['total_expected' => $itemsCreated]);

        return $itemsCreated;
    }

    /**
     * Obtener IDs de legal entities como array
     */
    public function getLegalEntityIdsAttribute(): array
    {
        return $this->legalEntities->pluck('id')->toArray();
    }

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->count_number)) {
                $model->count_number = self::generateCountNumber();
            }
            if (empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }
}
