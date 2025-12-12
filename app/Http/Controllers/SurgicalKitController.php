<?php

namespace App\Http\Controllers;

use App\Models\SurgicalKit;
use App\Models\SurgicalKitItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurgicalKitController extends Controller
{
    /**
     * Display a listing of surgical kits.
     */
    public function index(Request $request)
    {
        $query = SurgicalKit::with(['items', 'creator']);

        // Búsqueda por nombre o código
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('surgery_type', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo de cirugía
        if ($request->filled('surgery_type')) {
            $query->where('surgery_type', $request->surgery_type);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }

        $kits = $query->latest()->paginate(15);

        // Obtener tipos de cirugía únicos para el filtro
        $surgeryTypes = SurgicalKit::select('surgery_type')
            ->distinct()
            ->orderBy('surgery_type')
            ->pluck('surgery_type');

        return view('surgical-kits.index', compact('kits', 'surgeryTypes'));
    }

    /**
     * Show the form for creating a new surgical kit.
     */
    public function create()
    {
        $products = Product::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('surgical-kits.create', compact('products'));
    }

    /**
     * Store a newly created surgical kit in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surgery_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Crear el kit
                $kit = SurgicalKit::create([
                    'name' => $validated['name'],
                    'surgery_type' => $validated['surgery_type'],
                    'description' => $validated['description'] ?? null,
                    'is_active' => $validated['is_active'] ?? true,
                    'created_by' => auth()->id(),
                ]);

                // Agregar productos
                foreach ($validated['products'] as $productData) {
                    SurgicalKitItem::create([
                        'surgical_kit_id' => $kit->id,
                        'product_id' => $productData['product_id'],
                        'quantity' => $productData['quantity'],
                        'notes' => $productData['notes'] ?? null,
                    ]);
                }
            });

            return redirect()
                ->route('surgical-kits.index')
                ->with('success', 'Prearmado creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al crear el prearmado: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified surgical kit.
     */
    public function show(SurgicalKit $surgicalKit)
    {
        $surgicalKit->load(['items.product', 'creator']);

        return view('surgical-kits.show', compact('surgicalKit'));
    }

    /**
     * Show the form for editing the specified surgical kit.
     */
    public function edit(SurgicalKit $surgicalKit)
    {
        $surgicalKit->load('items.product');
        
        $products = Product::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('surgical-kits.edit', compact('surgicalKit', 'products'));
    }

    /**
     * Update the specified surgical kit in storage.
     */
    public function update(Request $request, SurgicalKit $surgicalKit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surgery_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $surgicalKit) {
                // Actualizar información del kit
                $surgicalKit->update([
                    'name' => $validated['name'],
                    'surgery_type' => $validated['surgery_type'],
                    'description' => $validated['description'] ?? null,
                    'is_active' => $validated['is_active'] ?? true,
                ]);

                // Eliminar productos existentes
                $surgicalKit->items()->delete();

                // Agregar productos nuevos
                foreach ($validated['products'] as $productData) {
                    SurgicalKitItem::create([
                        'surgical_kit_id' => $surgicalKit->id,
                        'product_id' => $productData['product_id'],
                        'quantity' => $productData['quantity'],
                        'notes' => $productData['notes'] ?? null,
                    ]);
                }
            });

            return redirect()
                ->route('surgical-kits.show', $surgicalKit)
                ->with('success', 'Prearmado actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al actualizar el prearmado: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified surgical kit from storage.
     */
    public function destroy(SurgicalKit $surgicalKit)
    {
        try {
            $surgicalKit->delete();

            return redirect()
                ->route('surgical-kits.index')
                ->with('success', 'Prearmado eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar el prearmado: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(SurgicalKit $surgicalKit)
    {
        $surgicalKit->update([
            'is_active' => !$surgicalKit->is_active
        ]);

        $status = $surgicalKit->is_active ? 'activado' : 'desactivado';

        return redirect()
            ->back()
            ->with('success', "Prearmado {$status} exitosamente.");
    }

    /**
     * Duplicate a surgical kit
     */
    public function duplicate(SurgicalKit $surgicalKit)
    {
        try {
            DB::transaction(function () use ($surgicalKit) {
                // Crear copia del kit
                $newKit = $surgicalKit->replicate();
                $newKit->name = $surgicalKit->name . ' (Copia)';
                $newKit->code = null; // Se generará automáticamente
                $newKit->created_by = auth()->id();
                $newKit->save();

                // Copiar items
                foreach ($surgicalKit->items as $item) {
                    SurgicalKitItem::create([
                        'surgical_kit_id' => $newKit->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'notes' => $item->notes,
                    ]);
                }
            });

            return redirect()
                ->route('surgical-kits.index')
                ->with('success', 'Prearmado duplicado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al duplicar el prearmado: ' . $e->getMessage());
        }
    }
}