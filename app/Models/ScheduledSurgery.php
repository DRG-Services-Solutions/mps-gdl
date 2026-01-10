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
    'hospital_modality_config_id', 
    'doctor_id',
    'surgery_datetime',              
    'patient_name',
    'surgery_notes',
    'status',
    'scheduled_by',
    'hospital_id',
];

    protected $casts = [
        'surgery_datetime' => 'datetime',  
    ];

    /**
     * RELACIONES
     */
    
    // Check list aplicado
    public function checklist()
    {
        return $this->belongsTo(SurgicalChecklist::class, 'checklist_id');
    }

   

    // Doctor
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
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
        return $query->where('surgery_datetime', '>=', now()) 
            ->where('status', 'scheduled')
            ->orderBy('surgery_datetime', 'asc');  
    }

    // Cirugías de hoy
    public function scopeToday(Builder $query)
    {
        return $query->whereDate('surgery_datetime', today());
    }

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
        return $query->whereBetween('surgery_datetime', [$startDate, $endDate]);
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
        $results = [];
        // 1. Cargamos los items base del checklist
        $baseItems = $this->checklist->items()->with('product')->get();

        foreach ($baseItems as $item) {
            // 2. Buscamos si este item específico tiene una regla para ESTA cirugía
            $conditional = ChecklistConditional::where('checklist_item_id', $item->id)
                ->forDoctor($this->doctor_id)
                ->forHospital($this->hospital_id)
                ->forModality($this->modality_id)
                ->first();

            // 3. Determinamos la cantidad final
            $finalQuantity = $conditional 
                ? $conditional->getEffectiveQuantity($item->quantity) 
                : $item->quantity;

            // Si la cantidad final es > 0, lo agregamos a la lista de preparación
            if ($finalQuantity > 0) {
                $results[] = [
                    'item' => $item,
                    'adjusted_quantity' => $finalQuantity,
                    'is_mandatory' => $item->is_mandatory, // O la lógica que prefieras
                    'source' => $conditional ? 'conditional' : 'base'
                ];
            }
        }

        // 4. (Opcional) Agregar productos adicionales que no están en el checklist base 
        // pero sí en los condicionales para este doctor/hospital
        $extraProducts = ChecklistConditional::additionalProducts()
            ->whereHas('checklistItem', function($q) {
                $q->where('checklist_id', $this->checklist_id);
            })
            ->forDoctor($this->doctor_id)
            ->get();

        foreach ($extraProducts as $extra) {
            $results[] = [
                'item' => $extra->checklistItem,
                'adjusted_quantity' => $extra->additional_quantity,
                'is_mandatory' => false,
                'source' => 'extra'
            ];
        }

        return $results;
    }

    //Configuraciones de hospitales
    public function hospitalModalityConfig()
    {
        return $this->belongsTo(HospitalModalityConfig::class);
    }
    public function hospital()
    {
        return $this->hasOneThrough(
            Hospital::class,
            HospitalModalityConfig::class,
            'id',                           // FK en hospital_modality_configs
            'id',                           // FK en hospitals
            'hospital_modality_config_id',  // Local key en scheduled_surgeries
            'hospital_id'                   // FK en hospital_modality_configs
        );
    }

    public function modality()
    {
        return $this->hasOneThrough(
            Modality::class,
            HospitalModalityConfig::class,
            'id',                           // FK en hospital_modality_configs
            'id',                           // FK en modalities
            'hospital_modality_config_id',  // Local key en scheduled_surgeries
            'modality_id'                   // FK en hospital_modality_configs
        );
    }


    public function surgeryChecklist()
    {
        return $this->belongsTo(SurgicalChecklist::class);
    }


}