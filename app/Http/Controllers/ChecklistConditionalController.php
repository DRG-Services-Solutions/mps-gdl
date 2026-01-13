<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use App\Models\ChecklistConditional;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Modality;
use App\Models\LegalEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ChecklistConditionalController extends Controller
{
    /**
     * Listar condicionales de un item
     * GET /checklist-items/{item}/conditionals
     */
    public function index(ChecklistItem $item)
    {
        try {
            $conditionals = $item->conditionals()
                ->with(['doctor', 'hospital', 'modality', 'legalEntity'])
                ->orderBy('id', 'desc')
                ->get()
                ->map(function($conditional) {
                    return [
                        'id' => $conditional->id,
                        'doctor_id' => $conditional->doctor_id,
                        // ✅ CORREGIDO: Doctor con first_name y last_name
                        'doctor_name' => $conditional->doctor 
                            ? 'Dr. ' . $conditional->doctor->first_name . ' ' . $conditional->doctor->last_name 
                            : 'Todos',
                        'hospital_id' => $conditional->hospital_id,
                        'hospital_name' => $conditional->hospital?->name ?? 'Todos',
                        'modality_id' => $conditional->modality_id,
                        'modality_name' => $conditional->modality?->name ?? 'Todas',
                        'legal_entity_id' => $conditional->legal_entity_id,
                        'legal_entity_name' => $conditional->legalEntity?->name ?? 'Todas',
                        'quantity_override' => $conditional->quantity_override,
                        'is_additional_product' => $conditional->is_additional_product,
                        'additional_quantity' => $conditional->additional_quantity,
                        'description' => $conditional->getDescription(),
                        'notes' => $conditional->notes,
                        'specificity_level' => $conditional->getSpecificityLevel(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $conditionals,
                'item' => [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'base_quantity' => $item->quantity,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error al listar condicionales: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar condicionales: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear nuevo condicional
     * POST /checklist-items/{item}/conditionals
     */
    public function store(Request $request, ChecklistItem $item)
    {
        try {
            $validated = $request->validate([
                'doctor_id' => 'nullable|exists:doctors,id',
                'hospital_id' => 'nullable|exists:hospitals,id',
                'modality_id' => 'nullable|exists:modalities,id',
                'legal_entity_id' => 'nullable|exists:legal_entities,id',
                'quantity_override' => 'nullable|integer|min:0',
                'is_additional_product' => 'required|boolean',
                'additional_quantity' => 'nullable|integer|min:1',
                'notes' => 'nullable|string|max:500',
            ]);

            // Validación: al menos un criterio debe estar presente
            if (empty($validated['doctor_id']) && 
                empty($validated['hospital_id']) && 
                empty($validated['modality_id']) && 
                empty($validated['legal_entity_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes seleccionar al menos un criterio (Doctor, Hospital, Modalidad o Legal Entity).',
                ], 422);
            }

            // Validación: debe tener cantidad según el tipo
            if ($validated['is_additional_product']) {
                if (empty($validated['additional_quantity'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes especificar la cantidad del producto adicional.',
                    ], 422);
                }
                // Limpiar quantity_override si es producto adicional
                $validated['quantity_override'] = null;
            } else {
                if (!isset($validated['quantity_override']) || $validated['quantity_override'] === null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes especificar la cantidad a reemplazar.',
                    ], 422);
                }
                // Limpiar additional_quantity si no es producto adicional
                $validated['additional_quantity'] = null;
            }

            // Verificar que no exista un condicional idéntico
            $exists = ChecklistConditional::where('checklist_item_id', $item->id)
                ->where(function($query) use ($validated) {
                    $query->where('doctor_id', $validated['doctor_id'] ?? null)
                          ->where('hospital_id', $validated['hospital_id'] ?? null)
                          ->where('modality_id', $validated['modality_id'] ?? null)
                          ->where('legal_entity_id', $validated['legal_entity_id'] ?? null);
                })
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un condicional con estos mismos criterios.',
                ], 422);
            }

            // Crear condicional
            DB::beginTransaction();

            $validated['checklist_item_id'] = $item->id;
            $validated['created_by'] = auth()->id();

            $conditional = ChecklistConditional::create($validated);

            DB::commit();

            Log::info("Condicional creado exitosamente:", [
                'conditional_id' => $conditional->id,
                'item_id' => $item->id,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '✓ Condicional creado exitosamente.',
                'data' => [
                    'id' => $conditional->id,
                    'doctor_name' => $conditional->doctor 
                        ? 'Dr. ' . $conditional->doctor->first_name . ' ' . $conditional->doctor->last_name 
                        : 'Todos',
                    'hospital_name' => $conditional->hospital?->name ?? 'Todos',
                    'modality_name' => $conditional->modality?->name ?? 'Todas',
                    'legal_entity_name' => $conditional->legalEntity?->name ?? 'Todas',
                    'quantity_override' => $conditional->quantity_override,
                    'is_additional_product' => $conditional->is_additional_product,
                    'additional_quantity' => $conditional->additional_quantity,
                    'description' => $conditional->fresh()->getDescription(),
                    'notes' => $conditional->notes,
                ],
            ], 201);


        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear condicional: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear condicional: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar condicional
     * PUT /checklist-items/{item}/conditionals/{conditional}
     */
    public function update(Request $request, ChecklistItem $item, ChecklistConditional $conditional)
    {
        try {
            // Verificar que el condicional pertenece al item
            if ($conditional->checklist_item_id !== $item->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este condicional no pertenece al item especificado.',
                ], 403);
            }

            $validated = $request->validate([
                'doctor_id' => 'nullable|exists:doctors,id',
                'hospital_id' => 'nullable|exists:hospitals,id',
                'modality_id' => 'nullable|exists:modalities,id',
                'legal_entity_id' => 'nullable|exists:legal_entities,id',
                'quantity_override' => 'nullable|integer|min:0',
                'is_additional_product' => 'required|boolean',
                'additional_quantity' => 'nullable|integer|min:1',
                'notes' => 'nullable|string|max:500',
            ]);

            // Validación: al menos un criterio
            if (empty($validated['doctor_id']) && 
                empty($validated['hospital_id']) && 
                empty($validated['modality_id']) && 
                empty($validated['legal_entity_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes seleccionar al menos un criterio.',
                ], 422);
            }

            // Validación de cantidades según tipo
            if ($validated['is_additional_product']) {
                if (empty($validated['additional_quantity'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes especificar la cantidad del producto adicional.',
                    ], 422);
                }
                $validated['quantity_override'] = null;
            } else {
                if (!isset($validated['quantity_override']) || $validated['quantity_override'] === null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes especificar la cantidad a reemplazar.',
                    ], 422);
                }
                $validated['additional_quantity'] = null;
            }

            DB::beginTransaction();

            $conditional->update($validated);

            DB::commit();

            Log::info("Condicional actualizado: {$conditional->id}");

            return response()->json([
                'success' => true,
                'message' => '✓ Condicional actualizado exitosamente.',
                'data' => [
                    'id' => $conditional->id,
                    // ✅ CORREGIDO: Doctor con first_name y last_name
                    'doctor_name' => $conditional->fresh()->doctor 
                        ? 'Dr. ' . $conditional->fresh()->doctor->first_name . ' ' . $conditional->fresh()->doctor->last_name 
                        : 'Todos',
                    'hospital_name' => $conditional->fresh()->hospital?->name ?? 'Todos',
                    'modality_name' => $conditional->fresh()->modality?->name ?? 'Todas',
                    'legal_entity_name' => $conditional->fresh()->legalEntity?->name ?? 'Todas',
                    'quantity_override' => $conditional->quantity_override,
                    'is_additional_product' => $conditional->is_additional_product,
                    'additional_quantity' => $conditional->additional_quantity,
                    'description' => $conditional->fresh()->getDescription(),
                    'notes' => $conditional->notes,
                ],
            ]);


        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar condicional: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar condicional: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar condicional
     * DELETE /checklist-items/{item}/conditionals/{conditional}
     */
    public function destroy(ChecklistItem $item, ChecklistConditional $conditional)
    {
        try {
            // Verificar que el condicional pertenece al item
            if ($conditional->checklist_item_id !== $item->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este condicional no pertenece al item especificado.',
                ], 403);
            }

            DB::beginTransaction();

            $conditionalId = $conditional->id;
            $conditional->delete();

            DB::commit();

            Log::info("Condicional eliminado: {$conditionalId}");

            return response()->json([
                'success' => true,
                'message' => '✓ Condicional eliminado exitosamente.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar condicional: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar condicional: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener datos para los selects del formulario
     * GET /conditional-form-data
     */
    public function getFormData()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    // ✅ CORREGIDO: Doctores con first_name y last_name
                    'doctors' => Doctor::select('id', 'first_name', 'last_name')
                        ->orderBy('first_name')
                        ->get()
                        ->map(function($doctor) {
                            return [
                                'id' => $doctor->id,
                                'name' => 'Dr. ' . $doctor->first_name . ' ' . $doctor->last_name,
                            ];
                        }),
                    'hospitals' => Hospital::select('id', 'name')
                        ->orderBy('name')
                        ->get(),
                    'modalities' => Modality::select('id', 'name')
                        ->orderBy('name')
                        ->get(),
                    'legal_entities' => LegalEntity::select('id', 'name')
                        ->orderBy('name')
                        ->get(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Error al cargar datos del formulario: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar datos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview de cómo se aplicarían los condicionales a una cirugía específica
     * POST /checklist-items/{item}/conditionals/preview
     */
    public function preview(Request $request, ChecklistItem $item)
    {
        try {
            $validated = $request->validate([
                'doctor_id' => 'nullable|exists:doctors,id',
                'hospital_id' => 'nullable|exists:hospitals,id',
                'modality_id' => 'nullable|exists:modalities,id',
                'legal_entity_id' => 'nullable|exists:legal_entities,id',
            ]);

            // Crear objeto simulado de cirugía
            $mockSurgery = (object) [
                'doctor_id' => $validated['doctor_id'] ?? null,
                'hospital_id' => $validated['hospital_id'] ?? null,
                'hospital_modality_config_id' => $validated['modality_id'] ?? null,
                'hospital' => (object) [
                    'legal_entity_id' => $validated['legal_entity_id'] ?? null,
                ],
            ];

            // Aplicar condicionales
            $result = $item->getAdjustedQuantity($mockSurgery);

            return response()->json([
                'success' => true,
                'data' => [
                    'base_quantity' => $result['base_quantity'],
                    'final_quantity' => $result['final_quantity'],
                    'has_conditional' => $result['has_conditional'],
                    'conditional_description' => $result['conditional_description'],
                    'product_name' => $item->product->name,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Error en preview: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar preview: ' . $e->getMessage(),
            ], 500);
        }
    }
}