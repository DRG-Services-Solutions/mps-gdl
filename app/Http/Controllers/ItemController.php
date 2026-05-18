<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    // Agregué 'kit' al array ya que lo usas en el index
    protected $itemTypes = [
        'instrumental', 'implant', 'implant_set', 'instrumental_set', 
        'equipment', 'accessory', 'tray', 'console', 'tower', 'kit'
    ];

    public function index(Request $request)
    {
        // ==========================================
        // 1. MÉTRICAS DEL DASHBOARD 
        // ==========================================
        $activeCount = Item::where('is_active', true)->count();
        $requiresMaintenanceCount = Item::where('requires_maintenance', true)->count();
        $kitCount = Item::whereIn('type', [
            'tray', 'instrumental_set', 'implant_set', 'tower', 'console', 'kit'
        ])->count();

        // ==========================================
        // 2. CONSTRUCTOR DE CONSULTAS
        // ==========================================
        $query = Item::query();

        // ==========================================
        // 3. APLICAR FILTROS DE BÚSQUEDA
        // ==========================================
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // ==========================================
        // 4. EAGER LOADING DE CONTEOS Y PAGINACIÓN
        // ==========================================
        $items = $query->withCount(['stockUnits', 'components'])
                       ->orderBy('name')
                       ->paginate(15)
                       ->withQueryString();

        return view('items.index', compact('items', 'activeCount', 'requiresMaintenanceCount', 'kitCount'));
    }

    /**
     * 2. CREATE: Formulario de Alta
     */
    public function create()
    {
        // Instanciamos un modelo vacío para que la vista unificada no falle al buscar $item->exists
        $item = new Item();
        return view('items.create', compact('item'));
    }

    /**
     * 3. STORE: Guardar el nuevo catálogo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:items,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in($this->itemTypes)],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'requires_maintenance' => ['boolean'],
            'maintenance_interval_uses' => ['nullable', 'required_if:requires_maintenance,1', 'integer', 'min:1'],
            'is_active' => ['boolean']
        ]);

        $validated['requires_maintenance'] = $request->boolean('requires_maintenance');
        $validated['is_active'] = true;

        if (!$validated['requires_maintenance']) {
            $validated['maintenance_interval_uses'] = null;
        }

        Item::create($validated);

        return redirect()->route('items.index')->with('success', 'Producto registrado en catálogo.');
    }

    /**
     * 4. SHOW: El Expediente Maestro-Detalle 
     */
    public function show(Item $item)
    {
        $item->load([
            'stockUnits',
            'components'
        ]);

        $availableInstruments = collect();

        if (in_array($item->type, ['tray', 'instrumental_set', 'implant_set', 'kit', 'tower', 'equipment'])) {
            $availableInstruments = Item::where('id', '!=', $item->id)
                ->where('is_active', true)
                ->whereIn('type', ['instrumental', 'implant', 'accessory'])
                ->orderBy('name', 'asc')
                ->get(['id', 'code', 'name', 'type']);
        }

        return view('items.show', compact('item', 'availableInstruments'));
    }

    /**
     * 5. EDIT: Formulario de Edición
     */
    public function edit(Item $item)
    {
        return view('items.edit', compact('item'));
    }

    /**
     * 6. UPDATE: Actualizar datos del catálogo
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('items')->ignore($item->id)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in($this->itemTypes)], // Refactorizado para usar el array central
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'requires_maintenance' => ['boolean'],
            'maintenance_interval_uses' => ['nullable', 'required_if:requires_maintenance,1', 'integer', 'min:1'],
            'is_active' => ['boolean']
        ]);

        $validated['requires_maintenance'] = $request->boolean('requires_maintenance');
        $validated['is_active'] = $request->has('is_active');

        if (!$validated['requires_maintenance']) {
            $validated['maintenance_interval_uses'] = null;
        }

        $item->update($validated);

        return redirect()->route('items.show', $item)
                         ->with('success', 'Catálogo actualizado correctamente.');
    }

    /**
     * 7. TOGGLE STATUS: Archivar/Desarchivar
     */
    public function toggleStatus(Item $item)
    {
        $item->update([
            'is_active' => !$item->is_active
        ]);

        $statusName = $item->is_active ? 'activado' : 'archivado';

        return redirect()->back()
                         ->with('success', "El modelo ha sido {$statusName} en el catálogo.");
    }

    /**
     * 8. DESTROY: Borrado seguro
     */
    public function destroy(Item $item)
    {
        // BLINDAJE LÓGICO 1: exists() es más rápido a nivel SQL que count() > 0
        if ($item->stockUnits()->exists()) {
            return redirect()->back()
                             ->with('error', 'No se puede eliminar: Existen unidades físicas de este modelo en el inventario. Archiva el catálogo en su lugar.');
        }

        // BLINDAJE LÓGICO 2: Protegemos contra el borrado si pertenece a un kit
        // Nota: Asegúrate de que esta relación ('parentItems' o la que utilices) esté correctamente definida en tu modelo Item.
        if (method_exists($item, 'parentItems') && $item->parentItems()->exists()) {
            return redirect()->back()   
                             ->with('error', 'No se puede eliminar: Este instrumento es componente de una Charola o Kit activo.');
        }

        // Si pasa los blindajes lógicos, ejecutamos el borrado
        $item->delete();

        return redirect()->route('items.index')
                         ->with('success', 'Catálogo eliminado permanentemente.');
    }

    public function searchApi(Request $request)
    {
        $search = $request->get('q');
        $parentType = $request->get('parent_type');

        // Blindaje: Si no escriben nada, devolvemos un arreglo vacío
        if (!$search) {
            return response()->json([]);
        }

        $query = Item::where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });

        if ($parentType) {
            $dummyItem = new Item(['type' => $parentType]);
            $allowedTypes = $dummyItem->getAllowedChildTypes();
            
            if (empty($allowedTypes)) {
                return response()->json([]); // No puede contener nada
            }
            $query->whereIn('type', $allowedTypes);
        } elseif (!$request->boolean('all')) {
            // Comportamiento anterior por defecto
            $query->whereNotIn('type', ['tower', 'equipment', 'console']);
        }

        $items = $query->limit(50)->get(['id', 'code', 'name', 'type']);

        return response()->json($items->map(fn($item) => [
            'id' => $item->id,
            'code' => $item->code,
            'name' => $item->name,
            'type' => $item->type,
            'type_label' => $item->typeLabel,
        ]));
    }
}