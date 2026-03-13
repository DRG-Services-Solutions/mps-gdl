<?php

namespace App\Http\Controllers;

use App\Models\SurgicalKitTemplateItems;
use App\Models\SurgicalKitTemplateItemConditional;
use App\Models\Doctor;
use App\Models\Hospital;
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
        $conditionals = SurgicalKitTemplateItemConditional::where(
                'surgical_kit_template_item_id', $surgicalKitTemplateItem->id
            )
            ->with(['doctor', 'hospital', 'targetProduct'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($c) => $this->formatConditional($c));

        return response()->json([
            'success' => true,
            'data'    => $conditionals,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // DATOS PARA EL FORMULARIO
    // ═══════════════════════════════════════════════════════════

    public function formData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'doctors'  => Doctor::orderBy('first_name')
                                ->get(['id', 'first_name', 'last_name'])
                                ->map(fn ($d) => [
                                    'id'   => $d->id,
                                    'name' => "Dr. {$d->first_name} {$d->last_name}",
                                ]),
                'hospitals' => Hospital::orderBy('name')->get(['id', 'name']),
                'products'  => Product::where('status', 'active')
                                ->whereNotNull('code')
                                ->orderBy('name')
                                ->get(['id', 'code', 'name'])
                                ->map(fn ($p) => [
                                    'id'    => $p->id,
                                    'label' => "{$p->code} - {$p->name}",
                                ]),
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // CREAR CONDICIONAL
    // ═══════════════════════════════════════════════════════════

    public function store(Request $request, SurgicalKitTemplateItems $surgicalKitTemplateItem): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id'          => 'nullable|exists:doctors,id',
            'hospital_id'        => 'nullable|exists:hospitals,id',
            'action_type'        => 'required|in:adjust_quantity,replace,add_dependency',
            'quantity_override'  => 'nullable|integer|min:0',
            'target_product_id'  => 'nullable|exists:products,id',
            'dependency_quantity'=> 'nullable|integer|min:1',
            'notes'              => 'nullable|string|max:500',
        ]);

        // Al menos un criterio
        if (empty($validated['doctor_id']) && empty($validated['hospital_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Debes seleccionar al menos un criterio: Doctor o Hospital.',
            ], 422);
        }

        // Campos requeridos según action_type
        $action = $validated['action_type'];

        if ($action === 'adjust_quantity' && is_null($validated['quantity_override'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'La nueva cantidad es requerida para "Ajustar Cantidad".',
            ], 422);
        }

        if (in_array($action, ['replace', 'add_dependency']) && empty($validated['target_product_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'El producto objetivo es requerido para este tipo de acción.',
            ], 422);
        }

        if ($action === 'add_dependency' && empty($validated['dependency_quantity'])) {
            return response()->json([
                'success' => false,
                'message' => 'La cantidad de dependencia es requerida.',
            ], 422);
        }

        // Crear — cada condicional es independiente, sin detección de conflictos
        $conditional = SurgicalKitTemplateItemConditional::create([
            'surgical_kit_template_item_id' => $surgicalKitTemplateItem->id,
            'doctor_id'          => $validated['doctor_id'] ?? null,
            'hospital_id'        => $validated['hospital_id'] ?? null,
            'action_type'        => $action,
            'quantity_override'  => $action === 'adjust_quantity' ? ($validated['quantity_override'] ?? null) : null,
            'target_product_id'  => in_array($action, ['replace', 'add_dependency']) ? ($validated['target_product_id'] ?? null) : null,
            'dependency_quantity'=> $action === 'add_dependency' ? ($validated['dependency_quantity'] ?? null) : null,
            'notes'              => $validated['notes'] ?? null,
            'created_by'         => auth()->id(),
        ]);

        $conditional->load(['doctor', 'hospital', 'targetProduct']);

        return response()->json([
            'success' => true,
            'message' => 'Condicional guardado correctamente.',
            'data'    => $this->formatConditional($conditional),
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // ELIMINAR CONDICIONAL
    // ═══════════════════════════════════════════════════════════

    public function destroy(
        SurgicalKitTemplateItems $surgicalKitTemplateItem,
        SurgicalKitTemplateItemConditional $conditional
    ): JsonResponse {
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
    // HELPER PRIVADO
    // ═══════════════════════════════════════════════════════════

    private function formatConditional(SurgicalKitTemplateItemConditional $c): array
    {
        return [
            'id'                 => $c->id,
            'action_type'        => $c->action_type,
            'action_description' => $c->getActionDescription(),
            'description'        => $c->getDescription(),
            'specificity_level'  => $c->getSpecificityLevel(),

            // Criterios
            'doctor_id'          => $c->doctor_id,
            'doctor_name'        => $c->doctor
                                      ? 'Dr. ' . $c->doctor->first_name . ' ' . $c->doctor->last_name
                                      : 'Todos',
            'hospital_id'        => $c->hospital_id,
            'hospital_name'      => $c->hospital?->name ?? 'Todos',

            // Valores
            'quantity_override'  => $c->quantity_override,
            'target_product_id'  => $c->target_product_id,
            'target_product_name'=> $c->targetProduct
                                      ? $c->targetProduct->code . ' - ' . $c->targetProduct->name
                                      : null,
            'dependency_quantity'=> $c->dependency_quantity,

            'notes'              => $c->notes,
        ];
    }
}