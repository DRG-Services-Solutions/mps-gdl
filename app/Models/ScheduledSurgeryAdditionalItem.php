<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledSurgeryAdditionalItem extends Model
{
    protected $fillable = [
        'scheduled_surgery_id',
        'product_id',
        'instrument_id',
        'instrument_kit_id',
        'quantity',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function surgery()
    {
        return $this->belongsTo(ScheduledSurgery::class, 'scheduled_surgery_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function instrument()
    {
        return $this->belongsTo(Instrument::class);
    }

    public function instrumentKit()
    {
        return $this->belongsTo(InstrumentKit::class);
    }

    /**
     * Accesor útil para obtener el nombre sin importar el tipo
     */
    public function getItemNameAttribute()
    {
        if ($this->product_id) return $this->product->name;
        if ($this->instrument_id) return $this->instrument->name;
        if ($this->instrument_kit_id) return $this->instrumentKit->name;
        return 'N/A';
    }
}