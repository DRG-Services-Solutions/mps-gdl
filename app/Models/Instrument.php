<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Instrument extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'name',
        'code',
        'category_id',
        'product_id',
        'kit_id',
        'depends_on_id',
        'status',
        'notes',
    ];

    // ═══════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════

    public function category()
    {
        return $this->belongsTo(InstrumentCategory::class, 'category_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function kit()
    {
        return $this->belongsTo(InstrumentKit::class, 'kit_id');
    }

    /**
     * Instrumento del que depende (ej: mango necesita hoja)
     */
    public function dependsOn()
    {
        return $this->belongsTo(Instrument::class, 'depends_on_id');
    }

    /**
     * Instrumentos que dependen de este
     */
    public function dependents()
    {
        return $this->hasMany(Instrument::class, 'depends_on_id');
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeAvailable(Builder $query)
    {
        return $query->where('status', 'available');
    }

    public function scopeInKit(Builder $query)
    {
        return $query->whereNotNull('kit_id');
    }

    public function scopeLoose(Builder $query)
    {
        return $query->whereNull('kit_id');
    }

    public function scopeByCategory(Builder $query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('serial_number', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    /**
     * Label completo: "PK-001 — Pinza Kelly 14cm"
     */
    public function getFullLabelAttribute(): string
    {
        return "{$this->serial_number} — {$this->name}";
    }

    /**
     * ¿Está asignado a un kit?
     */
    public function getIsInKitAttribute(): bool
    {
        return $this->kit_id !== null;
    }

    /**
     * ¿Tiene dependencias?
     */
    public function getHasDependentsAttribute(): bool
    {
        return $this->relationLoaded('dependents')
            ? $this->dependents->isNotEmpty()
            : $this->dependents()->exists();
    }

    /**
     * Color del status para la vista
     */
    public function getStatusColorAttribute(): array
    {
        return match($this->status) {
            'available'   => ['classes' => 'bg-green-100 text-green-800', 'label' => 'Disponible'],
            'in_kit'      => ['classes' => 'bg-blue-100 text-blue-800', 'label' => 'En Kit'],
            'in_surgery'  => ['classes' => 'bg-purple-100 text-purple-800', 'label' => 'En Cirugía'],
            'maintenance' => ['classes' => 'bg-yellow-100 text-yellow-800', 'label' => 'Mantenimiento'],
            'retired'     => ['classes' => 'bg-gray-100 text-gray-800', 'label' => 'Retirado'],
            'lost'        => ['classes' => 'bg-red-100 text-red-800', 'label' => 'Extraviado'],
            default       => ['classes' => 'bg-gray-100 text-gray-800', 'label' => $this->status],
        };
    }

    /**
     * Color de la condición
     */
    public function getConditionColorAttribute(): array
    {
        return match($this->condition) {
            'good'    => ['classes' => 'bg-green-100 text-green-800', 'label' => 'Bueno'],
            'fair'    => ['classes' => 'bg-yellow-100 text-yellow-800', 'label' => 'Regular'],
            'damaged' => ['classes' => 'bg-red-100 text-red-800', 'label' => 'Dañado'],
            default   => ['classes' => 'bg-gray-100 text-gray-800', 'label' => $this->condition],
        };
    }

    // ═══════════════════════════════════════════
    // MÉTODOS DE NEGOCIO
    // ═══════════════════════════════════════════

    /**
     * ¿Se puede asignar a un kit?
     */
    public function canBeAssignedToKit(): bool
    {
        return in_array($this->status, ['available']) && $this->kit_id === null;
    }

    /**
     * Marcar como en mantenimiento
     */
    public function sendToMaintenance(string $notes = null): void
    {
        $this->update([
            'status' => 'maintenance',
            'notes' => $notes ?? $this->notes,
        ]);

        // Si estaba en un kit, actualizar el estado del kit
        if ($this->kit) {
            $this->kit->refreshStatus();
        }
    }

    /**
     * Marcar como disponible (regresó de mantenimiento)
     */
    public function returnFromMaintenance(): void
    {
        $newStatus = $this->kit_id ? 'in_kit' : 'available';
        $this->update(['status' => $newStatus]);

        if ($this->kit) {
            $this->kit->refreshStatus();
        }
    }

    /**
     * Marcar como extraviado
     */
    public function markAsLost(string $notes = null): void
    {
        $this->update([
            'status' => 'lost',
            'notes' => $notes ?? $this->notes,
        ]);

        if ($this->kit) {
            $this->kit->refreshStatus();
        }
    }

    /**
     * Retirar instrumento
     */
    public function retire(): void
    {
        // Remover del kit si aplica
        if ($this->kit_id) {
            $this->update(['kit_id' => null]);
        }

        $this->update(['status' => 'retired']);
    }
}