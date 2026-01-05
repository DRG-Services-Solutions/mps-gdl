<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistConditional extends Model
{
    protected $fillable = [
        'checklist_item_id',
        'doctor_id',
        'hospital_id',
        'modality_id',
        'legal_entity_id',
        'quantity_override',
        'is_additional_product',
        'additional_quantity',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_additional_product' => 'boolean',
        'quantity_override' => 'integer',
        'additional_quantity' => 'integer',
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
     * MÉTODOS DE NEGOCIO
     */

    /**
     * Obtener la cantidad final considerando este condicional
     */
    public function getEffectiveQuantity(?int $baseQuantity = null): int
    {
        if ($this->is_additional_product) {
            return $this->additional_quantity ?? 0;
        }

        if ($this->quantity_override !== null) {
            return $this->quantity_override;
        }

        return $baseQuantity ?? 0;
    }

    /**
     * Verificar qué tan específico es este condicional (para debugging)
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
     * Descripción legible del condicional
     */
    public function getDescription(): string
    {
        $parts = [];

        if ($this->doctor_id) {
            $parts[] = "Doctor: {$this->doctor->name}";
        }

        if ($this->hospital_id) {
            $parts[] = "Hospital: {$this->hospital->name}";
        }

        if ($this->modality_id) {
            $parts[] = "Modalidad: {$this->modality->name}";
        }

        if ($this->legal_entity_id) {
            $parts[] = "Legal Entity: {$this->legalEntity->name}";
        }

        if (empty($parts)) {
            return "Condicional general (aplica a todos)";
        }

        return implode(" + ", $parts);
    }
}