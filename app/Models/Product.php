<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Category;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'specialty_id', 
        'subcategory_id',
        'manufacturer_id',
        'name',
        'code',
        'description',
        
        
        'tracking_type',
       
        
        'unit_cost',
        'minimum_stock',
        
        'status',
    ];

    protected $casts = [
        
        'unit_cost' => 'decimal:2',
    ];

    // ==================== RELACIONES ====================
    
    public function category() 
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function medicalSpecialty() 
    {
        return $this->belongsTo(MedicalSpecialty::class, 'specialty_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }
    
    public function manufacturer() 
    {
        return $this->belongsTo(Manufacturer::class);    
    }

    /**
     * Movimientos de inventario de este producto
     */
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Alias para inventoryMovements (compatibilidad)
     */
    public function inventoryMovements()
    {
        return $this->movements();
    }

    /**
     * Unidades individuales (para RFID/Serial)
     */
    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * Alias para productUnits (compatibilidad)
     */
    public function productUnits()
    {
        return $this->units();
    }

    // ==================== MÉTODOS DE TRACKING ====================
    
    public function usesRFID(): bool
    {
        return $this->tracking_type === 'rfid';
    }

    public function usesSerial(): bool
    {
        return $this->tracking_type === 'serial';
    }

    public function hasIndividualTracking(): bool
    {
        return in_array($this->tracking_type, ['rfid', 'serial']);
    }

    public function usesStockTracking(): bool
    {
        return $this->tracking_type === 'stock';
    }

    // ==================== MÉTODOS DE STOCK (MEJORADOS) ====================
    
    /**
     * Obtiene el stock en una ubicación específica según el tipo de tracking
     */
    public function getStockInLocation(int $locationId, string $status = 'available'): int
    {
        switch ($this->tracking_type) {
            case 'stock':
                // Para productos con tracking por cantidad (consumibles)
                return $this->getStockByMovements($locationId);
                
            case 'rfid':
            case 'serial':
                // Para productos con tracking individual (instrumentales)
                return $this->units()
                    ->where('current_location_id', $locationId)
                    ->where('status', $status)
                    ->count();
                
            case 'none':
            default:
                return 0;
        }
    }

    /**
     * Calcula stock mediante movimientos de inventario
     */
    private function getStockByMovements(int $locationId): int
    {
        $entries = $this->movements()
            ->where('to_location_id', $locationId)
            ->whereIn('type', ['entry', 'return', 'adjustment'])
            ->sum('quantity');

        $exits = $this->movements()
            ->where('from_location_id', $locationId)
            ->whereIn('type', ['exit', 'transfer', 'discard'])
            ->sum('quantity');

        return max(0, $entries - $exits);
    }

    /**
     * Stock actual (alias para compatibilidad)
     */
    public function getCurrentStock(): int
    {
        return $this->total_stock;
    }

    /**
     * Obtiene el stock total del producto en todas las ubicaciones
     */
    public function getTotalStockAttribute(): int
    {
        switch ($this->tracking_type) {
            case 'stock':
                $entries = $this->movements()
                    ->whereIn('type', ['entry', 'return'])
                    ->sum('quantity');
                    
                $exits = $this->movements()
                    ->whereIn('type', ['exit', 'discard'])
                    ->sum('quantity');
                    
                return max(0, $entries - $exits);
                
            case 'rfid':
            case 'serial':
                return $this->units()
                    ->whereIn('status', ['available', 'reserved', 'in_use'])
                    ->count();
                
            case 'none':
            default:
                return 0;
        }
    }

    /**
     * Obtiene solo el stock disponible
     */
    public function getAvailableStockAttribute(): int
    {
        switch ($this->tracking_type) {
            case 'stock':
                return $this->total_stock; // Para consumibles, todo es disponible
                
            case 'rfid':
            case 'serial':
                return $this->units()
                    ->where('status', 'available')
                    ->count();
                
            default:
                return 0;
        }
    }

    /**
     * Verifica si hay stock suficiente en una ubicación
     */
    public function hasStockInLocation(int $locationId, int $requiredQuantity = 1): bool
    {
        return $this->getStockInLocation($locationId) >= $requiredQuantity;
    }

    /**
     * Verificar si el stock está bajo
     */
    public function isLowStock(): bool
    {
        return $this->total_stock < $this->minimum_stock;
    }

    // ==================== RELACIONES DE UNIDADES (MEJORADAS) ====================
    
    /**
     * Unidades disponibles para uso
     */
    public function availableUnits()
    {
        return $this->units()->where('status', 'available');
    }

    /**
     * Unidades en uso
     */
    public function inUseUnits()
    {
        return $this->units()->where('status', 'in_use');
    }

    /**
     * Unidades en esterilización
     */
    public function inSterilizationUnits()
    {
        return $this->units()->where('status', 'in_sterilization');
    }

    /**
     * Unidades en mantenimiento
     */
    public function maintenanceUnits()
    {
        return $this->units()->where('status', 'maintenance');
    }

    /**
     * Unidades dañadas
     */
    public function damagedUnits()
    {
        return $this->units()->where('status', 'damaged');
    }

    // ==================== MÉTODOS AUXILIARES ====================
    
    /**
     * Obtiene las ubicaciones donde hay stock de este producto
     */
    public function getLocationsWithStock()
    {
        switch ($this->tracking_type) {
            case 'stock':
                // Obtener ubicaciones únicas de movimientos
                $locationIds = $this->movements()
                    ->where('to_location_id', '!=', null)
                    ->pluck('to_location_id')
                    ->unique();
                
                return StorageLocation::whereIn('id', $locationIds)
                    ->get()
                    ->filter(function($location) {
                        return $this->getStockInLocation($location->id) > 0;
                    });
                
            case 'rfid':
            case 'serial':
                $locationIds = $this->units()
                    ->whereIn('status', ['available', 'reserved', 'in_use'])
                    ->where('current_location_id', '!=', null)
                    ->pluck('current_location_id')
                    ->unique();
                
                return StorageLocation::whereIn('id', $locationIds)->get();
                
            default:
                return collect([]);
        }
    }

    /**
     * Obtiene los movimientos recientes de este producto
     */
    public function getRecentMovements(int $limit = 10)
    {
        return $this->movements()
            ->with(['fromLocation', 'toLocation', 'user', 'productUnit'])
            ->latest('movement_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtiene unidades disponibles en una ubicación (solo para RFID/Serial)
     */
    public function getAvailableUnitsInLocation(int $locationId)
    {
        if (!$this->hasIndividualTracking()) {
            return collect([]);
        }

        return $this->units()
            ->where('current_location_id', $locationId)
            ->where('status', 'available')
            ->get();
    }

    /**
     * Obtiene unidades que están por caducar
     */
    public function getExpiringUnits(int $days = 30)
    {
        if (!$this->hasIndividualTracking()) {
            return collect([]);
        }

        return $this->units()
            ->expiringSoon($days)
            ->get();
    }

    /**
     * Obtiene información resumida del inventario
     */
    public function getInventorySummary(): array
    {
        switch ($this->tracking_type) {
            case 'stock':
                return [
                    'tracking_type' => 'stock',
                    'total_stock' => $this->total_stock,
                    'minimum_stock' => $this->minimum_stock,
                    'is_low_stock' => $this->isLowStock(),
                    'locations_count' => $this->getLocationsWithStock()->count(),
                ];
                
            case 'rfid':
            case 'serial':
                $units = $this->units;
                return [
                    'tracking_type' => $this->tracking_type,
                    'total_units' => $units->count(),
                    'available' => $units->where('status', 'available')->count(),
                    'in_use' => $units->where('status', 'in_use')->count(),
                    'in_sterilization' => $units->where('status', 'in_sterilization')->count(),
                    'maintenance' => $units->where('status', 'maintenance')->count(),
                    'damaged' => $units->where('status', 'damaged')->count(),
                    'expired' => $units->where('status', 'expired')->count(),
                    'is_low_stock' => $units->where('status', 'available')->count() < $this->minimum_stock,
                ];
                
            default:
                return [
                    'tracking_type' => 'none',
                    'message' => 'Este producto no tiene tracking de inventario',
                ];
        }
    }

    /**
     * Obtiene el valor total del inventario
     */
    public function getInventoryValueAttribute(): float
    {
        switch ($this->tracking_type) {
            case 'stock':
                return $this->total_stock * ($this->unit_cost ?? 0);
                
            case 'rfid':
            case 'serial':
                return $this->units()
                    ->whereIn('status', ['available', 'in_use', 'reserved'])
                    ->sum('acquisition_cost');
                
            default:
                return 0;
        }
    }

    // ==================== SCOPES ====================
    
    /**
     * Scope para productos activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para productos con tracking RFID
     */
    public function scopeWithRFID($query)
    {
        return $query->where('tracking_type', 'rfid');
    }

    
    /**
     * Scope para productos con stock bajo
     */
    public function scopeLowStock($query)
    {
        return $query->where(function($q) {
            // Para productos con tracking 'stock'
            $q->where('tracking_type', 'stock')
              ->whereRaw('(SELECT COALESCE(SUM(CASE 
                  WHEN type IN ("entry", "return") THEN quantity 
                  WHEN type IN ("exit", "discard") THEN -quantity 
                  ELSE 0 END), 0) 
                  FROM inventory_movements 
                  WHERE inventory_movements.product_id = products.id) < products.minimum_stock');
        })->orWhere(function($q) {
            // Para productos con tracking 'rfid' o 'serial'
            $q->whereIn('tracking_type', ['rfid', 'serial'])
              ->whereRaw('(SELECT COUNT(*) 
                  FROM product_units 
                  WHERE product_units.product_id = products.id 
                  AND product_units.status = "available" 
                  AND product_units.deleted_at IS NULL) < products.minimum_stock');
        });
    }

    /**
     * Scope para productos con stock disponible
     */
    public function scopeInStock($query)
    {
        return $query->where(function($q) {
            $q->where('tracking_type', 'stock')
              ->whereRaw('(SELECT COALESCE(SUM(CASE 
                  WHEN type IN ("entry", "return") THEN quantity 
                  WHEN type IN ("exit", "discard") THEN -quantity 
                  ELSE 0 END), 0) 
                  FROM inventory_movements 
                  WHERE inventory_movements.product_id = products.id) > 0');
        })->orWhere(function($q) {
            $q->whereIn('tracking_type', ['rfid', 'serial'])
              ->whereRaw('(SELECT COUNT(*) 
                  FROM product_units 
                  WHERE product_units.product_id = products.id 
                  AND product_units.status IN ("available", "reserved") 
                  AND product_units.deleted_at IS NULL) > 0');
        });
    }
}