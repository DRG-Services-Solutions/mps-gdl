<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalSpecialty extends Model

{
    use HasFactory;

    protected $fillable = ['name', 'description'];
    protected $table = 'medical_specialties'; 

    public function products()
    {
        // Se relaciona con Product usando la foreign key 'specialty_id' (como definimos antes)
        return $this->hasMany(Product::class, 'specialty_id'); 
    }
}
