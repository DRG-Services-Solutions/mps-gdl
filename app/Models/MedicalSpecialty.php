<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalSpecialty extends Model

{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_active',];
    protected $table = 'medical_specialties'; 

    public function products()
    {
        return $this->hasMany(Product::class, 'specialty_id'); 
    }
}
