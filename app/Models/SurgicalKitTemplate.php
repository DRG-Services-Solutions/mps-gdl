<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgicalKitTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\SurgicalKitTemplateFactory> */
    use HasFactory;
    protected $fillable = [
        
        'name',
        'code',
        'surgery_type',
        'is_active',
        'description',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(SurgicalKitTemplateItem::class);
    }
}
