<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgeryPreparationUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'preparation_item_id',
        'product_unit_id',
        'source_type',
        'source_package_id',
        'assigned_at',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * RELACIONES
     */
    
    // Item de preparación
    public function preparationItem()
    {
        return $this->belongsTo(SurgeryPreparationItem::class, 'preparation_item_id');
    }

    // Unidad física asignada
    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function sourcePackage()
    {
        return $this->belongsTo(PreAssembledPackage::class, 'source_package_id');
    }

    // Usuario que asignó
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Verificar si viene de pre-armado
    public function isFromPreAssembled()
    {
        return $this->source_type === 'pre_assembled';
    }

    // Verificar si viene de almacén
    public function isFromWarehouse()
    {
        return $this->source_type === 'warehouse';
    }
}