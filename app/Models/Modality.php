<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modality extends Model
{
    protected $fillable = 
    [
        'name',
    ];

    protected $table = 'modalities';


    //relaciones a hospitales
    public function hospitals()
    {
        return $this->belongsToMany(Hospital::class, 'hospital_modality_configs');
    }
}
