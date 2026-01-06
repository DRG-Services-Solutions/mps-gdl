<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_id',
        'product_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * RELACIONES
     */
    
    // Check list al que pertenece
    public function checklist()
    {
        return $this->belongsTo(SurgicalChecklist::class, 'checklist_id');
    }

    // Producto asociado
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Condicionales de este item
    public function conditionals()
    {
        return $this->hasMany(ChecklistConditional::class);
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Evaluar condicionales para hospital/doctor/modalidad específicos
    public function evaluateConditionals($params)
    {
        $hospitalId = $params['hospital_id'] ?? $params->hospital_id ?? null;
        $doctorId = $params['doctor_id'] ?? $params->doctor_id ?? null;
        $modalityId = $params['modality_id'] ?? $params->modality_id ?? null;
        $legalEntityId = $params['legal_entity_id'] ?? $params->legal_entity_id ?? null;

        $matchingConditionals = $this->conditionals->filter(function ($c) use ($hospitalId, $doctorId, $modalityId, $legalEntityId) {
            return ($c->hospital_id == $hospitalId || $c->doctor_id == $doctorId || 
                    $c->modality_id == $modalityId || $c->legal_entity_id == $legalEntityId);
        });

        $finalQuantity = $this->quantity;
        $status = 'optional';

        foreach ($matchingConditionals as $conditional) {
            if ($conditional->quantity_override !== null) {
                $finalQuantity = $conditional->quantity_override;
            } elseif ($conditional->is_additional_product) {
                $finalQuantity += $conditional->additional_quantity;
            }

            if (isset($conditional->condition_type) && $conditional->condition_type === 'excluded') {
                return ['status' => 'excluded', 'quantity' => 0];
            }
        }

        return [
            'status' => $status,
            'quantity' => $finalQuantity,
            'is_modified' => $finalQuantity != $this->quantity
        ];
    }

    // Obtener cantidad ajustada por condicionales
    public function getAdjustedQuantity($legalEntityId, $paymentMode)
    {
        $evaluation = $this->evaluateConditionals($legalEntityId, $paymentMode);
        return $evaluation['quantity'];
    }
}