<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'specialty_id', 
        'subcategory_id',
        'manufacturer_id',
        'name',
        'code',
        'model',
        'serial_number',
        'description',
        'rfid_enabled',
        'rfid_tag_id',
        'requires_sterilization',
        'is_consumable',
        'is_single_use',
        'unit_cost',
        'minimum_stock',
        'current_stock',
        'storage_location',
        'expiration_date',
        'lot_number',
        'specifications',
        'status',
    ];

    protected $casts = [
        'rfid_enabled' => 'boolean',
        'requires_sterilization' => 'boolean',
        'is_consumable' => 'boolean',
        'is_single_use' => 'boolean',
        'expiration_date' => 'date',
        'unit_cost' => 'decimal:2',
    ];

    // Relaciones corregidas
    public function category() {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function medicalSpecialty() {
        return $this->belongsTo(MedicalSpecialty::class, 'specialty_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }
    
    public function manufacturer() 
    {
        return $this->belongsTo(Manufacturer::class);    
    }
    
    }
    