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
        return $this->hasMany(ChecklistConditional::class, 'checklist_item_id');
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Evaluar condicionales para hospital/doctor/modalidad específicos
    public function evaluateConditionals($legalEntityId, $paymentMode)
    {
        $conditionals = $this->conditionals()
            ->where(function($q) use ($legalEntityId, $paymentMode) {
                $q->where('legal_entity_id', $legalEntityId)
                  ->orWhere('payment_mode', $paymentMode);
            })->get();

        $quantityMultiplier = 1.0;
        $conditionResult = $this->is_mandatory ? 'required' : 'optional';

        foreach ($conditionals as $conditional) {
            // Si se excluye, retornar inmediatamente
            if ($conditional->condition_type === 'excluded') {
                return [
                    'status' => 'excluded',
                    'quantity' => 0,
                    'multiplier' => 0
                ];
            }

            // Si se vuelve obligatorio
            if ($conditional->condition_type === 'required') {
                $conditionResult = 'required';
            }

            // Aplicar multiplicador de cantidad
            if ($conditional->quantity_multiplier > $quantityMultiplier) {
                $quantityMultiplier = $conditional->quantity_multiplier;
            }
        }

        return [
            'status' => $conditionResult,
            'quantity' => ceil($this->quantity * $quantityMultiplier),
            'multiplier' => $quantityMultiplier
        ];
    }

    // Obtener cantidad ajustada por condicionales
    public function getAdjustedQuantity($legalEntityId, $paymentMode)
    {
        $evaluation = $this->evaluateConditionals($legalEntityId, $paymentMode);
        return $evaluation['quantity'];
    }
}