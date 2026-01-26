<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Collection;

class ChecklistConditional extends Model
{
    protected $fillable = [
        'checklist_item_id',
        'doctor_id',
        'hospital_id',
        'modality_id',
        'legal_entity_id',
        'action_type',
        'quantity_override',
        'is_additional_product',
        'additional_quantity',
        'exclude_from_invoice',
        'target_product_id',
        'dependency_quantity',
        'requires_approval',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_additional_product' => 'boolean',
        'exclude_from_invoice' => 'boolean',
        'requires_approval' => 'boolean',
        'quantity_override' => 'integer',
        'additional_quantity' => 'integer',
        'dependency_quantity' => 'integer',
    ];

    /**
     * RELACIONES
     */

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function modality(): BelongsTo
    {
        return $this->belongsTo(Modality::class);
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Producto objetivo (para dependencias o reemplazos)
     */
    public function targetProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'target_product_id');
    }

    /**
     * SCOPES
     */

    /**
     * Filtrar por doctor (incluyendo null)
     */
    public function scopeForDoctor($query, ?int $doctorId)
    {
        return $query->where(function ($q) use ($doctorId) {
            $q->whereNull('doctor_id')
              ->orWhere('doctor_id', $doctorId);
        });
    }

    /**
     * Filtrar por hospital (incluyendo null)
     */
    public function scopeForHospital($query, ?int $hospitalId)
    {
        return $query->where(function ($q) use ($hospitalId) {
            $q->whereNull('hospital_id')
              ->orWhere('hospital_id', $hospitalId);
        });
    }

    /**
     * Filtrar por modalidad (incluyendo null)
     */
    public function scopeForModality($query, ?int $modalityId)
    {
        return $query->where(function ($q) use ($modalityId) {
            $q->whereNull('modality_id')
              ->orWhere('modality_id', $modalityId);
        });
    }

    /**
     * Filtrar por legal entity (incluyendo null)
     */
    public function scopeForLegalEntity($query, ?int $legalEntityId)
    {
        return $query->where(function ($q) use ($legalEntityId) {
            $q->whereNull('legal_entity_id')
              ->orWhere('legal_entity_id', $legalEntityId);
        });
    }

    /**
     * Solo productos adicionales
     */
    public function scopeAdditionalProducts($query)
    {
        return $query->where('is_additional_product', true);
    }

    /**
     * Solo dependencias
     */
    public function scopeDependencies($query)
    {
        return $query->where('action_type', 'add_dependency');
    }

    /**
     * MÉTODOS DE NEGOCIO
     */

    /**
     * Obtener la cantidad final considerando este condicional
     */
    public function getEffectiveQuantity(?int $baseQuantity = null): int
    {
        // Para productos adicionales (backward compatibility)
        if ($this->is_additional_product) {
            return $this->additional_quantity ?? 0;
        }

        // Para action_type = add_product
        if ($this->action_type === 'add_product') {
            return $this->additional_quantity ?? 0;
        }

        // Para action_type = add_dependency
        if ($this->action_type === 'add_dependency') {
            return $this->dependency_quantity ?? 1;
        }

        // Para action_type = adjust_quantity o default
        if ($this->quantity_override !== null) {
            return $this->quantity_override;
        }

        return $baseQuantity ?? 0;
    }

    /**
     * Verificar si es una dependencia
     */
    public function isDependency(): bool
    {
        return $this->action_type === 'add_dependency' && $this->target_product_id !== null;
    }

    /**
     * Verificar si es un reemplazo
     */
    public function isReplacement(): bool
    {
        return $this->action_type === 'replace' && $this->target_product_id !== null;
    }

    /**
     * Verificar si es una exclusión
     */
    public function isExclusion(): bool
    {
        return $this->action_type === 'exclude';
    }

    /**
     * Verificar qué tan específico es este condicional
     * Nivel más alto = más específico = más prioridad
     */
    public function getSpecificityLevel(): int
    {
        $level = 0;
        if ($this->doctor_id !== null) $level++;
        if ($this->hospital_id !== null) $level++;
        if ($this->modality_id !== null) $level++;
        if ($this->legal_entity_id !== null) $level++;
        return $level;
    }

    /**
     * Obtener hash de criterios para detectar conflictos
     */
    public function getCriteriaHash(): string
    {
        return md5(implode('|', [
            $this->doctor_id ?? 'null',
            $this->hospital_id ?? 'null',
            $this->modality_id ?? 'null',
            $this->legal_entity_id ?? 'null',
        ]));
    }

    /**
     * Verificar si este condicional tiene los mismos criterios que otro
     */
    public function hasSameCriteriaAs(ChecklistConditional $other): bool
    {
        return $this->doctor_id === $other->doctor_id
            && $this->hospital_id === $other->hospital_id
            && $this->modality_id === $other->modality_id
            && $this->legal_entity_id === $other->legal_entity_id;
    }

    /**
     * Verificar si este condicional es más específico que otro
     */
    public function isMoreSpecificThan(ChecklistConditional $other): bool
    {
        return $this->getSpecificityLevel() > $other->getSpecificityLevel();
    }

    /**
     * Detectar conflictos potenciales con otros condicionales del mismo item
     * 
     * @return array ['has_conflict' => bool, 'conflicts' => Collection, 'warnings' => array]
     */
    public function detectConflicts(): array
    {
        $conflicts = new Collection();
        $warnings = [];

        // Obtener todos los condicionales del mismo item
        $otherConditionals = ChecklistConditional::where('checklist_item_id', $this->checklist_item_id)
            ->where('id', '!=', $this->id ?? 0)
            ->get();

        foreach ($otherConditionals as $other) {
            // CONFLICTO EXACTO: Mismos criterios, misma acción
            if ($this->hasSameCriteriaAs($other)) {
                // Si tienen el mismo action_type pero diferentes valores
                if ($this->action_type === $other->action_type) {
                    $conflicts->push([
                        'type' => 'exact_duplicate',
                        'severity' => 'high',
                        'message' => 'Condicional duplicado con los mismos criterios',
                        'conditional' => $other,
                    ]);
                } else {
                    $conflicts->push([
                        'type' => 'action_conflict',
                        'severity' => 'high',
                        'message' => 'Mismo criterio pero diferente acción',
                        'conditional' => $other,
                    ]);
                }
            }

            // CONFLICTO DE SUPERPOSICIÓN: Uno es más específico que el otro
            if ($this->isSubsetOf($other) || $other->isSubsetOf($this)) {
                $warnings[] = [
                    'type' => 'overlap',
                    'severity' => 'medium',
                    'message' => 'Posible superposición de criterios. El más específico tendrá prioridad.',
                    'conditional' => $other,
                ];
            }
        }

        return [
            'has_conflict' => $conflicts->isNotEmpty(),
            'conflicts' => $conflicts,
            'warnings' => $warnings,
        ];
    }

    /**
     * Verificar si este condicional es un subconjunto de otro
     * (es decir, es más específico)
     */
    private function isSubsetOf(ChecklistConditional $other): bool
    {
        // Si este tiene un doctor específico y el otro no, este es subset
        if ($this->doctor_id !== null && $other->doctor_id === null) {
            // Verificar que los demás criterios coincidan o sean null en other
            return ($other->hospital_id === null || $this->hospital_id === $other->hospital_id)
                && ($other->modality_id === null || $this->modality_id === $other->modality_id)
                && ($other->legal_entity_id === null || $this->legal_entity_id === $other->legal_entity_id);
        }

        // Similar para hospital, modality, legal_entity
        if ($this->hospital_id !== null && $other->hospital_id === null) {
            return ($other->doctor_id === null || $this->doctor_id === $other->doctor_id)
                && ($other->modality_id === null || $this->modality_id === $other->modality_id)
                && ($other->legal_entity_id === null || $this->legal_entity_id === $other->legal_entity_id);
        }

        if ($this->modality_id !== null && $other->modality_id === null) {
            return ($other->doctor_id === null || $this->doctor_id === $other->doctor_id)
                && ($other->hospital_id === null || $this->hospital_id === $other->hospital_id)
                && ($other->legal_entity_id === null || $this->legal_entity_id === $other->legal_entity_id);
        }

        if ($this->legal_entity_id !== null && $other->legal_entity_id === null) {
            return ($other->doctor_id === null || $this->doctor_id === $other->doctor_id)
                && ($other->hospital_id === null || $this->hospital_id === $other->hospital_id)
                && ($other->modality_id === null || $this->modality_id === $other->modality_id);
        }

        return false;
    }

    /**
     * Descripción legible del condicional
     */
    public function getDescription(): string
    {
        $parts = [];
        
        if ($this->doctor_id) {
            $doctorName = $this->doctor 
                ? 'Dr. ' . $this->doctor->first_name . ' ' . $this->doctor->last_name 
                : 'Doctor ID ' . $this->doctor_id;
            $parts[] = "Doctor: {$doctorName}";
        }
        
        if ($this->hospital_id) {
            $parts[] = "Hospital: " . ($this->hospital?->name ?? 'ID ' . $this->hospital_id);
        }
        
        if ($this->modality_id) {
            $parts[] = "Modalidad: " . ($this->modality?->name ?? 'ID ' . $this->modality_id);
        }
        
        if ($this->legal_entity_id) {
            $parts[] = "Legal Entity: " . ($this->legalEntity?->name ?? 'ID ' . $this->legal_entity_id);
        }
        
        return implode(' + ', $parts) ?: 'Todos (sin criterios específicos)';
    }

    /**
     * Descripción del tipo de acción
     */
    public function getActionDescription(): string
    {
        return match($this->action_type) {
            'adjust_quantity' => 'Ajustar cantidad a ' . $this->quantity_override,
            'add_product' => 'Agregar ' . $this->additional_quantity . ' unidad(es) adicionales',
            'exclude' => 'Excluir producto',
            'replace' => 'Reemplazar por: ' . ($this->targetProduct?->name ?? 'Producto ID ' . $this->target_product_id),
            'add_dependency' => 'Requiere ' . $this->dependency_quantity . 'x ' . ($this->targetProduct?->name ?? 'Producto ID ' . $this->target_product_id),
            default => 'Acción desconocida',
        };
    }
}