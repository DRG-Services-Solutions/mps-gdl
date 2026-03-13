<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgicalKitTemplateItemConditional extends Model
{
    protected $fillable = [
        'surgical_kit_template_item_id',
        'doctor_id',
        'hospital_id',
        'action_type',
        'quantity_override',
        'target_product_id',
        'dependency_quantity',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity_override'   => 'integer',
        'dependency_quantity' => 'integer',
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

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS DE NEGOCIO
    // ═══════════════════════════════════════════════════════════

    public function getSpecificityLevel(): int
    {
        return (int) ($this->doctor_id !== null)
             + (int) ($this->hospital_id !== null);
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

        return implode(' + ', $parts) ?: 'Todos (sin criterios específicos)';
    }

    public function getActionDescription(): string
    {
        return match ($this->action_type) {
            'adjust_quantity' => 'Ajustar cantidad a ' . $this->quantity_override,
            'replace'         => 'Reemplazar por: ' . ($this->targetProduct?->name ?? 'Producto ID ' . $this->target_product_id),
            'add_dependency'  => 'Requiere ' . $this->dependency_quantity . 'x ' . ($this->targetProduct?->name ?? 'Producto ID ' . $this->target_product_id),
            default           => 'Acción desconocida',
        };
    }
}