<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

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

    // ==================== RELACIONES ====================
    
    /**
     * Check list aplicado
     */
    public function checklist()
    {
        return $this->belongsTo(SurgicalChecklist::class, 'checklist_id');
    }

    /**
     * Doctor asignado
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * Usuario que agendó
     */
    public function scheduler()
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    /**
     * Preparación de la cirugía
     */
    public function preparation()
    {
        return $this->hasOne(SurgeryPreparation::class, 'scheduled_surgery_id');    
    }

    /**
     * Remisión/Factura asociada
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'scheduled_surgery_id');
    }

    /**
     * Unidades de producto relacionadas
     */
    public function productUnits()
    {
        return $this->hasManyThrough(
            ProductUnit::class,
            PreAssembledPackage::class,
            'scheduled_surgery_id',
            'id',
            'id',
            'product_unit_id',
        );
    }

    /**
     * Configuración de hospital y modalidad
     */
    public function hospitalModalityConfig()
    {
        return $this->belongsTo(HospitalModalityConfig::class);
    }

    /**
     * Hospital (a través de hospital_modality_config)
     */
    public function hospital()
    {
        return $this->hasOneThrough(
            Hospital::class,
            HospitalModalityConfig::class,
            'id',
            'id',
            'hospital_modality_config_id',
            'hospital_id'
        );
    }

    /**
     * Modalidad de pago (a través de hospital_modality_config)
     */
    public function modality()
    {
        return $this->hasOneThrough(
            Modality::class,
            HospitalModalityConfig::class,
            'id',
            'id',
            'hospital_modality_config_id',
            'modality_id'
        );
    }

    /**
     * Alias para checklist (por compatibilidad)
     */
    public function surgeryChecklist()
    {
        return $this->belongsTo(SurgicalChecklist::class, 'checklist_id');
    }

    // ==================== SCOPES ====================
    
    /**
     * Cirugías programadas (futuras)
     */
    public function scopeUpcoming(Builder $query)
    {
        return $query->where('surgery_datetime', '>=', now()) 
            ->where('status', 'scheduled')
            ->orderBy('surgery_datetime', 'asc');  
    }

    /**
     * Cirugías de hoy
     */
    public function scopeToday(Builder $query)
    {
        return $query->whereDate('surgery_datetime', today());
    }

    /**
     * Por hospital
     */
    public function scopeForHospital(Builder $query, $hospitalId)
    {
        return $query->where('hospital_id', $hospitalId);
    }

    /**
     * Por doctor
     */
    public function scopeForDoctor(Builder $query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Por rango de fechas
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('surgery_datetime', [$startDate, $endDate]);
    }

    /**
     * Por estado
     */
    public function scopeWithStatus(Builder $query, $status)
    {
        return $query->where('status', $status);
    }

    // ==================== MÉTODOS PRINCIPALES ====================
    
    /**
     * Obtener items del checklist con condicionales aplicados
     * Este es el método principal que usa el sistema de preparación
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getChecklistItemsWithConditionals()
    {
        if (!$this->checklist_id) {
            Log::warning("Cirugía {$this->id} no tiene checklist asignado");
            return collect();
        }

        Log::info("Obteniendo items con condicionales para cirugía:", [
            'surgery_id' => $this->id,
            'checklist_id' => $this->checklist_id,
            'doctor_id' => $this->doctor_id,
            'hospital_id' => $this->hospital_id,
            'modality_config_id' => $this->hospital_modality_config_id,
        ]);

        // Obtener items base del checklist
        $baseItems = ChecklistItem::where('checklist_id', $this->checklist_id)
            ->with(['product', 'conditionals'])
            ->ordered()
            ->get();

        Log::info("Items base del checklist: {$baseItems->count()}");

        $results = collect();

        // Procesar cada item base
        foreach ($baseItems as $item) {
            $adjustedData = $item->getAdjustedQuantity($this);
            
            Log::debug("Item procesado:", [
                'product_name' => $item->product->name,
                'base_quantity' => $adjustedData['base_quantity'],
                'final_quantity' => $adjustedData['final_quantity'],
                'has_conditional' => $adjustedData['has_conditional'],
            ]);

            // Solo agregar si la cantidad final es mayor a 0
            if ($adjustedData['final_quantity'] > 0) {
                $results->push([
                    'item' => $item,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'base_quantity' => $adjustedData['base_quantity'],
                    'adjusted_quantity' => $adjustedData['final_quantity'],
                    'has_conditional' => $adjustedData['has_conditional'],
                    'conditional_description' => $adjustedData['conditional_description'],
                    'is_mandatory' => $item->is_mandatory ?? true,
                    'source' => $adjustedData['has_conditional'] ? 'conditional' : 'base',
                ]);
            }
        }

        // Buscar productos adicionales (items con is_additional_product = true)
        $additionalProducts = $this->getAdditionalProducts();
        
        if ($additionalProducts->isNotEmpty()) {
            Log::info("Productos adicionales encontrados: {$additionalProducts->count()}");
            
            foreach ($additionalProducts as $additional) {
                $results->push([
                    'item' => $additional['item'],
                    'product_id' => $additional['item']->product_id,
                    'product_name' => $additional['item']->product->name,
                    'base_quantity' => 0,
                    'adjusted_quantity' => $additional['quantity'],
                    'has_conditional' => true,
                    'conditional_description' => $additional['description'],
                    'is_mandatory' => false,
                    'source' => 'additional',
                ]);
            }
        }

        Log::info("Total items finales: {$results->count()}");

        return $results;
    }

    /**
     * Obtener productos adicionales que no están en el checklist base
     * pero deben incluirse por condicionales específicos
     * 
     * @return \Illuminate\Support\Collection
     */
    protected function getAdditionalProducts()
    {
        // Obtener legal_entity_id según tu estructura
        $legalEntityId = $this->hospital->legal_entity_id ?? null;

        // Buscar condicionales que marquen productos adicionales
        $additionalConditionals = ChecklistConditional::where('is_additional_product', true)
            ->whereHas('checklistItem', function($query) {
                $query->where('checklist_id', $this->checklist_id);
            })
            ->get();

        $results = collect();

        foreach ($additionalConditionals as $conditional) {
            // Verificar si este condicional aplica a esta cirugía
            $matches = true;

            if ($conditional->doctor_id !== null && $conditional->doctor_id !== $this->doctor_id) {
                $matches = false;
            }

            if ($conditional->hospital_id !== null && $conditional->hospital_id !== $this->hospital_id) {
                $matches = false;
            }

            if ($conditional->modality_id !== null && $conditional->modality_id !== $this->hospital_modality_config_id) {
                $matches = false;
            }

            if ($conditional->legal_entity_id !== null && $conditional->legal_entity_id !== $legalEntityId) {
                $matches = false;
            }

            if ($matches && $conditional->additional_quantity > 0) {
                $results->push([
                    'item' => $conditional->checklistItem,
                    'quantity' => $conditional->additional_quantity,
                    'description' => $conditional->getDescription(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Obtener resumen de la preparación con cantidades
     * 
     * @return array
     */
    public function getPreparationSummary(): array
    {
        $items = $this->getChecklistItemsWithConditionals();

        return [
            'total_items' => $items->count(),
            'total_quantity' => $items->sum('adjusted_quantity'),
            'items_with_conditionals' => $items->where('has_conditional', true)->count(),
            'additional_products' => $items->where('source', 'additional')->count(),
            'mandatory_items' => $items->where('is_mandatory', true)->count(),
        ];
    }

    // ==================== MÉTODOS AUXILIARES ====================
    
    /**
     * Generar código único para la cirugía
     */
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

    /**
     * Cambiar estado de la cirugía
     */
    public function updateStatus($newStatus)
    {
        $this->update(['status' => $newStatus]);
        
        Log::info("Estado de cirugía actualizado:", [
            'surgery_id' => $this->id,
            'old_status' => $this->getOriginal('status'),
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Verificar si puede ser editada
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['scheduled', 'in_preparation']);
    }

    /**
     * Verificar si puede ser cancelada
     */
    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Verificar si está lista para iniciar
     */
    public function isReady(): bool
    {
        return $this->status === 'ready' && 
               $this->preparation && 
               $this->preparation->status === 'complete';
    }

    /**
     * Verificar si tiene preparación activa
     */
    public function hasActivePreparation(): bool
    {
        return $this->preparation && 
               in_array($this->preparation->status, ['picking', 'pending']);
    }

    /**
     * Obtener el nombre del hospital
     */
    public function getHospitalNameAttribute(): string
    {
        return $this->hospital?->name ?? 'Hospital no asignado';
    }

    /**
     * Obtener el nombre del doctor
     */
    public function getDoctorNameAttribute(): string
    {
        return $this->doctor?->name ?? 'Doctor no asignado';
    }

    /**
     * Obtener el nombre de la modalidad
     */
    public function getModalityNameAttribute(): string
    {
        return $this->modality?->name ?? 'Modalidad no asignada';
    }

    /**
     * Obtener fecha formateada para humanos
     */
    public function getSurgeryDateFormattedAttribute(): string
    {
        return $this->surgery_datetime?->format('d/m/Y H:i') ?? 'Fecha no asignada';
    }

    /**
     * Verificar si la cirugía es para hoy
     */
    public function isTodayAttribute(): bool
    {
        return $this->surgery_datetime?->isToday() ?? false;
    }

    /**
     * Verificar si la cirugía es futura
     */
    public function isFutureAttribute(): bool
    {
        return $this->surgery_datetime?->isFuture() ?? false;
    }

    /**
     * Verificar si la cirugía ya pasó
     */
    public function isPastAttribute(): bool
    {
        return $this->surgery_datetime?->isPast() ?? false;
    }

    // ==================== EVENTOS DEL MODELO ====================
    
    protected static function boot()
    {
        parent::boot();

        // Generar código automáticamente al crear
        static::creating(function ($surgery) {
            if (empty($surgery->code)) {
                $surgery->code = self::generateCode();
            }
        });

        // Log al actualizar
        static::updating(function ($surgery) {
            if ($surgery->isDirty('status')) {
                Log::info("Cambio de estado en cirugía {$surgery->id}: {$surgery->getOriginal('status')} → {$surgery->status}");
            }
        });
    }
}