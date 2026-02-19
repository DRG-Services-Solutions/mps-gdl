<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShippingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_number',
        'scheduled_surgery_id',
        'hospital_id',
        'doctor_id',
        'surgical_checklist_id',
        'hospital_modality_config_id',
        'surgery_type',
        'surgery_date',
        'billing_legal_entity_id',
        'checklist_evaluation',
        'status',
        'notes',
        'created_by',
        'confirmed_by',
        'confirmed_at',
        'sent_at',
        'surgery_started_at',
        'returned_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'surgery_date' => 'date',
        'checklist_evaluation' => 'array',
        'confirmed_at' => 'datetime',
        'sent_at' => 'datetime',
        'surgery_started_at' => 'datetime',
        'returned_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════════════════════
    // BOOT - Generar número automático
    // ═══════════════════════════════════════════════════════════

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($note) {
            if (empty($note->shipping_number)) {
                $note->shipping_number = self::generateShippingNumber();
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════════════════════

    /**
     * Cirugía programada que originó esta remisión
     */
    public function scheduledSurgery(): BelongsTo
    {
        return $this->belongsTo(ScheduledSurgery::class);
    }

    /**
     * Hospital
     */
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Doctor
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Checklist quirúrgico usado
     */
    public function surgicalChecklist(): BelongsTo
    {
        return $this->belongsTo(SurgicalChecklist::class);
    }

    /**
     * Configuración hospital-modalidad
     */
    public function hospitalModalityConfig(): BelongsTo
    {
        return $this->belongsTo(HospitalModalityConfig::class);
    }

    /**
     * Razón social que factura
     */
    public function billingLegalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class, 'billing_legal_entity_id');
    }

    /**
     * Paquetes pre-armados en esta remisión
     */
    public function packages(): HasMany
    {
        return $this->hasMany(ShippingNotePackage::class);
    }

    /**
     * Kits quirúrgicos en esta remisión
     */
    public function kits(): HasMany
    {
        return $this->hasMany(ShippingNoteKit::class);
    }

    /**
     * Todos los items (productos individuales)
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShippingNoteItem::class);
    }

    /**
     * Conceptos de renta
     */
    public function rentalConcepts(): HasMany
    {
        return $this->hasMany(ShippingNoteRentalConcept::class);
    }

    /**
     * Usuario que creó
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que confirmó
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
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

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['cancelled', 'completed']);
    }

    public function scopeByHospital(Builder $query, int $hospitalId): Builder
    {
        return $query->where('hospital_id', $hospitalId);
    }

    public function scopeByDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeByLegalEntity(Builder $query, int $legalEntityId): Builder
    {
        return $query->where('billing_legal_entity_id', $legalEntityId);
    }

    public function scopeDateRange(Builder $query, $from, $to): Builder
    {
        if ($from) {
            $query->where('surgery_date', '>=', $from);
        }
        if ($to) {
            $query->where('surgery_date', '<=', $to);
        }
        return $query;
    }

    public function scopeForToday(Builder $query): Builder
    {
        return $query->whereDate('surgery_date', today());
    }

    // ═══════════════════════════════════════════════════════════
    // GENERACIÓN DE NÚMERO
    // ═══════════════════════════════════════════════════════════

    /**
     * Generar número de remisión único
     * Formato: REM-2025-000001
     */
    public static function generateShippingNumber(): string
    {
        $year = date('Y');

        $last = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $last
            ? (int) substr($last->shipping_number, -6) + 1
            : 1;

        return 'REM-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    // ═══════════════════════════════════════════════════════════
    // VERIFICACIONES DE ESTADO
    // ═══════════════════════════════════════════════════════════

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isInSurgery(): bool
    {
        return $this->status === 'in_surgery';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === 'draft' && $this->items()->count() > 0;
    }

    public function canBeSent(): bool
    {
        return $this->status === 'confirmed';
    }

    public function canRegisterReturn(): bool
    {
        return in_array($this->status, ['sent', 'in_surgery']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'confirmed']);
    }

    // ═══════════════════════════════════════════════════════════
    // TRANSICIONES DE ESTADO
    // ═══════════════════════════════════════════════════════════

    /**
     * Confirmar remisión (draft → confirmed)
     */
    public function confirm(): void
    {
        if (!$this->canBeConfirmed()) {
            throw new \Exception('La remisión no puede ser confirmada en su estado actual.');
        }

        $this->update([
            'status' => 'confirmed',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        Log::info("Remisión {$this->shipping_number} confirmada por usuario " . auth()->id());
    }

    /**
     * Enviar material (confirmed → sent)
     */
    public function markAsSent(): void
    {
        if (!$this->canBeSent()) {
            throw new \Exception('La remisión no puede ser enviada en su estado actual.');
        }

        DB::transaction(function () {
            // Actualizar estado de todos los items
            $this->items()->where('status', 'pending')->update([
                'status' => 'sent',
                'sent_at' => now(),
                'quantity_sent' => DB::raw('quantity_required'),
            ]);

            // Actualizar paquetes
            $this->packages()->where('status', 'assigned')->update([
                'status' => 'sent',
            ]);

            // Actualizar kits
            $this->kits()->where('status', 'assigned')->update([
                'status' => 'sent',
            ]);

            // Actualizar product_units a in_surgery
            $productUnitIds = $this->items()
                ->whereNotNull('product_unit_id')
                ->pluck('product_unit_id');

            if ($productUnitIds->isNotEmpty()) {
                ProductUnit::whereIn('id', $productUnitIds)
                    ->update(['status' => 'in_surgery']);
            }

            // Actualizar paquetes pre-armados
            $packageIds = $this->packages()->pluck('pre_assembled_package_id');
            if ($packageIds->isNotEmpty()) {
                PreAssembledPackage::whereIn('id', $packageIds)
                    ->update(['status' => 'in_use']);
            }

            $this->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        });

        Log::info("Remisión {$this->shipping_number} enviada a cirugía");
    }

    /**
     * Marcar en cirugía (sent → in_surgery)
     */
    public function markInSurgery(): void
    {
        if ($this->status !== 'sent') {
            throw new \Exception('La remisión debe estar enviada para marcarla en cirugía.');
        }

        $this->items()->where('status', 'sent')->update(['status' => 'in_surgery']);
        $this->packages()->where('status', 'sent')->update(['status' => 'in_surgery']);
        $this->kits()->where('status', 'sent')->update(['status' => 'in_surgery']);

        $this->update([
            'status' => 'in_surgery',
            'surgery_started_at' => now(),
        ]);

        Log::info("Remisión {$this->shipping_number} en cirugía");
    }

    /**
     * Cancelar remisión
     */
    public function cancel(?string $reason = null): void
    {
        if (!$this->canBeCancelled()) {
            throw new \Exception('La remisión no puede ser cancelada en su estado actual.');
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'notes' => $reason
                ? ($this->notes ? $this->notes . "\n[CANCELACIÓN]: " . $reason : "[CANCELACIÓN]: " . $reason)
                : $this->notes,
        ]);

        // Liberar paquetes pre-armados
        $packageIds = $this->packages()->pluck('pre_assembled_package_id');
        if ($packageIds->isNotEmpty()) {
            PreAssembledPackage::whereIn('id', $packageIds)
                ->update(['status' => 'available']);
        }

        Log::info("Remisión {$this->shipping_number} cancelada. Razón: {$reason}");
    }

    // ═══════════════════════════════════════════════════════════
    // ESTADÍSTICAS Y TOTALES
    // ═══════════════════════════════════════════════════════════

    /**
     * Total de items en la remisión
     */
    public function getTotalItems(): int
    {
        return $this->items()->count();
    }

    /**
     * Items por origen
     */
    public function getItemsByOrigin(): array
    {
        return [
            'package' => $this->items()->where('item_origin', 'package')->count(),
            'kit' => $this->items()->where('item_origin', 'kit')->count(),
            'standalone' => $this->items()->where('item_origin', 'standalone')->count(),
            'conditional' => $this->items()->where('item_origin', 'conditional')->count(),
        ];
    }

    /**
     * Items enviados
     */
    public function getSentItems(): int
    {
        return $this->items()->where('status', 'sent')->count();
    }

    /**
     * Items retornados
     */
    public function getReturnedItems(): int
    {
        return $this->items()->where('status', 'returned')->count();
    }

    /**
     * Items usados (no regresaron)
     */
    public function getUsedItems(): int
    {
        return $this->items()->where('status', 'used')->count();
    }

    /**
     * Total facturables (excluyendo no_charge y exclude_from_invoice)
     */
    public function getBillableTotal(): float
    {
        return $this->items()
            ->where('exclude_from_invoice', false)
            ->where('billing_mode', '!=', 'no_charge')
            ->sum('total_price')
            + $this->rentalConcepts()
                ->where('exclude_from_invoice', false)
                ->sum('total_price')
            + $this->kits()
                ->where('exclude_from_invoice', false)
                ->sum('rental_price');
    }

    /**
     * Subtotal por tipo
     */
    public function getTotals(): array
    {
        $itemSales = $this->items()
            ->where('billing_mode', 'sale')
            ->where('exclude_from_invoice', false)
            ->sum('total_price');

        $itemRentals = $this->items()
            ->where('billing_mode', 'rental')
            ->where('exclude_from_invoice', false)
            ->sum('total_price');

        $kitRentals = $this->kits()
            ->where('exclude_from_invoice', false)
            ->sum('rental_price');

        $rentalConcepts = $this->rentalConcepts()
            ->where('exclude_from_invoice', false)
            ->sum('total_price');

        return [
            'sales' => (float) $itemSales,
            'item_rentals' => (float) $itemRentals,
            'kit_rentals' => (float) $kitRentals,
            'rental_concepts' => (float) $rentalConcepts,
            'total_rentals' => (float) ($itemRentals + $kitRentals + $rentalConcepts),
            'grand_total' => (float) ($itemSales + $itemRentals + $kitRentals + $rentalConcepts),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // LABELS Y BADGES
    // ═══════════════════════════════════════════════════════════

    /**
     * Labels de estado para UI
     */
    public static function getStatusLabels(): array
    {
        return [
            'draft' => 'Borrador',
            'confirmed' => 'Confirmada',
            'sent' => 'Enviada',
            'in_surgery' => 'En Cirugía',
            'returned' => 'Retornada',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public static function getStatusColors(): array
    {
        return [
            'draft' => 'gray',
            'confirmed' => 'blue',
            'sent' => 'yellow',
            'in_surgery' => 'orange',
            'returned' => 'purple',
            'completed' => 'green',
            'cancelled' => 'red',
        ];
    }

    public function getStatusColorAttribute(): string
    {
        return self::getStatusColors()[$this->status] ?? 'gray';
    }
}