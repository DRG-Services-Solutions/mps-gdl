<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ScheduledSurgery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'checklist_id',
        'hospital_id',
        'doctor_id',
        'payment_mode',
        'surgery_date',
        'patient_name',
        'surgery_notes',
        'status',
        'scheduled_by',
    ];

    protected $casts = [
        'surgery_date' => 'datetime',
    ];

    /**
     * RELACIONES
     */
    
    // Check list aplicado
    public function checklist()
    {
        return $this->belongsTo(SurgicalChecklist::class, 'checklist_id');
    }

    // Hospital
    public function hospital()
    {
        return $this->belongsTo(LegalEntity::class, 'hospital_id');
    }

    // Doctor
    public function doctor()
    {
        return $this->belongsTo(LegalEntity::class, 'doctor_id');
    }

    // Usuario que agendó
    public function scheduler()
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    // Preparación de la cirugía
    public function preparation()
    {
        return $this->hasOne(SurgeryPreparation::class);
    }

    // Remisión asociaada

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'scheduled_surgery_id');
    }

    // Product units actualmente en esta cirugía
    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class, 'current_surgery_id');
    }

    /**
     * SCOPES
     */
    
    // Cirugías programadas (futuras)
    public function scopeUpcoming(Builder $query)
    {
        return $query->where('surgery_date', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('surgery_date', 'asc');
    }

    // Cirugías de hoy
    public function scopeToday(Builder $query)
    {
        return $query->whereDate('surgery_date', today());
    }

    // Por hospital
    public function scopeForHospital(Builder $query, $hospitalId)
    {
        return $query->where('hospital_id', $hospitalId);
    }

    // Por doctor
    public function scopeForDoctor(Builder $query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    // Por rango de fechas
    public function scopeDateRange(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('surgery_date', [$startDate, $endDate]);
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Generar código único
    public static function generateCode()
    {
        $date = now()->format('Ymd');
        $random = rand(1000, 9999);
        $code = "CIR-{$date}-{$random}";

        // Verificar unicidad
        while (self::where('code', $code)->exists()) {
            $random = rand(1000, 9999);
            $code = "CIR-{$date}-{$random}";
        }

        return $code;
    }

    // Cambiar estado
    public function updateStatus($newStatus)
    {
        $this->update(['status' => $newStatus]);
    }

    // Verificar si puede ser editada
    public function canBeEdited()
    {
        return in_array($this->status, ['scheduled', 'in_preparation']);
    }

    // Verificar si puede ser cancelada
    public function canBeCancelled()
    {
        return !in_array($this->status, ['completed', 'cancelled']);
    }

    // Verificar si está lista
    public function isReady()
    {
        return $this->status === 'ready' && 
               $this->preparation && 
               $this->preparation->status === 'completed';
    }

    // Obtener items del check list con condicionales aplicados
    public function getChecklistItemsWithConditionals()
    {
        return $this->checklist->items->map(function($item) {
            $evaluation = $item->evaluateConditionals(
                $this->hospital_id, 
                $this->payment_mode
            );

            return [
                'item' => $item,
                'product' => $item->product,
                'base_quantity' => $item->quantity,
                'adjusted_quantity' => $evaluation['quantity'],
                'is_mandatory' => $evaluation['status'] === 'required',
                'is_excluded' => $evaluation['status'] === 'excluded',
                'multiplier' => $evaluation['multiplier'],
            ];
        })->filter(function($item) {
            return !$item['is_excluded'];
        });
    }
}