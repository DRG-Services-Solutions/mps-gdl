<?php

namespace App\Http\Controllers;

use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\Hospital;
use App\Services\PriceListImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PriceListController extends Controller
{
    protected PriceListImportService $importService;

    public function __construct(PriceListImportService $importService)
    {
        $this->importService = $importService;
    }

    // ============================================
    // CRUD
    // ============================================

    /**
     * Listado de listas de precios
     */
    public function index(Request $request)
    {
        $query = PriceList::with(['hospital', 'creator'])
            ->withCount('items');

        // Filtro: Búsqueda
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtro: Hospital
        if ($request->filled('hospital_id')) {
            $query->forHospital($request->hospital_id);
        }

        // Filtro: Estado
        if ($request->filled('status')) {
            match ($request->status) {
                'active' => $query->active(),
                'inactive' => $query->where('is_active', false),
                default => null,
            };
        }

        $priceLists = $query->latest()->paginate(15)->withQueryString();

        // Contadores
        $totalCount = PriceList::count();
        $activeCount = PriceList::where('is_active', true)->count();

        // Hospital seleccionado para persistir Tom Select
        $selectedHospital = null;
        if ($request->filled('hospital_id')) {
            $selectedHospital = Hospital::find($request->hospital_id);
        }

        return view('price-lists.index', compact(
            'priceLists',
            'totalCount',
            'activeCount',
            'selectedHospital'
        ));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        return view('price-lists.create');
    }

    /**
     * Guardar nueva lista
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hospital_id' => 'required|exists:hospitals,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verificar si el hospital ya tiene una lista activa
        $existingActive = PriceList::where('hospital_id', $validated['hospital_id'])
            ->where('is_active', true)
            ->exists();

        $priceList = PriceList::create([
            'name' => $validated['name'],
            'code' => PriceList::generateCode(),
            'hospital_id' => $validated['hospital_id'],
            'is_active' => !$existingActive, // Activa solo si no hay otra activa
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $message = 'Lista de precios creada exitosamente.';
        if ($existingActive) {
            $message .= ' Se creó como inactiva porque el hospital ya tiene una lista activa.';
        }

        return redirect()
            ->route('price-lists.show', $priceList)
            ->with('success', $message);
    }

    /**
     * Detalle de lista con sus productos
     */
    public function show(PriceList $priceList)
    {
        $priceList->load(['hospital', 'creator', 'items.product']);

        $items = $priceList->items->sortBy('product.name');

        // Estadísticas de la lista
        $stats = [
            'total_products' => $priceList->items->count(),
            'avg_price' => $priceList->items->avg('unit_price'),
            'min_price' => $priceList->items->min('unit_price'),
            'max_price' => $priceList->items->max('unit_price'),
        ];

        return view('price-lists.show', compact('priceList', 'items', 'stats'));
    }

    /**
     * Formulario de edición (datos generales)
     */
    public function edit(PriceList $priceList)
    {
        $priceList->load('hospital');

        return view('price-lists.edit', compact('priceList'));
    }

    /**
     * Actualizar datos generales
     */
    public function update(Request $request, PriceList $priceList)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hospital_id' => 'required|exists:hospitals,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $priceList->update($validated);

        return redirect()
            ->route('price-lists.show', $priceList)
            ->with('success', 'Lista de precios actualizada.');
    }

    /**
     * Eliminar lista
     */
    public function destroy(PriceList $priceList)
    {
        $name = $priceList->name;
        $priceList->delete();

        return redirect()
            ->route('price-lists.index')
            ->with('success', "Lista '{$name}' eliminada.");
    }

    // ============================================
    // ACTIVAR / DESACTIVAR
    // ============================================

    /**
     * Activar lista (desactiva las demás del mismo hospital)
     */
    public function activate(PriceList $priceList)
    {
        $priceList->activate();

        return redirect()->back()
            ->with('success', "Lista '{$priceList->name}' activada. Las demás listas del hospital fueron desactivadas.");
    }

    /**
     * Desactivar lista
     */
    public function deactivate(PriceList $priceList)
    {
        $priceList->deactivate();

        return redirect()->back()
            ->with('success', "Lista '{$priceList->name}' desactivada.");
    }

    // ============================================
    // IMPORTACIÓN CSV
    // ============================================

    /**
     * Mostrar formulario de importación
     */
    public function importForm(PriceList $priceList)
    {
        return view('price-lists.import', compact('priceList'));
    }

    /**
     * Procesar CSV y mostrar vista previa
     */
    public function importPreview(Request $request, PriceList $priceList)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120', // max 5MB
        ]);

        try {
            $filePath = $request->file('csv_file')->getRealPath();
            $result = $this->importService->parseCSV($filePath);

            // Guardar datos parseados en sesión para el paso de confirmación
            session([
                'import_preview' => $result,
                'import_price_list_id' => $priceList->id,
            ]);

            return view('price-lists.import-preview', compact('priceList', 'result'));

        } catch (\Exception $e) {
            Log::error("Error al parsear CSV: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al leer el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Ejecutar importación confirmada
     */
    public function importExecute(Request $request, PriceList $priceList)
    {
        $preview = session('import_preview');

        if (!$preview || session('import_price_list_id') !== $priceList->id) {
            return redirect()->route('price-lists.import', $priceList)
                ->with('error', 'La sesión de importación expiró. Sube el archivo nuevamente.');
        }

        $request->validate([
            'found_items' => 'nullable|array',
            'found_items.*' => 'boolean',
            'create_items' => 'nullable|array',
            'create_items.*.code' => 'required_with:create_items|string',
            'create_items.*.name' => 'required_with:create_items|string|max:255',
            'create_items.*.unit_price' => 'required_with:create_items|numeric|min:0',
            'create_items.*.notes' => 'nullable|string',
        ]);

        try {
            // Filtrar items encontrados que el usuario quiere importar
            $selectedFound = [];
            foreach ($preview['found'] as $index => $item) {
                if ($request->input("found_items.{$index}", true)) {
                    $selectedFound[] = $item;
                }
            }

            // Items no encontrados que el usuario quiere crear
            $createItems = $request->input('create_items', []);

            $result = $this->importService->executeImport($priceList, $selectedFound, $createItems);

            // Limpiar sesión
            session()->forget(['import_preview', 'import_price_list_id']);

            $message = "{$result['imported']} productos importados";
            if ($result['created'] > 0) {
                $message .= ", {$result['created']} productos nuevos creados";
            }

            return redirect()
                ->route('price-lists.show', $priceList)
                ->with('success', $message . '.');

        } catch (\Exception $e) {
            Log::error("Error al ejecutar importación: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error durante la importación: ' . $e->getMessage());
        }
    }

    // ============================================
    // GESTIÓN MANUAL DE ITEMS
    // ============================================

    /**
     * Agregar producto manualmente a la lista
     */
    public function addItem(Request $request, PriceList $priceList)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Verificar si ya existe
        $exists = $priceList->items()
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Este producto ya está en la lista. Puedes editar su precio.');
        }

        $priceList->items()->create($validated);

        return redirect()->back()
            ->with('success', 'Producto agregado a la lista.');
    }

    /**
     * Actualizar precio de un item
     */
    public function updateItem(Request $request, PriceList $priceList, PriceListItem $item)
    {
        // Verificar que el item pertenece a esta lista
        if ($item->price_list_id !== $priceList->id) {
            abort(403);
        }

        $validated = $request->validate([
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $item->update($validated);

        return redirect()->back()
            ->with('success', 'Precio actualizado.');
    }

    /**
     * Eliminar item de la lista
     */
    public function removeItem(PriceList $priceList, PriceListItem $item)
    {
        if ($item->price_list_id !== $priceList->id) {
            abort(403);
        }

        $productName = $item->product->name ?? 'Producto';
        $item->delete();

        return redirect()->back()
            ->with('success', "{$productName} eliminado de la lista.");
    }

    // ============================================
    // API (para Tom Select y AJAX)
    // ============================================

    /**
     * Buscar productos para agregar (excluye los que ya están en la lista)
     */
    public function searchProducts(Request $request, PriceList $priceList)
    {
        $search = $request->input('search', '');

        $existingProductIds = $priceList->items()->pluck('product_id')->toArray();

        $products = Product::where('status', 'active')
            ->whereNotIn('id', $existingProductIds)
            ->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'text' => "{$product->code} — {$product->name}",
                    'price' => $product->list_price ?? 0,
                ];
            }),
        ]);
    }
}
