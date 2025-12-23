<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistConditional extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_item_id',
        'legal_entity_id',
        'payment_mode',
        'condition_type',
        'quantity_multiplier',
        'notes',
    ];

    protected $casts = [
        'quantity_multiplier' => 'decimal:2',
    ];

    /**
     * RELACIONES
     */
    
    // Item al que pertenece
    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class, 'checklist_item_id');
    }

    // Entidad legal (hospital/doctor)
    public function legalEntity()
    {
        return $this->belongsTo(LegalEntity::class, 'legal_entity_id');
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Verificar si aplica para hospital/modalidad específicos
    public function appliesTo($legalEntityId, $paymentMode)
    {
        $matchesEntity = is_null($this->legal_entity_id) || $this->legal_entity_id == $legalEntityId;
        $matchesMode = is_null($this->payment_mode) || $this->payment_mode == $paymentMode;

        return $matchesEntity && $matchesMode;
    }
}