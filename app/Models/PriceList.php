<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'hospital_id',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ═══════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function items()
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForHospital(Builder $query, $hospitalId)
    {
        return $query->where('hospital_id', $hospitalId);
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhereHas('hospital', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              });
        });
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    /**
     * Total de productos en la lista
     */
    public function getProductCountAttribute(): int
    {
        return $this->relationLoaded('items')
            ? $this->items->count()
            : $this->items()->count();
    }

    /**
     * Label para mostrar: "Lista - Hospital"
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} — {$this->hospital->name}";
    }

    // ═══════════════════════════════════════════
    // MÉTODOS DE NEGOCIO
    // ═══════════════════════════════════════════

    /**
     * Activar esta lista y desactivar cualquier otra del mismo hospital.
     * Garantiza la regla: solo 1 activa por hospital.
     */
    public function activate(): void
    {
        // Desactivar todas las del mismo hospital
        static::where('hospital_id', $this->hospital_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        $this->update(['is_active' => true]);
    }

    /**
     * Desactivar esta lista
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Obtener precio de un producto en esta lista.
     * Retorna null si el producto no está en la lista.
     */
    public function getPriceFor(int $productId): ?float
    {
        $item = $this->relationLoaded('items')
            ? $this->items->firstWhere('product_id', $productId)
            : $this->items()->where('product_id', $productId)->first();

        return $item?->unit_price;
    }

    /**
     * Obtener la lista activa de un hospital (helper estático).
     * Útil para la segunda etapa cuando se apliquen precios en cirugías.
     */
    public static function getActiveForHospital(int $hospitalId): ?self
    {
        return static::active()
            ->forHospital($hospitalId)
            ->with('items')
            ->first();
    }

    /**
     * Generar código automático: PL-001, PL-002, etc.
     */
    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->value('code');

        if ($last && preg_match('/PL-(\d+)/', $last, $matches)) {
            $next = intval($matches[1]) + 1;
        } else {
            $next = 1;
        }

        return 'PL-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
