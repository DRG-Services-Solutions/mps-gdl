<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Collection;

class SurgicalKitTemplateItemConditional extends Model
{
    protected $fillable = [
        'surgical_kit_template_item_id',
        'doctor_id',
        'hospital_id',
        'modality_id',
        'legal_entity_id',
        'action_type',
        'quantity_override',
        'additional_quantity',
        'target_product_id',
        'dependency_quantity',
        'exclude_from_invoice',
        'requires_approval',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'exclude_from_invoice' => 'boolean',
        'requires_approval'    => 'boolean',
        'quantity_override'    => 'integer',
        'additional_quantity'  => 'integer',
        'dependency_quantity'  => 'integer',
    ];

    // ═══════════════════════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════════════════════

    public function kitTemplateItem(): BelongsTo
    {
        return $this->belongsTo(SurgicalKitTemplateItems::class, 'surgical_kit_template_item_id');
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

    public function targetProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'target_product_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    public function scopeForDoctor($query, ?int $doctorId)
    {
        return $query->where(function ($q) use ($doctorId) {
            $q->whereNull('doctor_id')->orWhere('doctor_id', $doctorId);
        });
    }

    public function scopeForHospital($query, ?int $hospitalId)
    {
        return $query->where(function ($q) use ($hospitalId) {
            $q->whereNull('hospital_id')->orWhere('hospital_id', $hospitalId);
        });
    }

    public function scopeForModality($query, ?int $modalityId)
    {
        return $query->where(function ($q) use ($modalityId) {
            $q->whereNull('modality_id')->orWhere('modality_id', $modalityId);
        });
    }

    public function scopeForLegalEntity($query, ?int $legalEntityId)
    {
        return $query->where(function ($q) use ($legalEntityId) {
            $q->whereNull('legal_entity_id')->orWhere('legal_entity_id', $legalEntityId);
        });
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS DE NEGOCIO
    // ═══════════════════════════════════════════════════════════

    public function getEffectiveQuantity(?int $baseQuantity = null): int
    {
        return match ($this->action_type) {
            'add_product'    => $this->additional_quantity ?? 0,
            'add_dependency' => $this->dependency_quantity ?? 1,
            'adjust_quantity'=> $this->quantity_override ?? ($baseQuantity ?? 0),
            default          => $baseQuantity ?? 0,
        };
    }

    public function getSpecificityLevel(): int
    {
        return (int) ($this->doctor_id !== null)
             + (int) ($this->hospital_id !== null)
             + (int) ($this->modality_id !== null)
             + (int) ($this->legal_entity_id !== null);
    }

    public function getCriteriaHash(): string
    {
        return md5(implode('|', [
            $this->doctor_id       ?? 'null',
            $this->hospital_id     ?? 'null',
            $this->modality_id     ?? 'null',
            $this->legal_entity_id ?? 'null',
        ]));
    }

    public function hasSameCriteriaAs(self $other): bool
    {
        return $this->doctor_id       === $other->doctor_id
            && $this->hospital_id     === $other->hospital_id
            && $this->modality_id     === $other->modality_id
            && $this->legal_entity_id === $other->legal_entity_id;
    }

    public function getDescription(): string
    {
        $parts = [];

        if ($this->doctor_id && $this->doctor) {
            $parts[] = 'Doctor: Dr. ' . $this->doctor->first_name . ' ' . $this->doctor->last_name;
        }
        if ($this->hospital_id) {
            $parts[] = 'Hospital: ' . ($this->hospital?->name ?? 'ID ' . $this->hospital_id);
        }
        if ($this->modality_id) {
            $parts[] = 'Modalidad: ' . ($this->modality?->name ?? 'ID ' . $this->modality_id);
        }
        if ($this->legal_entity_id) {
            $parts[] = 'Razón Social: ' . ($this->legalEntity?->name ?? 'ID ' . $this->legal_entity_id);
        }

        return implode(' + ', $parts) ?: 'Todos (sin criterios específicos)';
    }

    public function getActionDescription(): string
    {
        return match ($this->action_type) {
            'adjust_quantity' => 'Ajustar cantidad a ' . $this->quantity_override,
            'add_product'     => 'Agregar ' . $this->additional_quantity . ' unidad(es) adicionales',
            'exclude'         => 'Excluir instrumental',
            'replace'         => 'Reemplazar por: ' . ($this->targetProduct?->name ?? 'Producto ID ' . $this->target_product_id),
            'add_dependency'  => 'Requiere ' . $this->dependency_quantity . 'x ' . ($this->targetProduct?->name ?? 'Producto ID ' . $this->target_product_id),
            default           => 'Acción desconocida',
        };
    }

    /**
     * Detectar conflictos con otros condicionales del mismo item
     */
    public function detectConflicts(): array
    {
        $conflicts = new Collection();
        $warnings  = [];

        $others = self::where('surgical_kit_template_item_id', $this->surgical_kit_template_item_id)
            ->where('id', '!=', $this->id ?? 0)
            ->get();

        foreach ($others as $other) {
            if ($this->hasSameCriteriaAs($other)) {
                $conflicts->push([
                    'type'        => $this->action_type === $other->action_type ? 'exact_duplicate' : 'action_conflict',
                    'severity'    => 'high',
                    'message'     => $this->action_type === $other->action_type
                        ? 'Condicional duplicado con los mismos criterios'
                        : 'Mismo criterio pero diferente acción',
                    'conditional' => $other,
                ]);
            }
        }

        return [
            'has_conflict' => $conflicts->isNotEmpty(),
            'conflicts'    => $conflicts,
            'warnings'     => $warnings,
        ];
    }
}