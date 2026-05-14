<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigurationRequirement extends Model
{
    protected $fillable = [
        'configuration_id', 
        'item_id', 
        'requirement_type',
        'doctor_id', 
        'hospital_id',
        'notes'
    ];

    public function configuration()
    {
        return $this->belongsTo(ChecklistConfiguration::class, 'configuration_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class); 
    }
}
