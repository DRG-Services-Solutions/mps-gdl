<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'model',
        'description',
        'specifications',
        
        'tracking_type',
        'requires_sterilization',
        'is_consumable',
        'is_single_use',
        
        'unit_cost',
        'minimum_stock',
        
        'status',
        
      
    ];

    protected $casts = [
        'requires_sterilization' => 'boolean',
        'is_consumable' => 'boolean',
        'is_single_use' => 'boolean',
        'unit_cost' => 'decimal:2',
    ];

    // Relaciones
    public function category() 
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
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

  

    // Movimientos de inventario
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // Métodos de ayuda
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

    // Stock calculado desde unidades físicas
    public function getCurrentStock(): int
    {
        if ($this->hasIndividualTracking()) {
            // Contar unidades disponibles
            return $this->units()
                ->where('status', 'available')
                ->count();
        }
        
        // Para productos sin tracking individual, sumar movimientos
        return $this->movements()
            ->selectRaw('SUM(CASE 
                WHEN type = "entry" THEN quantity 
                WHEN type = "exit" THEN -quantity 
                ELSE 0 END) as total')
            ->value('total') ?? 0;
    }

    // Unidades disponibles para uso
    public function availableUnits()
    {
        return $this->units()->where('status', 'available');
    }

    // Unidades en uso
    public function inUseUnits()
    {
        return $this->units()->where('status', 'in_use');
    }

    // Unidades en esterilización
    public function inSterilizationUnits()
    {
        return $this->units()->where('status', 'in_sterilization');
    }

    // Verificar si el stock está bajo
    public function isLowStock(): bool
    {
        return $this->getCurrentStock() <= $this->minimum_stock;
    }

    // Scope para productos activos
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope para productos con tracking RFID
    public function scopeWithRFID($query)
    {
        return $query->where('tracking_type', 'rfid');
    }

    // Scope para instrumentales (con serial)
    public function scopeInstrumentals($query)
    {
        return $query->where('tracking_type', 'serial')
                    ->where('requires_sterilization', true);
    }

    // Scope para consumibles
    public function scopeConsumables($query)
    {
        return $query->where('is_consumable', true);
    }

    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function getAvailableStockAttribute()
    {
        return $this->availableUnits()->count();
    }

    public function getTotalStockAttribute()
    {
        return $this->units()->count();
    }
}