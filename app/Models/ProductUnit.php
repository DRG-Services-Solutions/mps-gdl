<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'current_status',          
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
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function currentLocation()
    {
        return $this->belongsTo(StorageLocation::class, 'current_location_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function preAssembledPackage()
    {
        return $this->belongsTo(PreAssembledPackage::class, 'pre_assembled_package_id');
    }

    /**
     * Obtener entidad legal de cada producto asignada 
     */
    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function reserveForPreparation($packageId, $surgeryId, $userId)
    {
        if (!$this->isAvailable()) {
            throw new \Exception("Esta unidad no está disponible (estado: {$this->current_status})");
        }

        $this->update([
            'current_status' => self::STATUS_RESERVED, // ✅ Usar constante
            'current_package_id' => $packageId,
            'current_surgery_id' => $surgeryId,
            'reserved_at' => now(),
            'reserved_by' => $userId,
        ]);
    }

    public function markAsInUse($surgeryId)
    {
        $this->update([
            'current_status' => self::STATUS_IN_USE,
            'current_surgery_id' => $surgeryId,
            'updated_at' => now(),
        ]);
    }

    public function release()
    {
        $this->update([
            'current_status' => self::STATUS_AVAILABLE,
            'current_package_id' => null,
            'current_surgery_id' => null,
            'reserved_at' => null,
            'reserved_by' => null,
        ]);
    }

    /**
     * Obtener el sub almacen asignado a esta unidad
     */
    public function subWarehouse(): BelongsTo
    {
        return $this->belongsTo(SubWarehouse::class);
    }

    /**
     * Obtener la orden de compra de donde proviene este producto
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // ==================== ATRIBUTOS CALCULADOS ====================
    
    /**
     * Cada ProductUnit representa UNA unidad física individual
     * Este atributo indica si está disponible (1) o reservada (0)
     */
    public function getAvailableQuantityAttribute(): int
    {
        return $this->status === 'available' && $this->reserved_quantity == 0 ? 1 : 0;
    }

    /**
     * Alias para mantener compatibilidad
     */
    public function getQuantityAttribute(): int
    {
        return 1; // Cada ProductUnit siempre representa 1 unidad física
    }

    // ==================== SCOPES ====================
    
    public function scopeAvailable($query)
    {
        return $query->whereIn('current_status', [self::STATUS_AVAILABLE, 'in_stock']);
    }

    public function scopeInUse($query)
    {
        return $query->whereIn('current_status', [
            self::STATUS_RESERVED,
            self::STATUS_IN_USE,
            self::STATUS_IN_STERILIZATION
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

    // ==================== MÉTODOS AUXILIARES ====================
    
    /**
     * Verifica si la unidad está disponible para uso
     */
    public function isAvailable()
    {
        return in_array($this->current_status, [self::STATUS_AVAILABLE, 'in_stock']);
    }

    /**
     * Verifica si la unidad está caducada
     */
    public function isExpired(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }
        return $this->expiration_date->isPast();
    }

    /**
     * Verifica si está próximo a caducar
     */
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

    /**
     * Verifica si necesita mantenimiento
     */
    public function needsMaintenance(): bool
    {
        if (!$this->next_maintenance_date) {
            return false;
        }
        return $this->next_maintenance_date->isPast();
    }

    /**
     * Verifica si está cerca de alcanzar el máximo de ciclos
     */
    public function isNearMaxCycles($threshold = 0.9): bool
    {
        if (!$this->max_sterilization_cycles) {
            return false;
        }
        return $this->sterilization_cycles >= ($this->max_sterilization_cycles * $threshold);
    }

    /**
     * Obtiene el identificador único (EPC o Serial)
     */
    public function getUniqueIdentifierAttribute(): string
    {
        return $this->epc ?? $this->serial_number ?? 'N/A';
    }

    /**
     * Calcula los días hasta la caducidad
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expiration_date) {
            return null;
        }
        return Carbon::now()->diffInDays($this->expiration_date, false);
    }

    /**
     * Obtiene el estado con formato legible
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'available' => 'Disponible',
            'in_use' => 'En Uso',
            'reserved' => 'Reservado',
            'in_sterilization' => 'En Esterilización',
            'maintenance' => 'En Mantenimiento',
            'quarantine' => 'En Cuarentena',
            'damaged' => 'Dañado',
            'expired' => 'Caducado',
            'lost' => 'Extraviado',
            'retired' => 'Dado de Baja',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Obtiene el color del badge según el estado
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'available' => 'green',
            'in_use' => 'blue',
            'reserved' => 'yellow',
            'in_sterilization' => 'purple',
            'maintenance' => 'orange',
            'quarantine' => 'gray',
            'damaged' => 'red',
            'expired' => 'red',
            'lost' => 'red',
            'retired' => 'gray',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    // ==================== EVENTOS DEL MODELO ====================
    
    protected static function boot()
    {
        parent::boot();

        // Al crear una unidad
        static::creating(function ($unit) {
            $unit->created_by = auth()->id();
        });

        // Al actualizar una unidad
        static::updating(function ($unit) {
            $unit->updated_by = auth()->id();
            
            // Si está caducado, actualizar estado automáticamente
            if ($unit->isExpired() && $unit->status !== 'expired') {
                $unit->status = 'expired';
            }
        });
    }

    public function scopeNextAvailable($query, $productId, $locationId = null, $legalEntityId = null)
    {
        // Filtrar por producto y estado disponible
        $query = $query->where('product_id', $productId)
                    ->where('status', self::STATUS_AVAILABLE);
        
        // Filtrar por ubicación si se especifica
        if ($locationId) {
            $query->where('current_location_id', $locationId);
        }
        
        // Filtrar por entidad legal si se especifica
        if ($legalEntityId) {
            $query->where('legal_entity_id', $legalEntityId);
        }
        
        // Ordenar por prioridad:
        // 1. Si tiene expiration_date: ordenar por caducidad (FEFO)
        // 2. Si no tiene expiration_date: ordenar por antigüedad (FIFO)
        return $query->orderByRaw('
            CASE 
                WHEN expiration_date IS NOT NULL THEN expiration_date
                ELSE COALESCE(manufacture_date, acquisition_date, created_at)
            END ASC
        ')->first();
    }

    public static function findByEPC($epc)
    {
        return static::where('epc', $epc)
                    ->where('status', self::STATUS_AVAILABLE)
                    ->first();
    }

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

    public function getConfirmationData()
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


}