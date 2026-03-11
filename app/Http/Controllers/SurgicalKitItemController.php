<?php

namespace App\Http\Controllers;

use App\Models\SurgicalKitTemplate;
use App\Models\SurgicalKitTemplateItem;
use App\Models\SurgicalChecklist;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSurgicalKitTemplateRequest;
use App\Http\Requests\UpdateSurgicalKitTemplateRequest;
use Illuminate\Support\Facades\DB;

class SurgicalKitTemplateController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    // CRUD BÁSICO
    // ═══════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = SurgicalKitTemplate::with('items');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('surgery_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $templates = $query->latest()->paginate(15);

        return view('surgical_kit_templates.index', compact('templates'));
    }

    public function create()
    {
        $checklists = SurgicalChecklist::with('items')->get();
        $products   = $this->getProductsWithCode();

        return view('surgical_kit_templates.create', compact('checklists', 'products'));
    }

    public function store(StoreSurgicalKitTemplateRequest $request)
    {
        $validated = $request->validated();
        $template  = SurgicalKitTemplate::create($validated);

        return redirect()
            ->route('surgical_kit_templates.show', $template)
            ->with('success', 'Kit quirúrgico creado con éxito.');
    }

    public function show(SurgicalKitTemplate $surgicalKitTemplate)
    {
        $surgicalKitTemplate->load('items.product');

        // Productos con code para el TomSelect de la vista
        $products = $this->getProductsWithCode();

        // Disponibilidad de stock (igual que SurgicalKitController)
        $availability = $surgicalKitTemplate->checkAvailability();

        return view('surgical_kit_templates.show', compact(
            'surgicalKitTemplate',
            'products',
            'availability'
        ));
    }

    public function edit(SurgicalKitTemplate $surgicalKitTemplate)
    {
        $surgicalKitTemplate->load('items.product');
        $checklists = SurgicalChecklist::with('items')->get();

        return view('surgical_kit_templates.edit', compact('surgicalKitTemplate', 'checklists'));
    }

    public function update(UpdateSurgicalKitTemplateRequest $request, SurgicalKitTemplate $surgicalKitTemplate)
    {
        $surgicalKitTemplate->update($request->validated());

        return redirect()
            ->route('surgical_kit_templates.show', $surgicalKitTemplate)
            ->with('success', 'Kit quirúrgico actualizado con éxito.');
    }

    public function destroy(SurgicalKitTemplate $surgicalKitTemplate)
    {
        try {
            $surgicalKitTemplate->delete();

            return redirect()
                ->route('surgical_kit_templates.index')
                ->with('success', 'Kit quirúrgico eliminado con éxito.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // VALIDACIÓN DE STOCK
    // ═══════════════════════════════════════════════════════════

    /**
     * Muestra la vista de verificación de stock para cada item del template.
     * Reutiliza la lógica de checkAvailability() del modelo.
     */
    public function checkStock(SurgicalKitTemplate $surgicalKitTemplate)
    {
        $surgicalKitTemplate->load('items.product');
        $availability = $surgicalKitTemplate->checkAvailability();

        return view('surgical_kit_templates.check-stock', compact('surgicalKitTemplate', 'availability'));
    }

    // ═══════════════════════════════════════════════════════════
    // TOGGLE ACTIVO / INACTIVO
    // ═══════════════════════════════════════════════════════════

    public function toggleActive(SurgicalKitTemplate $surgicalKitTemplate)
    {
        $surgicalKitTemplate->update([
            'is_active' => ! $surgicalKitTemplate->is_active,
        ]);

        $status = $surgicalKitTemplate->is_active ? 'activado' : 'desactivado';

        return redirect()
            ->back()
            ->with('success', "Kit quirúrgico {$status} exitosamente.");
    }

    // ═══════════════════════════════════════════════════════════
    // DUPLICAR
    // ═══════════════════════════════════════════════════════════

    public function duplicate(SurgicalKitTemplate $surgicalKitTemplate)
    {
        try {
            DB::transaction(function () use ($surgicalKitTemplate) {
                $copy       = $surgicalKitTemplate->replicate();
                $copy->name = $surgicalKitTemplate->name . ' (Copia)';
                $copy->code = null;
                $copy->save();

                foreach ($surgicalKitTemplate->items as $item) {
                    SurgicalKitTemplateItem::create([
                        'surgical_kit_template_id' => $copy->id,
                        'product_id'               => $item->product_id,
                        'quantity'                 => $item->quantity,
                        'notes'                    => $item->notes,
                    ]);
                }
            });

            return redirect()
                ->route('surgical_kit_templates.index')
                ->with('success', 'Kit quirúrgico duplicado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al duplicar: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // HELPERS PRIVADOS
    // ═══════════════════════════════════════════════════════════

    /**
     * Devuelve productos activos con code y name para los selects / TomSelect.
     * Filtra los que no tienen code ni name para no romper el render.
     */
    private function getProductsWithCode()
    {
        return Product::where('status', 'active')
            ->whereNotNull('code')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);
    }
}