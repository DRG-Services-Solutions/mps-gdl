<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity_ordered',
        'quantity_received',
        'unit_price',
        'subtotal',
        'product_code',
        'product_name',
        'description',
    ];

    protected $casts = [
        'quantity_ordered' => 'integer',
        'quantity_received' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getPendingQuantityAttribute()
    {
        return $this->quantity_ordered - $this->quantity_received;
    }

    public function isFullyReceived(): bool
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Guardar snapshot del producto
            if ($item->product) {
                $item->product_code = $item->product->code;
                $item->product_name = $item->product->name;
                $item->description = $item->product->description;
            }
            
            // Calcular subtotal
            $item->subtotal = $item->quantity_ordered * $item->unit_price;
        });

        static::updating(function ($item) {
            $item->subtotal = $item->quantity_ordered * $item->unit_price;
        });
    }
}