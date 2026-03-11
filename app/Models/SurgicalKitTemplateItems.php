<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgicalKitTemplateItems extends Model
{
    /** @use HasFactory<\Database\Factories\SurgicalKitTemplateItemsFactory> */
    use HasFactory;
    protected $fillable = [
        'surgical_kit_template_id',
        'product_id',               
        'quantity_required',
        'is_mandatory',
        'notes',
    ];

    public function template()
    {
        return $this->belongsTo(SurgicalKitTemplate::class, 'surgical_kit_template_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function conditionals()
    {
        return $this->hasMany(SurgicalKitTemplateItemConditional::class, 'surgical_kit_template_item_id');
    }
}
