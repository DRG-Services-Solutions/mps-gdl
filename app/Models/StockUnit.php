<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockUnit extends Model
{
    protected $table = 'stock_units';
    
    protected $fillable = [
        'item_id', 
        'serial_number', 
        
        'status', 
        'current_surgery_id', 
        'location_id',
        'total_uses', 
        'last_maintenance_at', 
        'sterilization_expires_at'
    ];

    protected $casts = [
        'last_maintenance_at' => 'datetime',
        'sterilization_expires_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function location()
    {
        return $this->belongsTo(StorageLocation::class, 'location_id');
    }

    public function currentSurgery()
    {
        return $this->belongsTo(ScheduledSurgery::class, 'current_surgery_id');
    }

    // ✨ Accessor para imprimir el estado bonito en Blade sin ensuciar el HTML
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'sterile' => 'Estéril',
            'dirty' => 'Sucio / Usado',
            'in_process' => 'En Lavado/CEYE',
            'in_surgery' => 'En Quirófano',
            'implanted' => 'Implantado',
            'maintenance' => 'En Mantenimiento',
            'retired' => 'Dado de Baja',
            default => 'Desconocido',
        };
    }

    /**
     * La receta específica que requiere esta unidad física exacta.
     */
    public function requiredItems()
    {
        return $this->belongsToMany(Item::class, 'stock_unit_recipes', 'stock_unit_id', 'item_id')
                    ->withPivot(['quantity', 'requirement_type', 'condition_rules', 'notes'])
                    ->withTimestamps();
    }
}