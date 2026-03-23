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
        'checklist_item_id',
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
    
    public function checklistItem()
    {
        return $this->belongsTo(\App\Models\ChecklistItem::class, 'checklist_item_id');
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
    /**
     * Atributo personalizado para obtener los condicionales del Checklist original
     */
    public function getConditionalsAttribute()
    {
        if ($this->checklist_item_id) {
            return \App\Models\ChecklistItem::find($this->checklist_item_id)
                ?->conditionals()->with('targetProduct')->get() ?? collect();
        }

        $surgery = $this->preparation->scheduledSurgery ?? null;
        if (!$surgery?->checklist_id) return collect();

        $checklistItem = \App\Models\ChecklistItem::where('checklist_id', $surgery->checklist_id)
            ->where('product_id', $this->product_id)
            ->first();

        return $checklistItem?->conditionals()->with('targetProduct')->get() ?? collect();
    }

    public function getApplicableConditional(): ?\App\Models\ChecklistConditional
    {
        $surgery = $this->preparation->scheduledSurgery;
        if (!$surgery) return null;

        $context = [
            'doctor_id'       => $surgery->doctor_id,
            'hospital_id'     => $surgery->hospital_id,
            'modality_id'     => $surgery->hospitalModalityConfig?->modality_id,
            'legal_entity_id' => $surgery->hospital?->legal_entity_id,
        ];

        $matching = $this->conditionals->filter(function ($conditional) use ($context) {
            if ($conditional->doctor_id       !== null && $conditional->doctor_id       != $context['doctor_id'])       return false;
            if ($conditional->hospital_id     !== null && $conditional->hospital_id     != $context['hospital_id'])     return false;
            if ($conditional->modality_id     !== null && $conditional->modality_id     != $context['modality_id'])     return false;
            if ($conditional->legal_entity_id !== null && $conditional->legal_entity_id != $context['legal_entity_id']) return false;
            return true;
        });

        return $matching->sortByDesc(fn($c) => $c->getSpecificityLevel())->first();
    }
}