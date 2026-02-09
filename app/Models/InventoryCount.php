<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'legal_entity_id',
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
        return $query->where('legal_entity_id', $legalEntityId);
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
        if ($this->total_expected == 0) {
            return 0;
        }
        
        $counted = $this->items()->where('status', '!=', 'pending')->count();
        $total = $this->items()->count();
        
        return $total > 0 ? round(($counted / $total) * 100, 2) : 0;
    }

    // ==================== MÉTODOS ====================

    /**
     * Generar número de inventario único
     */
    public static function generateCountNumber(int $legalEntityId): string
    {
        $legalEntity = LegalEntity::find($legalEntityId);
        $prefix = $legalEntity ? strtoupper(substr($legalEntity->name, 0, 3)) : 'INV';
        
        $date = now()->format('Ymd');
        
        $lastCount = self::where('count_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('count_number', 'desc')
            ->first();

        if ($lastCount) {
            $lastNumber = (int) substr($lastCount->count_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "{$prefix}-{$date}-{$newNumber}";
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
        $totalMatched = $items->where('status', 'matched')->count();
        $totalDiscrepancies = $items->whereIn('status', ['surplus', 'shortage', 'not_found', 'unexpected'])->count();

        $accuracy = $totalExpected > 0 
            ? round(($totalMatched / $items->count()) * 100, 2) 
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
     * Generar items esperados basados en el inventario actual
     */
    public function generateExpectedItems(): int
    {
        $query = ProductUnit::where('status', 'available');

        // Filtrar por alcance
        if ($this->storage_location_id) {
            $query->where('current_location_id', $this->storage_location_id);
        } elseif ($this->sub_warehouse_id) {
            $query->where('sub_warehouse_id', $this->sub_warehouse_id);
        }

        $query->where('legal_entity_id', $this->legal_entity_id);

        $units = $query->with('product')->get();

        // Agrupar por producto
        $grouped = $units->groupBy('product_id');

        $itemsCreated = 0;

        foreach ($grouped as $productId => $productUnits) {
            $firstUnit = $productUnits->first();
            
            $this->items()->create([
                'product_id' => $productId,
                'product_code' => $firstUnit->product->code,
                'product_name' => $firstUnit->product->name,
                'expected_quantity' => $productUnits->count(),
                'counted_quantity' => 0,
                'difference' => 0 - $productUnits->count(),
                'status' => 'pending',
            ]);

            $itemsCreated++;
        }

        return $itemsCreated;
    }

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->count_number)) {
                $model->count_number = self::generateCountNumber($model->legal_entity_id);
            }
            if (empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }
}
