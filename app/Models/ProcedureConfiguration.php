<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Procedure;
use App\Models\ConfigurationRequirement;    


class ProcedureConfiguration extends Model
{
    protected $fillable = ['procedure_id', 'name', 'is_default'];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    /**
     * Obtiene todas las filas (reglas) de esta configuración.
     */
    public function requirements()
    {
        return $this->hasMany(ConfigurationRequirement::class, 'configuration_id');
    }
}
