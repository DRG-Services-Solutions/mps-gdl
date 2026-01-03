<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalModalityConfig extends Model
{
    protected $fillable = [
        'hospital_id',
        'modality_id',
        'legal_entity_id',
        'unit_price',
    ];

    protected $table = 'hospital_modality_configs';

    //relaciones inversas
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function modality()
    {
        return $this->belongsTo(Modality::class);
    }

    public function legalEntity()
    {
        return $this->belongsTo(LegalEntity::class);
    }
}
