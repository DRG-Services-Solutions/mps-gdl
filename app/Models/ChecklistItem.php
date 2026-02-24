<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_id',     
        'product_id',
        'quantity',
        'is_mandatory',
        'order',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_mandatory' => 'boolean',
        'order' => 'integer',
    ];

    // ==================== RELACIONES ====================
    
    /**
     * Check list al que pertenece
     */
    public function checklist()
    {
        return $this->belongsTo(SurgicalChecklist::class, 'checklist_id'); // ✅ CORRECTO
    }

    /**
     * Producto asociado
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Condicionales de este item
     */
    public function conditionals()
    {
        return $this->hasMany(ChecklistConditional::class)
                    ->orderBy('id', 'desc');
    }

    // ==================== MÉTODOS PRINCIPALES ====================
    
    /**
     * Obtener cantidad ajustada según contexto de cirugía
     * 
     * @param ScheduledSurgery|object $surgery
     * @return array
     */
    public function getAdjustedQuantity($surgery): array
    {
        $baseQuantity = $this->quantity;
        
        // Buscar condicional aplicable (más específico primero)
        $conditional = $this->findApplicableConditional($surgery);
        
        if (!$conditional) {
            return [
                'final_quantity' => $baseQuantity,
                'base_quantity' => $baseQuantity,
                'has_conditional' => false,
                'conditional' => null,
                'conditional_description' => null,
            ];
        }
        
        // Aplicar condicional según su tipo
        if ($conditional->is_additional_product) {
            $finalQuantity = $conditional->additional_quantity ?? 0;
            
            return [
                'final_quantity' => $finalQuantity,
                'base_quantity' => $baseQuantity,
                'has_conditional' => true,
                'conditional' => $conditional,
                'conditional_description' => "Producto adicional: {$conditional->getDescription()}",
            ];
        }
        
        // Reemplazar cantidad base
        $finalQuantity = $conditional->quantity_override ?? $baseQuantity;
        
        return [
            'final_quantity' => $finalQuantity,
            'base_quantity' => $baseQuantity,
            'has_conditional' => $finalQuantity !== $baseQuantity,
            'conditional' => $conditional,
            'conditional_description' => $finalQuantity !== $baseQuantity 
                ? $conditional->getDescription() 
                : null,
        ];
    }

    /**
     * Buscar el condicional más específico que aplica
     * Prioridad: 4 criterios > 3 criterios > 2 criterios > 1 criterio
     * 
     * @param ScheduledSurgery|object $surgery
     * @return ChecklistConditional|null
     */
    protected function findApplicableConditional($surgery): ?ChecklistConditional
    {
        $legalEntityId = $surgery->hospital->legal_entity_id ?? null;
        
        // ✅ FIX: Obtener el modality_id real desde la configuración
        $modalityId = null;
        if ($surgery->hospital_modality_config_id) {
            $config = \App\Models\HospitalModalityConfig::find($surgery->hospital_modality_config_id);
            $modalityId = $config?->modality_id;
        }

        Log::info("Buscando condicional para:", [
            'checklist_item_id' => $this->id,
            'doctor_id' => $surgery->doctor_id,
            'hospital_id' => $surgery->hospital_id,
            'modality_id' => $modalityId,
            'legal_entity_id' => $legalEntityId,
        ]);

        $conditionals = $this->conditionals()->get();

        if ($conditionals->isEmpty()) {
            Log::info("No hay condicionales para este item");
            return null;
        }

        $conditionals = $conditionals->sortByDesc(function ($conditional) {
            return $conditional->getSpecificityLevel();
        });

        foreach ($conditionals as $conditional) {
            if ($this->conditionalMatches($conditional, $surgery, $legalEntityId, $modalityId)) {
                Log::info("Condicional aplicable encontrado:", [
                    'conditional_id' => $conditional->id,
                    'description' => $conditional->getDescription(),
                ]);
                return $conditional;
            }
        }

        Log::info("Ningún condicional coincidió con los criterios");
        return null;
    }

    /**
     * Verificar si un condicional coincide con los criterios de la cirugía
     * 
     * @param ChecklistConditional $conditional
     * @param ScheduledSurgery|object $surgery
     * @param int|null $legalEntityId
     * @return bool
     */
    
    protected function conditionalMatches(
    ChecklistConditional $conditional,
    $surgery,
    ?int $legalEntityId,
    ?int $modalityId
): bool 
    {
    if ($conditional->doctor_id !== null) {
        if ((int) $conditional->doctor_id !== (int) $surgery->doctor_id) {
            Log::debug("No coincide doctor", [
                'conditional' => $conditional->doctor_id,
                'surgery' => $surgery->doctor_id,
            ]);
            return false;
        }
    }

    if ($conditional->hospital_id !== null) {
        if ((int) $conditional->hospital_id !== (int) $surgery->hospital_id) {
            Log::debug("No coincide hospital", [
                'conditional' => $conditional->hospital_id,
                'surgery' => $surgery->hospital_id,
            ]);
            return false;
        }
    }

    // ✅ FIX: Comparar contra modality_id real, NO hospital_modality_config_id
    if ($conditional->modality_id !== null) {
        if ((int) $conditional->modality_id !== (int) $modalityId) {
            Log::debug("No coincide modalidad", [
                'conditional' => $conditional->modality_id,
                'surgery_modality' => $modalityId,
            ]);
            return false;
        }
    }

    if ($conditional->legal_entity_id !== null) {
        if ((int) $conditional->legal_entity_id !== (int) $legalEntityId) {
            Log::debug("No coincide legal entity", [
                'conditional' => $conditional->legal_entity_id,
                'surgery' => $legalEntityId,
            ]);
            return false;
        }
    }

    return true;
}

    // ==================== SCOPES ====================
    
    /**
     * Items obligatorios
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Items opcionales
     */
    public function scopeOptional($query)
    {
        return $query->where('is_mandatory', false);
    }

    /**
     * Ordenar por posición
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Por checklist
     */
    public function scopeForChecklist($query, $checklistId)
    {
        return $query->where('checklist_id', $checklistId); // ✅ CORRECTO
    }

    // ==================== ATRIBUTOS CALCULADOS ====================
    
    /**
     * Obtener nombre del producto
     */
    public function getProductNameAttribute(): string
    {
        return $this->product?->name ?? 'Producto Desconocido';
    }

    /**
     * Verificar si tiene condicionales activos
     */
    public function getHasConditionalsAttribute(): bool
    {
        return $this->conditionals()->exists();
    }

    /**
     * Contar condicionales
     */
    public function getConditionalsCountAttribute(): int
    {
        return $this->conditionals()->count();
    }
}