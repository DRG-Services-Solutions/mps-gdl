<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistConfiguration extends Model
{
    protected $fillable = [
        'surgical_checklist_id', 
        'name', 
        'is_default'
    ];

    public function checklist()
    {
        return $this->belongsTo(SurgicalChecklist::class, 'surgical_checklist_id');
    }

    public function requirements()
    {
        return $this->hasMany(ConfigurationRequirement::class, 'configuration_id');
    }
}
