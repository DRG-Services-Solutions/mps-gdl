<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ShippingNotePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_note_id',
        'pre_assembled_package_id',
        'surgical_checklist_id',
        'comparison_snapshot',
        'status',
        'notes',
    ];

    protected $casts = [
        'comparison_snapshot' => 'array',
    ];

    // ═══════════════════════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════════════════════

    /**
     * Remisión a la que pertenece
     */
    public function shippingNote(): BelongsTo
    {
        return $this->belongsTo(ShippingNote::class);
    }

    /**
     * Paquete pre-armado físico
     */
    public function preAssembledPackage(): BelongsTo
    {
        return $this->belongsTo(PreAssembledPackage::class);
    }

    /**
     * Checklist contra el que se comparó
     */
    public function surgicalChecklist(): BelongsTo
    {
        return $this->belongsTo(SurgicalChecklist::class);
    }

    /**
     * Items de la remisión que vienen de este paquete
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShippingNoteItem::class);
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    public function scopeAssigned(Builder $query): Builder
    {
        return $query->where('status', 'assigned');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    public function scopeInSurgery(Builder $query): Builder
    {
        return $query->where('status', 'in_surgery');
    }

    public function scopeReturned(Builder $query): Builder
    {
        return $query->where('status', 'returned');
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS DE COMPARACIÓN
    // ═══════════════════════════════════════════════════════════

    /**
     * Generar snapshot de comparación entre checklist evaluado y contenido real del paquete
     *
     * @param array $evaluatedItems Items del checklist ya evaluados con condicionales
     * @return array
     */
    public function generateComparisonSnapshot(array $evaluatedItems): array
    {
        $package = $this->preAssembledPackage;
        $packageContents = $package->contents()
            ->with('product')
            ->get()
            ->groupBy('product_id');

        $comparisonItems = [];
        $totalRequired = 0;
        $totalAvailable = 0;

        foreach ($evaluatedItems as $evalItem) {
            $productId = $evalItem['product_id'];
            $required = $evalItem['adjusted_quantity'] ?? $evalItem['final_quantity'] ?? 0;
            $availableInPackage = isset($packageContents[$productId])
                ? $packageContents[$productId]->count()
                : 0;

            $totalRequired += $required;
            $totalAvailable += min($availableInPackage, $required);

            $comparisonItems[] = [
                'product_id' => $productId,
                'product_name' => $evalItem['product_name'] ?? 'N/A',
                'required' => $required,
                'available_in_package' => $availableInPackage,
                'missing' => max(0, $required - $availableInPackage),
                'is_complete' => $availableInPackage >= $required,
            ];
        }

        $snapshot = [
            'completeness_percentage' => $totalRequired > 0
                ? round(($totalAvailable / $totalRequired) * 100, 2)
                : 100,
            'total_required' => $totalRequired,
            'total_available' => $totalAvailable,
            'items' => $comparisonItems,
        ];

        $this->update(['comparison_snapshot' => $snapshot]);

        return $snapshot;
    }

    /**
     * Obtener porcentaje de completitud del snapshot
     */
    public function getCompletenessPercentage(): float
    {
        return $this->comparison_snapshot['completeness_percentage'] ?? 0;
    }

    /**
     * Obtener items faltantes del snapshot
     */
    public function getMissingItems(): array
    {
        if (!$this->comparison_snapshot || !isset($this->comparison_snapshot['items'])) {
            return [];
        }

        return collect($this->comparison_snapshot['items'])
            ->filter(fn($item) => !$item['is_complete'])
            ->values()
            ->toArray();
    }

    /**
     * ¿El paquete cubre todo lo requerido?
     */
    public function isComplete(): bool
    {
        return $this->getCompletenessPercentage() >= 100;
    }

    // ═══════════════════════════════════════════════════════════
    // ESTADÍSTICAS
    // ═══════════════════════════════════════════════════════════

    /**
     * Total de items de este paquete en la remisión
     */
    public function getTotalItems(): int
    {
        return $this->items()->count();
    }

    /**
     * Items retornados de este paquete
     */
    public function getReturnedItems(): int
    {
        return $this->items()->where('status', 'returned')->count();
    }

    /**
     * Items usados de este paquete
     */
    public function getUsedItems(): int
    {
        return $this->items()->where('status', 'used')->count();
    }

    // ═══════════════════════════════════════════════════════════
    // LABELS
    // ═══════════════════════════════════════════════════════════

    public static function getStatusLabels(): array
    {
        return [
            'assigned' => 'Asignado',
            'sent' => 'Enviado',
            'in_surgery' => 'En Cirugía',
            'returned' => 'Retornado',
            'reviewed' => 'Revisado',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }
}