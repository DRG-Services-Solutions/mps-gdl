<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Category;
use App\Models\MedicalSpecialty;    
use App\Models\ProductType;
use App\Models\Brand;
use App\Models\InventoryItem;  
use App\Models\Supplier;              
use App\Models\InventoryMovement;     
use App\Models\ProductUnit;           
use App\Models\StorageLocation;       
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'product_type_id',
        'category_id',
        'supplier_id',
        'brand_id',
        'name',
        'code',
        'description',
        'is_composite',
        'requires_sterilization',
        'requires_refrigeration',
        'requires_temperature',
        'has_expiration_date',
        'tracking_type',
        'minimum_stock',
        'status',
        'list_price',
        'cost_price',
        
    ];

    protected $casts = [

        'requires_refrigeration' => 'boolean',
        'requires_sterilization' => 'boolean',
        'requires_temperature' => 'boolean',
        'list_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    // ==================== RELACIONES ====================

    public function components()
    {
        return $this->belongsToMany(Product::class, 'product_components', 'parent_product_id', 'child_product_id')
                    ->withPivot('quantity', 'is_mandatory', 'notes')
                    ->withTimestamps();
    }

    public function parentSets()
    {
        return $this->belongsToMany(Product::class, 'product_components', 'child_product_id', 'parent_product_id')
                    ->withPivot('quantity', 'is_mandatory', 'notes')
                    ->withTimestamps();
    }


    public function partOfSets()
    {
        return $this->belongsToMany(Product::class, 'product_components', 'child_product_id', 'parent_product_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }


    public function productType() 
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }
    
    public function brand() 
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
    public function category() 
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


    public function supplier() 
    {
        return $this->belongsTo(Supplier::class);    
    }

    public function productLayouts(): HasMany
    {
        return $this->hasMany(ProductLayout::class);
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

    public function usesLot(): bool
    {
        return $this->tracking_type === 'lote';
    }

    public function hasIndividualTracking(): bool
    {
        return in_array($this->tracking_type, ['code','rfid', 'serial']);
    }

    public function usesStockTracking(): bool
    {
        return $this->tracking_type === 'code';
    }

    // ==================== MÉTODOS DE STOCK ====================
    
    /**
     * Obtiene el stock en una ubicación específica según el tipo de tracking
     */
    public function getStockInLocation(int $locationId, string $status = 'available'): int
    {
        switch ($this->tracking_type) {
            case 'code':
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
   public function getTotalStockAttribute()
    {
        return $this->attributes['total_stock'] ?? $this->inventorySummaries()->sum('quantity_on_hand');
    }

    /**
     * Obtiene solo el stock disponible
     */
    public function getAvailableStockAttribute(): int
    {
        switch ($this->tracking_type) {
            case 'code':
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

    public function inventorySummaries()
    {
        return $this->hasMany(InventorySummary::class);
    }

    public function totalStockGlobal()
    {
        // Suma de todos los almacenes
        return $this->hasMany(InventorySummary::class)->sum('quantity_on_hand');
    }

    // ==================== MÉTODOS AUXILIARES ====================
    
    /**
     * Obtiene las ubicaciones donde hay stock de este producto
     */
    public function getLocationsWithStock()
    {
        switch ($this->tracking_type) {
            case 'code':
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
            case 'code':
                return [
                    'tracking_type' => 'code',
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
            case 'code':
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
            $q->where('tracking_type', 'code')
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
            $q->where('tracking_type', 'code')
              ->whereRaw('(SELECT COALESCE(SUM(CASE 
                  WHEN type IN ("entry", "return") THEN quantity 
                  WHEN type IN ("exit", "discard") THEN -quantity 
                  ELSE 0 END), 0) 
                  FROM inventory_movements 
                  WHERE inventory_movements.product_id = products.id) > 0');
        })->orWhere(function($q) {
            $q->whereIn('tracking_type', ['code','rfid', 'serial'])
              ->whereRaw('(SELECT COUNT(*) 
                  FROM product_units 
                  WHERE product_units.product_id = products.id 
                  AND product_units.status IN ("available", "reserved") 
                  AND product_units.deleted_at IS NULL) > 0');
        });
    }

    public static function findByCode($code)
    {
        // Parsear código compuesto si tiene separadores
        $parsedCode = static::parseBarcode($code);
        
        return static::where('code', $parsedCode)
                    ->where('status', 'active')
                    ->first();
    }

    public static function parseBarcode($scannedCode)
    {
        $scannedCode = trim($scannedCode);
        
        if (strpos($scannedCode, '|') !== false) {
            return explode('|', $scannedCode)[0];
        }
        
        $parts = explode('-', $scannedCode);
        if (count($parts) > 2) {
            return $parts[0] . '-' . $parts[1]; // Mantiene "AR-3128"
        }
        
        return $scannedCode;
    }
    
    public function getNextAvailableUnit($locationId = null, $legalEntityId = null)
    {
        return ProductUnit::nextAvailable($this->id, $locationId, $legalEntityId);
    }

    public function canBeAssignedToKit(): bool
    {
        return $this->status === 'active' && !$this->kit_id;
    }

    

}