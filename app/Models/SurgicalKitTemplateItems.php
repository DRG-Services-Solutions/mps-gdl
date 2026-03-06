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
        'item_id',
        'quantity',
    ];

    public function template()
    {
        return $this->belongsTo(SurgicalKitTemplate::class, 'surgical_kit_template_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }
}
