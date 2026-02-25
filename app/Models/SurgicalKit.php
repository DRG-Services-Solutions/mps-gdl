<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgicalKit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'surgery_type',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySurgeryType($query, $surgeryType)
    {
        return $query->where('surgery_type', $surgeryType);
    }

    // ═══════════════════════════════════════════════════════════
    // ATRIBUTOS CALCULADOS
    // ═══════════════════════════════════════════════════════════

    public function getTotalProductsAttribute(): int
    {
        return $this->items()->count();
    }

    public function getTotalPiecesAttribute(): int
    {
        return $this->items()->sum('quantity');
    }

    // ═══════════════════════════════════════════════════════════
    // VALIDACIÓN DE STOCK
    // ═══════════════════════════════════════════════════════════

    /**
     * Verificar disponibilidad de stock para todos los productos del kit
     */
    public function checkAvailability(): array
    {
        $availability = [];
        $allAvailable = true;

        foreach ($this->items as $item) {
            $availableStock = ProductUnit::where('product_id', $item->product_id)
                ->where('status', 'available')
                ->count(); 

            $isAvailable = $availableStock >= $item->quantity;
            $missing = $isAvailable ? 0 : ($item->quantity - $availableStock);

            if (!$isAvailable) {
                $allAvailable = false;
            }

            $availability[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_code' => $item->product->code,
                'required_quantity' => $item->quantity,
                'available_quantity' => $availableStock,
                'is_available' => $isAvailable,
                'missing_quantity' => $missing,
            ];
        }

        return [
            'all_available' => $allAvailable,
            'items' => $availability,
            'total_required' => $this->total_pieces,
            'total_available' => collect($availability)->sum('available_quantity'),
        ];
    }

    public function hasCompleteStock(): bool
    {
        $check = $this->checkAvailability();
        return $check['all_available'];
    }

    public function getMissingProducts(): array
    {
        $check = $this->checkAvailability();
        return collect($check['items'])
            ->filter(fn($item) => !$item['is_available'])
            ->values()
            ->toArray();
    }

    /**
     * Obtener ProductUnits específicos disponibles para este kit
     */
    public function getAvailableProductUnits(): array
    {
        $productUnits = [];

        foreach ($this->items as $item) {
            $units = ProductUnit::where('product_id', $item->product_id)
                ->where('status', 'available')
                ->with(['product', 'legalEntity', 'subWarehouse'])
                ->get();

            $totalQuantity = $units->count();

            $productUnits[] = [
                'product' => $item->product,
                'required_quantity' => $item->quantity,
                'available_quantity' => $totalQuantity,
                'units' => $units,
                'sufficient' => $totalQuantity >= $item->quantity,
            ];
        }

        return $productUnits;
    }

    // ═══════════════════════════════════════════════════════════
    // GENERACIÓN DE CÓDIGO
    // ═══════════════════════════════════════════════════════════

    public static function generateCode(): string
    {
        $lastKit = self::withTrashed()->latest('id')->first();
        $nextNumber = $lastKit ? $lastKit->id + 1 : 1;
        
        return 'KIT-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kit) {
            if (!$kit->code) {
                $kit->code = self::generateCode();
            }
        });
    }
}