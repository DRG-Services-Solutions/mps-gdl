<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgeryPreparationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'preparation_id',
        'product_id',
        'quantity_required',
        'is_mandatory',
        'quantity_in_package',
        'quantity_missing',
        'quantity_picked',
        'status',
        'storage_location_id',
        'notes',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'quantity_required' => 'integer',
        'quantity_in_package' => 'integer',
        'quantity_missing' => 'integer',
        'quantity_picked' => 'integer',
    ];

    /**
     * RELACIONES
     */
    
    // Preparación a la que pertenece
    public function preparation()
    {
        return $this->belongsTo(SurgeryPreparation::class, 'preparation_id');
    }

    // Producto
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Ubicación del producto
    public function storageLocation()
    {
        return $this->belongsTo(StorageLocation::class, 'storage_location_id');
    }

    // Unidades físicas asignadas
    public function units()
    {
        return $this->hasMany(SurgeryPreparationUnit::class, 'preparation_item_id');
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Verificar si está completo
    public function isComplete()
    {
        return ($this->quantity_in_package + $this->quantity_picked) >= $this->quantity_required;
    }

    // Actualizar cantidades después de comparación
    public function updateAfterComparison($quantityInPackage)
    {
        $missing = max(0, $this->quantity_required - $quantityInPackage);
        
        $this->update([
            'quantity_in_package' => $quantityInPackage,
            'quantity_missing' => $missing,
            'status' => $missing === 0 ? 'in_package' : 'pending',
        ]);
    }

    // Agregar unidad surtida
    public function addPickedUnit($productUnitId, $sourceType, $userId)
    {
        // Crear registro de unidad
        SurgeryPreparationUnit::create([
            'preparation_item_id' => $this->id,
            'product_unit_id' => $productUnitId,
            'source_type' => $sourceType,
            'assigned_at' => now(),
            'assigned_by' => $userId,
        ]);

        // Actualizar cantidades
        $this->increment('quantity_picked');
        
        // Verificar si ya está completo
        if ($this->isComplete()) {
            $this->update(['status' => 'complete']);
        }
    }
}