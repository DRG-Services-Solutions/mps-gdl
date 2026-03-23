<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgicalKit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'surgery_type',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ═══════════════════════════════════════════════════════════
    // BOOT — Auto-generar código
    // ═══════════════════════════════════════════════════════════

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kit) {
            if (empty($kit->code)) {
                $kit->code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $nextNumber = $last ? $last->id + 1 : 1;
        return 'KIT-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    // ═══════════════════════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════════════════════

    public function items(): HasMany
    {
        return $this->hasMany(SurgicalKitItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS DE NEGOCIO
    // ═══════════════════════════════════════════════════════════

    /**
     * Verificar disponibilidad de stock para todos los items del kit
     */
    public function checkAvailability(): array
    {
        $this->loadMissing('items.product');
        $results = [];
        $allAvailable = true;

        foreach ($this->items as $item) {
            $available = ProductUnit::where('product_id', $item->product_id)
                ->where('status', 'available')
                ->count();

            $isAvailable = $available >= $item->quantity;
            if (!$isAvailable) {
                $allAvailable = false;
            }

            $results[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'N/A',
                'product_code' => $item->product->code ?? 'N/A',
                'required' => $item->quantity,
                'available' => $available,
                'is_available' => $isAvailable,
                'missing' => max(0, $item->quantity - $available),
            ];
        }

        return [
            'items' => $results,
            'all_available' => $allAvailable,
            'total_required' => collect($results)->sum('required'),
            'total_available' => collect($results)->sum('available'),
        ];
    }

    /**
     * Obtener ProductUnits disponibles para cada item del kit (FEFO/FIFO)
     */
    public function getAvailableProductUnits(): array
    {
        $this->loadMissing('items');
        $result = [];

        foreach ($this->items as $item) {
            $units = ProductUnit::where('product_id', $item->product_id)
                ->where('status', 'available')
                ->orderByRaw('CASE WHEN expiration_date IS NOT NULL THEN 0 ELSE 1 END')
                ->orderBy('expiration_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->limit($item->quantity)
                ->get();

            $result[] = [
                'product_id' => $item->product_id,
                'required_quantity' => $item->quantity,
                'units' => $units,
            ];
        }

        return $result;
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
