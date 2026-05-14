<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitAssembly extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_unit_id',
        'user_id',
        'status',
        'notes',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function stockUnit()
    {
        return $this->belongsTo(StockUnit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(KitAssemblyItem::class);
    }

    public function getIsPerfectAttribute()
    {
        return !$this->items()->whereColumn('quantity_found', '<', 'quantity_expected')->exists();
    }
}