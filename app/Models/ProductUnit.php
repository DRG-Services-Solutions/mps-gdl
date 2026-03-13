<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class ProductUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'epc',
        'serial_number',
        'batch_number',
        'expiration_date',
        'manufacture_date',
        'status',                   
        'current_location_id',
        'current_package_id',
        'current_surgery_id',
        'reserved_at',
        'reserved_by',
        'sterilization_cycles',
        'last_sterilization_date',
        'next_maintenance_date',
        'max_sterilization_cycles',
        'acquisition_cost',
        'acquisition_date',
        'supplier_id',
        'supplier_invoice',
        'purchase_order_id',        
        'print_job_id',             
        'notes',
        'damage_description',
        'created_by',
        'updated_by',
        'legal_entity_id',
        'sub_warehouse_id',
        'reserved_quantity',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'manufacture_date' => 'date',
        'last_sterilization_date' => 'date',
        'next_maintenance_date' => 'date',
        'acquisition_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'sterilization_cycles' => 'integer',
        'max_sterilization_cycles' => 'integer',
        'reserved_quantity' => 'integer',
        'reserved_at' => 'datetime',
    ];

    // ==================== CONSTANTES DE ESTADO ====================

    const STATUS_AVAILABLE = 'available';
    const STATUS_IN_USE = 'in_use';
    const STATUS_RESERVED = 'reserved';
    const STATUS_IN_STERILIZATION = 'in_sterilization';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_QUARANTINE = 'quarantine';
    const STATUS_DAMAGED = 'damaged';
    const STATUS_EXPIRED = 'expired';
    const STATUS_LOST = 'lost';
    const STATUS_RETIRED = 'retired';

    // ==================== RELACIONES ====================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class, 'current_location_id');
    }

    /**
     * Paquete pre-armado al que está asignada actualmente
     */
    public function currentPackage(): BelongsTo
    {
        return $this->belongsTo(PreAssembledPackage::class, 'current_package_id');
    }

    /**
     * Cirugía programada a la que está asignada
     */
    public function currentSurgery(): BelongsTo
    {
        return $this->belongsTo(ScheduledSurgery::class, 'current_surgery_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Orden de compra de donde proviene esta unidad
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Job de impresión de etiquetas generado en recepción de OC
     */
    public function printJob(): BelongsTo
    {
        return $this->belongsTo(PrintJob::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reservedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_by');
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function subWarehouse(): BelongsTo
    {
        return $this->belongsTo(SubWarehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function lastMovement(): HasOne
    {
        return $this->hasOne(ProductUnitMovement::class)->latestOfMany('performed_at');
    }

    // ==================== MÉTODOS DE NEGOCIO ====================

    /**
     * Reservar unidad para preparación de cirugía
     * 
     * CORREGIDO: Usa `status` en lugar de `current_status`
     */
    public function reserveForPreparation($packageId, $surgeryId, $userId)
    {
        if (!$this->isAvailable()) {
            throw new \Exception("Esta unidad no está disponible (estado: {$this->status})");
        }

        $this->update([
            'status' => self::STATUS_RESERVED,
            'current_package_id' => $packageId,
            'current_surgery_id' => $surgeryId,
            'reserved_at' => now(),
            'reserved_by' => $userId,
        ]);
    }

    /**
     * Marcar unidad como en uso durante cirugía
     * 
     * CORREGIDO: Usa `status` en lugar de `current_status`
     */
    public function markAsInUse($surgeryId)
    {
        $this->update([
            'status' => self::STATUS_IN_USE,
            'current_surgery_id' => $surgeryId,
        ]);
    }

    /**
     * Liberar unidad después de uso
     * 
     * CORREGIDO: Usa `status` en lugar de `current_status`
     */
    public function release()
    {
        $this->update([
            'status' => self::STATUS_AVAILABLE,
            'current_package_id' => null,
            'current_surgery_id' => null,
            'reserved_at' => null,
            'reserved_by' => null,
        ]);
    }

    /**
     * Reservar unidad (método genérico)
     */
    public function reserve($userId, $surgeryId = null, $packageId = null)
    {
        if (!$this->isAvailable()) {
            throw new \Exception("Esta unidad no está disponible (estado: {$this->status})");
        }

        return $this->update([
            'status' => self::STATUS_RESERVED,
            'reserved_at' => now(),
            'reserved_by' => $userId,
            'current_surgery_id' => $surgeryId,
            'current_package_id' => $packageId,
        ]);
    }

    // ==================== ATRIBUTOS CALCULADOS ====================

    /**
     * Cada ProductUnit representa UNA unidad física individual.
     * Retorna 1 si está disponible y sin reserva, 0 si no.
     */
    public function getAvailableQuantityAttribute(): int
    {
        return $this->status === self::STATUS_AVAILABLE && $this->reserved_quantity == 0 ? 1 : 0;
    }

    /**
     * Siempre es 1 — cada ProductUnit es una unidad física
     */
    public function getQuantityAttribute(): int
    {
        return 1;
    }

    /**
     * Identificador único (EPC o Serial)
     */
    public function getUniqueIdentifierAttribute(): string
    {
        if ($this->epc) {
            return $this->epc;
        }
        if ($this->serial_number) {
            return $this->serial_number;
        }
        return $this->product->code ?? 'N/A';
    }

    /**
     * Días hasta la caducidad (negativo si ya caducó)
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expiration_date) {
            return null;
        }
        return Carbon::now()->diffInDays($this->expiration_date, false);
    }

    /**
     * Etiqueta legible del estado
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_AVAILABLE => 'Disponible',
            self::STATUS_IN_USE => 'En Uso',
            self::STATUS_RESERVED => 'Reservado',
            self::STATUS_IN_STERILIZATION => 'En Esterilización',
            self::STATUS_MAINTENANCE => 'En Mantenimiento',
            self::STATUS_QUARANTINE => 'En Cuarentena',
            self::STATUS_DAMAGED => 'Dañado',
            self::STATUS_EXPIRED => 'Caducado',
            self::STATUS_LOST => 'Extraviado',
            self::STATUS_RETIRED => 'Dado de Baja',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Color de badge según estado
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_AVAILABLE => 'green',
            self::STATUS_IN_USE => 'blue',
            self::STATUS_RESERVED => 'yellow',
            self::STATUS_IN_STERILIZATION => 'purple',
            self::STATUS_MAINTENANCE => 'orange',
            self::STATUS_QUARANTINE => 'gray',
            self::STATUS_DAMAGED => 'red',
            self::STATUS_EXPIRED => 'red',
            self::STATUS_LOST => 'red',
            self::STATUS_RETIRED => 'gray',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    // ==================== SCOPES ====================

    /**
     * Unidades disponibles
     * 
     * CORREGIDO: Usa `status` en lugar de `current_status`
     * CORREGIDO: Eliminado 'in_stock' que no existe en el enum de la migración
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    /**
     * Unidades reservadas
     */
    public function scopeReserved($query)
    {
        return $query->where('status', self::STATUS_RESERVED);
    }

    /**
     * Unidades en uso (reservadas, en uso, o en esterilización)
     * 
     * CORREGIDO: Usa `status` en lugar de `current_status`
     */
    public function scopeInUse($query)
    {
        return $query->whereIn('status', [
            self::STATUS_RESERVED,
            self::STATUS_IN_USE,
            self::STATUS_IN_STERILIZATION,
        ]);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiration_date')
                     ->where('expiration_date', '<=', Carbon::now()->addDays($days))
                     ->where('expiration_date', '>', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiration_date')
                     ->where('expiration_date', '<', Carbon::now());
    }

    public function scopeInLocation($query, $locationId)
    {
        return $query->where('current_location_id', $locationId);
    }

    public function scopeByBatch($query, $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }

    public function scopeNeedsMaintenanceSoon($query, $days = 7)
    {
        return $query->whereNotNull('next_maintenance_date')
                     ->where('next_maintenance_date', '<=', Carbon::now()->addDays($days))
                     ->where('next_maintenance_date', '>', Carbon::now());
    }

    public function scopeByLegalEntity($query, $legalEntityId)
    {
        return $query->where('legal_entity_id', $legalEntityId);
    }

    /**
     * Siguiente unidad disponible (FEFO/FIFO)
     */
    public function scopeNextAvailable($query, $productId, $locationId = null, $legalEntityId = null)
    {
        $query->where('product_id', $productId)
              ->where('status', self::STATUS_AVAILABLE);

        if ($locationId) {
            $query->where('current_location_id', $locationId);
        }

        if ($legalEntityId) {
            $query->where('legal_entity_id', $legalEntityId);
        }

        return $query->orderByRaw('
            CASE 
                WHEN expiration_date IS NOT NULL THEN expiration_date
                ELSE COALESCE(manufacture_date, acquisition_date, created_at)
            END ASC
        ');
    }

    // ==================== MÉTODOS DE VERIFICACIÓN ====================

    /**
     * Verifica si la unidad está disponible
     * 
     * CORREGIDO: Usa `status` en lugar de `current_status`
     * CORREGIDO: Eliminado 'in_stock' — no existe en el enum
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isExpired(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }
        return $this->expiration_date->isPast();
    }

    public function isExpiringSoon($days = 30): bool
    {
        if (!$this->expiration_date) {
            return false;
        }
        return $this->expiration_date->isBetween(
            Carbon::now(),
            Carbon::now()->addDays($days)
        );
    }

    public function needsMaintenance(): bool
    {
        if (!$this->next_maintenance_date) {
            return false;
        }
        return $this->next_maintenance_date->isPast();
    }

    public function isNearMaxCycles($threshold = 0.9): bool
    {
        if (!$this->max_sterilization_cycles) {
            return false;
        }
        return $this->sterilization_cycles >= ($this->max_sterilization_cycles * $threshold);
    }

    // ==================== BÚSQUEDA ====================

    public static function findByEPC($epc)
    {
        return static::where('epc', $epc)
                      ->where('status', self::STATUS_AVAILABLE)
                      ->first();
    }

    // ==================== DATOS PARA CONFIRMACIÓN ====================

    public function getConfirmationData(): array
    {
        return [
            'unit_id' => $this->id,
            'epc' => $this->epc,
            'serial_number' => $this->serial_number,
            'product_code' => $this->product->code,
            'product_name' => $this->product->name,
            'batch_number' => $this->batch_number,
            'expiration_date' => $this->expiration_date?->format('Y-m-d'),
            'days_until_expiration' => $this->days_until_expiration,
            'is_expiring_soon' => $this->isExpiringSoon(30),
            'location_code' => $this->currentLocation?->code,
            'location_name' => $this->currentLocation?->name,
            'status' => $this->status,
            'status_label' => $this->status_label,
        ];
    }

    // ==================== EVENTOS DEL MODELO ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($unit) {
            $unit->created_by = auth()->id();
        });

        static::updating(function ($unit) {
            $unit->updated_by = auth()->id();

            // Auto-expirar si la fecha de caducidad ya pasó
            if ($unit->isExpired() && $unit->status !== self::STATUS_EXPIRED) {
                $unit->status = self::STATUS_EXPIRED;
            }
        });
    }
}