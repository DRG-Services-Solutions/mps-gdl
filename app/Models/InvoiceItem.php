<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'product_code',
        'product_name',
        'product_unit_ids',
        'quantity',
        'unit_price',
        'subtotal',
        'iva',
        'total',
        'notes',
    ];

    protected $casts = [
        'product_unit_ids' => 'array',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * RELACIONES
     */
    
    // Remisión a la que pertenece
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    // Producto
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Calcular totales del item
    public function calculateTotals()
    {
        $subtotal = $this->quantity * $this->unit_price;
        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;

        $this->update([
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total,
        ]);
    }

    // Obtener EPCs incluidos
    public function getEpcs()
    {
        if (empty($this->product_unit_ids)) {
            return collect();
        }

        return ProductUnit::whereIn('id', $this->product_unit_ids)->get();
    }
}