<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StockUnit;

class Item extends Model
{
    use HasFactory;
    protected $table = 'items';
    protected $fillable = [ 
        'code', 
        'name', 
        'description',
        'status', 
        'type', 
        'manufacturer',
        'requires_maintenance',
        'maintenance_interval_uses',
        'is_active' 
    ];

    public function relations()
    {
        return $this->belongsToMany(Item::class, 'item_relations', 'item_id', 'related_item_id')
                    ->withPivot('type', 'notes')
                    ->withTimestamps();
    }

    public function requiredItems()
    {
        return $this->relations()->wherePivot('type', 'required');
    }

    public function stockUnits()
    {
        return $this->hasMany(StockUnit::class);
    }
    public function getAvailableStockCountAttribute()
    {
        return $this->stockUnits()->where('status', 'sterile')->count();
    }

    public function compatibleItems()
    {
        return $this->relations()->wherePivot('type', 'compatible');
    }

    public function components()
    {
        return $this->belongsToMany(Item::class, 'item_components', 'parent_item_id', 'child_item_id')
                    ->withPivot('quantity');
    }

    public function parentItems()
    {
        return $this->belongsToMany(
            Item::class, 
            'item_components', 
            'child_item_id',  
            'parent_item_id' 
        )
        ->withPivot('quantity')
        ->withTimestamps();
    }

    // Inverso de relations: modelos que dependen de mi o me relacionan
    public function relatedToMe()
    {
        return $this->belongsToMany(
            Item::class, 
            'item_relations', 
            'related_item_id', 
            'item_id'
        )
        ->withPivot('type', 'notes')
        ->withTimestamps();
    }

    public function getTypeLabelAttribute() {
        return match($this->type) {
            'instrumental'     => 'Instrumento Suelto',
            'implant'          => 'Implante',
            'implant_set'      => 'Set de Implantes',
            'instrumental_set' => 'Set de Instrumental',
            'equipment'        => 'Equipo Médico',
            'accessory'        => 'Accesorio',
            'tray'             => 'Charola',
            'console'          => 'Consola',
            'tower'            => 'Torre Médica',
            'kit'              => 'Kit',
            default            => 'General',
        };
    }

    public function getStatusColorAttribute() {
        return $this->is_active ? 'from-blue-500 to-indigo-600' : 'from-gray-400 to-gray-500';
    }

    public function scopeTrays($query)
    {
        return $query->whereIn('type', ['tray', 'instrumental_set', 'implant_set']);
    }

    public function scopeInstruments($query)
    {
        return $query->where('type', 'instrumental');
    }

    public function scopeHardware($query)
    {
        return $query->whereIn('type', ['console', 'tower', 'equipment']);
    }

    public function scopeEquipments($query)
    {
        return $query->where('type', 'equipment');
    }

    public function scopeKits($query)
    {
        return $query->where('type', 'kit');
    }

    public function scopeUnits($query)
    {
        return $query->where('type', 'unit');
    }

    public function scopeEcosystems($query)
    {
        return $query->whereIn('type', ['tower', 'equipment', 'console']);
    }

    /**
     * Scope: Solo trae Instrumental y Consumibles
     */
    public function scopeComponents($query)
    {
        return $query->whereIn('type', ['instrumental', 'implant', 'accessory', 'tray']);
    }

    /**
     * Devuelve los tipos de items que pueden ser contenidos dentro de este item,
     * respetando la jerarquía estricta.
     */
    public function getAllowedChildTypes()
    {
        $hierarchy = [
            'tower'            => 1,
            'kit'              => 2,
            'tray'             => 3,
            'instrumental_set' => 4,
            'implant_set'      => 5,
            'console'          => 6,
            'equipment'        => 7,
            'instrumental'     => 8,
            'implant'          => 9,
            'accessory'        => 10,
        ];

        if (!isset($hierarchy[$this->type])) {
            return [];
        }

        $myLevel = $hierarchy[$this->type];
        
        return array_keys(array_filter($hierarchy, function($level) use ($myLevel) {
            return $level > $myLevel;
        }));
    }
}
