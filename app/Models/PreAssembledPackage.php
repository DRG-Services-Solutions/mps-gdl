<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class PreAssembledPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'surgery_checklist_id',
        'package_epc',
        'status',
        'storage_location_id',
        'last_used_at',
        'times_used',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'times_used' => 'integer',
    ];

    /**
     * RELACIONES
     */
    
    // Check list del tipo de cirugía
    public function surgeryChecklist()
    {
        return $this->belongsTo(SurgicalChecklist::class, 'surgery_checklist_id');
    }

    // Contenido actual del paquete
    public function contents()
    {
        return $this->hasMany(PreAssembledContent::class, 'package_id');
    }

    // Ubicación física
    public function storageLocation()
    {
        return $this->belongsTo(StorageLocation::class, 'storage_location_id');
    }

    // Usuario que creó el paquete
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Product units actualmente en el paquete
    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class, 'current_package_id');
    }

    // Preparaciones que usan este paquete
    public function preparations()
    {
        return $this->hasMany(SurgeryPreparation::class, 'pre_assembled_package_id');
    }

    /**
     * SCOPES
     */
    
    // Solo paquetes disponibles
    public function scopeAvailable(Builder $query)
    {
        return $query->where('status', 'available');
    }

    // Por tipo de cirugía
    public function scopeForSurgeryType(Builder $query, $checklistId)
    {
        return $query->where('surgery_checklist_id', $checklistId);
    }

    // Ordenar por PEPS (más antiguo primero)
    public function scopePeps(Builder $query)
    {
        return $query->orderBy('last_used_at', 'asc');
    }

    // Con productos próximos a caducar
    public function scopeWithExpiringSoon(Builder $query, $days = 30)
    {
        return $query->whereHas('contents', function($q) use ($days) {
            $q->whereNotNull('expiration_date')
              ->where('expiration_date', '<=', now()->addDays($days));
        });
    }

    /**
     * MÉTODOS AUXILIARES
     */
    
    // Obtener resumen de contenido agrupado por producto
    public function getContentSummary()
    {
        return $this->contents()
            ->selectRaw('product_id, COUNT(*) as quantity')
            ->groupBy('product_id')
            ->with('product')
            ->get()
            ->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'product' => $item->product,
                    'quantity' => $item->quantity,
                ];
            });
    }

    // Comparar con check list
    public function compareWithChecklist(ChecklistItem $item, $legalEntityId, $paymentMode)
    {
        // Cantidad requerida por el check list
        $requiredQty = $item->getAdjustedQuantity($legalEntityId, $paymentMode);
        
        // Cantidad disponible en el paquete
        $availableQty = $this->contents()
            ->where('product_id', $item->product_id)
            ->count();

        return [
            'product_id' => $item->product_id,
            'product' => $item->product,
            'required' => $requiredQty,
            'available' => $availableQty,
            'missing' => max(0, $requiredQty - $availableQty),
            'is_complete' => $availableQty >= $requiredQty,
        ];
    }

    // Marcar como usado
    public function markAsUsed()
    {
        $this->update([
            'last_used_at' => now(),
            'times_used' => $this->times_used + 1,
        ]);
    }

    // Cambiar estado
    public function updateStatus($newStatus)
    {
        $this->update(['status' => $newStatus]);
    }

    // Verificar si tiene productos caducados
    public function hasExpiredProducts()
    {
        return $this->contents()
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<', now())
            ->exists();
    }

    // Obtener productos ordenados por caducidad
    public function getProductsByExpiration()
    {
        return $this->contents()
            ->whereNotNull('expiration_date')
            ->orderBy('expiration_date', 'asc')
            ->with('product')
            ->get();
    }

    // Calcular porcentaje de completitud respecto a un check list
    public function getCompletenessPercentage($checklistId)
    {
        $checklist = SurgicalChecklist::find($checklistId);
        if (!$checklist) return 0;

        $totalItems = $checklist->items->count();
        if ($totalItems === 0) return 0;

        $completeItems = 0;
        foreach ($checklist->items as $item) {
            $availableQty = $this->contents()
                ->where('product_id', $item->product_id)
                ->count();
            
            if ($availableQty >= $item->quantity) {
                $completeItems++;
            }
        }

        return round(($completeItems / $totalItems) * 100, 2);
    }
}