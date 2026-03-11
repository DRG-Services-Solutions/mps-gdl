<?php

namespace App\Http\Controllers;

use App\Models\SurgicalKitTemplateItems;
use App\Models\SurgicalKitTemplateItemConditional;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Modality;
use App\Models\LegalEntity;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SurgicalKitTemplateItemConditionalController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    // LISTAR CONDICIONALES DE UN ITEM
    // ═══════════════════════════════════════════════════════════

    public function index(SurgicalKitTemplateItems $surgicalKitTemplateItem): JsonResponse
    {
        $conditionals = SurgicalKitTemplateItemConditional::where('surgical_kit_template_item_id', $surgicalKitTemplateItem->id)
            ->with(['doctor', 'hospital', 'modality', 'legalEntity', 'targetProduct'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($c) => $this->formatConditional($c));

        return response()->json([
            'success' => true,
            'data'    => $conditionals,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // DATOS PARA EL FORMULARIO (selects del modal)
    // ═══════════════════════════════════════════════════════════

    public function formData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'doctors'        => Doctor::orderBy('first_name')->get(['id', 'first_name', 'last_name'])
                                        ->map(fn ($d) => ['id' => $d->id, 'name' => "Dr. {$d->first_name} {$d->last_name}"]),
                'hospitals'      => Hospital::orderBy('name')->get(['id', 'name']),
                'modalities'     => Modality::orderBy('name')->get(['id', 'name']),
                'legal_entities' => LegalEntity::orderBy('name')->get(['id', 'name']),
                'products'       => Product::where('status', 'active')
                                        ->whereNotNull('code')
                                        ->orderBy('name')
                                        ->get(['id', 'code', 'name'])
                                        ->map(fn ($p) => ['id' => $p->id, 'label' => "{$p->code} - {$p->name}"]),
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // CREAR CONDICIONAL
    // ═══════════════════════════════════════════════════════════

    public function store(Request $request, SurgicalKitTemplateItems $surgicalKitTemplateItem): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id'         => 'nullable|exists:doctors,id',
            'hospital_id'       => 'nullable|exists:hospitals,id',
            'modality_id'       => 'nullable|exists:modalities,id',
            'legal_entity_id'   => 'nullable|exists:legal_entities,id',
            'action_type'       => 'required|in:adjust_quantity,add_product,exclude,replace,add_dependency',
            'quantity_override' => 'nullable|integer|min:0',
            'additional_quantity'=> 'nullable|integer|min:1',
            'target_product_id' => 'nullable|exists:products,id',
            'dependency_quantity'=> 'nullable|integer|min:1',
            'exclude_from_invoice' => 'boolean',
            'requires_approval'    => 'boolean',
            'notes'             => 'nullable|string|max:500',
        ]);

        // Validar que al menos un criterio esté definido
        $hasCriteria = $validated['doctor_id']
            || $validated['hospital_id']
            || $validated['modality_id']
            || $validated['legal_entity_id'];

        if (! $hasCriteria) {
            return response()->json([
                'success' => false,
                'message' => 'Debes definir al menos un criterio de aplicación.',
            ], 422);
        }

        // Validar campos requeridos según action_type
        $actionType = $validated['action_type'];

        if ($actionType === 'adjust_quantity' && is_null($validated['quantity_override'] ?? null)) {
            return response()->json(['success' => false, 'message' => 'La nueva cantidad es requerida.'], 422);
        }
        if ($actionType === 'add_product' && empty($validated['additional_quantity'])) {
            return response()->json(['success' => false, 'message' => 'La cantidad adicional es requerida.'], 422);
        }
        if (in_array($actionType, ['replace', 'add_dependency']) && empty($validated['target_product_id'])) {
            return response()->json(['success' => false, 'message' => 'El producto objetivo es requerido.'], 422);
        }
        if ($actionType === 'add_dependency' && empty($validated['dependency_quantity'])) {
            return response()->json(['success' => false, 'message' => 'La cantidad de dependencia es requerida.'], 422);
        }

        // Crear condicional
        $conditional = SurgicalKitTemplateItemConditional::create([
            'surgical_kit_template_item_id' => $surgicalKitTemplateItem->id,
            'doctor_id'          => $validated['doctor_id'] ?? null,
            'hospital_id'        => $validated['hospital_id'] ?? null,
            'modality_id'        => $validated['modality_id'] ?? null,
            'legal_entity_id'    => $validated['legal_entity_id'] ?? null,
            'action_type'        => $actionType,
            'quantity_override'  => $validated['quantity_override'] ?? null,
            'additional_quantity'=> $validated['additional_quantity'] ?? null,
            'target_product_id'  => $validated['target_product_id'] ?? null,
            'dependency_quantity'=> $validated['dependency_quantity'] ?? null,
            'exclude_from_invoice' => $validated['exclude_from_invoice'] ?? false,
            'requires_approval'  => $validated['requires_approval'] ?? false,
            'notes'              => $validated['notes'] ?? null,
            'created_by'         => auth()->id(),
        ]);

        $conditional->load(['doctor', 'hospital', 'modality', 'legalEntity', 'targetProduct']);

        // Detectar conflictos
        $conflictData = $conditional->detectConflicts();
        $warnings = [];

        if ($conflictData['has_conflict']) {
            $conditional->delete();
            return response()->json([
                'success'   => false,
                'message'   => 'Conflicto detectado: ' . $conflictData['conflicts']->first()['message'],
                'conflicts' => $conflictData['conflicts']->pluck('message')->toArray(),
            ], 422);
        }

        if (! empty($conflictData['warnings'])) {
            $warnings = collect($conflictData['warnings'])->pluck('message')->toArray();
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Condicional guardado correctamente.',
            'data'     => $this->formatConditional($conditional),
            'warnings' => $warnings,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // ELIMINAR CONDICIONAL
    // ═══════════════════════════════════════════════════════════

    public function destroy(
        SurgicalKitTemplateItems $surgicalKitTemplateItem,
        SurgicalKitTemplateItemConditional $conditional
    ): JsonResponse {
        // Verificar que el condicional pertenece al item
        if ($conditional->surgical_kit_template_item_id !== $surgicalKitTemplateItem->id) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $conditional->delete();

        return response()->json([
            'success' => true,
            'message' => 'Condicional eliminado.',
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // HELPER: Formatear condicional para el frontend
    // ═══════════════════════════════════════════════════════════

    private function formatConditional(SurgicalKitTemplateItemConditional $c): array
    {
        return [
            'id'                  => $c->id,
            'action_type'         => $c->action_type,
            'action_description'  => $c->getActionDescription(),
            'description'         => $c->getDescription(),
            'specificity_level'   => $c->getSpecificityLevel(),

            // Criterios
            'doctor_id'           => $c->doctor_id,
            'doctor_name'         => $c->doctor
                                        ? 'Dr. ' . $c->doctor->first_name . ' ' . $c->doctor->last_name
                                        : 'Todos',
            'hospital_id'         => $c->hospital_id,
            'hospital_name'       => $c->hospital?->name ?? 'Todos',
            'modality_id'         => $c->modality_id,
            'modality_name'       => $c->modality?->name ?? 'Todas',
            'legal_entity_id'     => $c->legal_entity_id,
            'legal_entity_name'   => $c->legalEntity?->name ?? 'Todas',

            // Valores
            'quantity_override'   => $c->quantity_override,
            'additional_quantity' => $c->additional_quantity,
            'target_product_id'   => $c->target_product_id,
            'target_product_name' => $c->targetProduct
                                        ? $c->targetProduct->code . ' - ' . $c->targetProduct->name
                                        : null,
            'dependency_quantity' => $c->dependency_quantity,

            // Modificadores
            'exclude_from_invoice'=> $c->exclude_from_invoice,
            'requires_approval'   => $c->requires_approval,
            'notes'               => $c->notes,
        ];
    }
}
