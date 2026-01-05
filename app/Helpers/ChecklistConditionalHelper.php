<?php

namespace App\Helpers;

use App\Models\ChecklistItem;
use App\Models\ChecklistConditional;
use App\Models\HospitalModalityConfig;

class ChecklistConditionalHelper
{
    /**
     * Aplicar condicionales a un item del checklist
     * 
     * @param ChecklistItem $item - Item del checklist
     * @param int $doctorId - ID del doctor
     * @param int $hospitalModalityConfigId - ID de la configuración hospital+modalidad+legal entity
     * @return int - Cantidad final a usar
     */
    public static function applyConditionals(
        ChecklistItem $item,
        int $doctorId,
        int $hospitalModalityConfigId
    ): int {
        \Log::info('[CONDITIONAL] ===== Aplicando condicionales =====', [
            'checklist_item_id' => $item->id,
            'product_name' => $item->product->name,
            'base_quantity' => $item->quantity,
            'doctor_id' => $doctorId,
            'hospital_modality_config_id' => $hospitalModalityConfigId,
        ]);

        // Obtener la configuración completa
        $config = HospitalModalityConfig::find($hospitalModalityConfigId);

        if (!$config) {
            \Log::warning('[CONDITIONAL] Configuración no encontrada, usando cantidad base');
            return $item->quantity;
        }

        \Log::info('[CONDITIONAL] Configuración obtenida', [
            'hospital_id' => $config->hospital_id,
            'modality_id' => $config->modality_id,
            'legal_entity_id' => $config->legal_entity_id,
        ]);

        // Buscar TODOS los condicionales que coincidan
        $conditionals = ChecklistConditional::where('checklist_item_id', $item->id)
            ->forDoctor($doctorId)
            ->forHospital($config->hospital_id)
            ->forModality($config->modality_id)
            ->forLegalEntity($config->legal_entity_id)
            ->get();

        \Log::info('[CONDITIONAL] Condicionales encontrados', [
            'count' => $conditionals->count(),
        ]);

        // Si no hay condicionales, usar cantidad base
        if ($conditionals->isEmpty()) {
            \Log::info('[CONDITIONAL] No hay condicionales, usando cantidad base', [
                'final_quantity' => $item->quantity,
            ]);
            return $item->quantity;
        }

        // Recopilar todas las cantidades posibles
        $quantities = [$item->quantity]; // Empezar con la base

        foreach ($conditionals as $conditional) {
            $effectiveQty = $conditional->getEffectiveQuantity($item->quantity);
            $quantities[] = $effectiveQty;

            \Log::info('[CONDITIONAL] Condicional evaluado', [
                'conditional_id' => $conditional->id,
                'description' => $conditional->getDescription(),
                'specificity' => $conditional->getSpecificityLevel(),
                'is_additional' => $conditional->is_additional_product,
                'quantity_override' => $conditional->quantity_override,
                'effective_quantity' => $effectiveQty,
            ]);
        }

        // TOMAR LA MAYOR cantidad
        $finalQuantity = max($quantities);

        \Log::info('[CONDITIONAL] ===== Resultado final =====', [
            'base_quantity' => $item->quantity,
            'all_quantities' => $quantities,
            'final_quantity' => $finalQuantity,
        ]);

        return $finalQuantity;
    }

    /**
     * Obtener productos adicionales para una cirugía
     * 
     * @param int $checklistId - ID del checklist
     * @param int $doctorId - ID del doctor
     * @param int $hospitalModalityConfigId - ID de la configuración
     * @return array - Array de ['product_id' => quantity]
     */
    public static function getAdditionalProducts(
        int $checklistId,
        int $doctorId,
        int $hospitalModalityConfigId
    ): array {
        \Log::info('[CONDITIONAL] Buscando productos adicionales', [
            'checklist_id' => $checklistId,
            'doctor_id' => $doctorId,
            'hospital_modality_config_id' => $hospitalModalityConfigId,
        ]);

        $config = HospitalModalityConfig::find($hospitalModalityConfigId);

        if (!$config) {
            return [];
        }

        // Buscar condicionales de productos adicionales
        $additionals = ChecklistConditional::whereHas('checklistItem', function ($q) use ($checklistId) {
                $q->where('surgery_checklist_id', $checklistId);
            })
            ->where('is_additional_product', true)
            ->forDoctor($doctorId)
            ->forHospital($config->hospital_id)
            ->forModality($config->modality_id)
            ->forLegalEntity($config->legal_entity_id)
            ->with('checklistItem.product')
            ->get();

        $products = [];

        foreach ($additionals as $additional) {
            $productId = $additional->checklistItem->product_id;
            $quantity = $additional->additional_quantity ?? 0;

            // Si ya existe, tomar la mayor cantidad
            if (isset($products[$productId])) {
                $products[$productId] = max($products[$productId], $quantity);
            } else {
                $products[$productId] = $quantity;
            }

            \Log::info('[CONDITIONAL] Producto adicional encontrado', [
                'product_id' => $productId,
                'product_name' => $additional->checklistItem->product->name,
                'quantity' => $quantity,
            ]);
        }

        return $products;
    }
}