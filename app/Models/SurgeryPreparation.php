<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgeryPreparation extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_surgery_id',
        'pre_assembled_package_id',
        'status',
        'started_at',
        'completed_at',
        'prepared_by',
        'verified_by',
        'notes',
        
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * RELACIONES
     */
    
    public function surgery()
    {
        return $this->belongsTo(ScheduledSurgery::class, 'scheduled_surgery_id');
    }
    // Cirugía a preparar
    public function scheduledSurgery()
    {
        return $this->belongsTo(ScheduledSurgery::class, 'scheduled_surgery_id');
    }

    // Paquete pre-armado utilizado
    public function preAssembledPackage()
    {
        return $this->belongsTo(PreAssembledPackage::class, 'pre_assembled_package_id');
    }

    // Usuario que preparó
    public function preparer()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    // Usuario que verificó
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Items de la preparación
    public function items()
    {
        return $this->hasMany(SurgeryPreparationItem::class, 'preparation_id');
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Iniciar preparación
    public function start($userId)
    {
        $this->update([
            'status' => 'comparing',
            'started_at' => now(),
            'prepared_by' => $userId,
        ]);
    }

    // Completar preparación
    public function complete($userId)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'verified_by' => $userId,
        ]);

        // Actualizar estado de la cirugía
        $this->scheduledSurgery->updateStatus('ready');
    }

    // Verificar si está completa
    public function isComplete()
    {
        return $this->items()
            ->where('status', '!=', 'complete')
            ->where('is_mandatory', true)
            ->count() === 0;
    }

    // Obtener resumen de faltantes
    public function getMissingSummary()
    {
        return $this->items()
            ->where('quantity_missing', '>', 0)
            ->with('product', 'storageLocation')
            ->get();
    }

    // Calcular porcentaje de completitud
    public function getCompletenessPercentage()
    {
        $totalItems = $this->items()->count();
        if ($totalItems === 0) return 0;

        $completeItems = $this->items()->where('status', 'complete')->count();
        return round(($completeItems / $totalItems) * 100, 2);
    }
}