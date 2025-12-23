<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SurgicalChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'surgery_type',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * RELACIONES
     */
    
    // Items del check list
    public function items()
    {
        return $this->hasMany(ChecklistItem::class, 'checklist_id')
            ->orderBy('order');
    }

    // Paquetes pre-armados de este tipo
    public function preAssembledPackages()
    {
        return $this->hasMany(PreAssembledPackage::class, 'surgery_checklist_id');
    }

    // Cirugías que usan este check list
    public function scheduledSurgeries()
    {
        return $this->hasMany(ScheduledSurgery::class, 'checklist_id');
    }

    /**
     * SCOPES
     */
    
    // Solo check lists activos
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active');
    }

    // Buscar por tipo de cirugía
    public function scopeBySurgeryType(Builder $query, string $type)
    {
        return $query->where('surgery_type', $type);
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Aplicar check list a una cirugía específica con condicionales
    public function applyToSurgery($legalEntityId, $surgeryDate, $paymentMode, $userId)
    {
        // Crear cirugía agendada
        $surgery = ScheduledSurgery::create([
            'code' => $this->generateSurgeryCode(),
            'checklist_id' => $this->id,
            'hospital_id' => $legalEntityId,
            'doctor_id' => $legalEntityId, // Ajustar según tu lógica
            'payment_mode' => $paymentMode,
            'surgery_date' => $surgeryDate,
            'scheduled_by' => $userId,
        ]);

        return $surgery;
    }

    // Generar código único para cirugía
    private function generateSurgeryCode()
    {
        $date = now()->format('Ymd');
        $random = rand(1000, 9999);
        return "CIR-{$date}-{$random}";
    }

    // Calcular cantidad de items
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    // Verificar si está completo
    public function isComplete()
    {
        return $this->items()->count() > 0;
    }
}