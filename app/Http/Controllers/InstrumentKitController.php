<?php

namespace App\Http\Controllers;

use App\Models\InstrumentKit;
use App\Models\Instrument;
use App\Models\SurgicalKitTemplate;
use Illuminate\Http\Request;
use App\Models\Product;
class InstrumentKitController extends Controller
{
    public function index(Request $request)
    {
        $query = InstrumentKit::with('template')
            ->withCount('instruments');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $kits = $query->latest()->paginate(15)->withQueryString();

        $totalCount     = InstrumentKit::count();
        $availableCount = InstrumentKit::where('status', 'available')->count();
        $incompleteCount = InstrumentKit::where('status', 'incomplete')->count();

        return view('instrument-kits.index', compact(
            'kits', 'totalCount', 'availableCount', 'incompleteCount'
        ));
    }

    public function create()
    {
        $templates = SurgicalKitTemplate::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('instrument-kits.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'serial_number' => 'required|string|max:100|unique:instrument_kits,serial_number',
            'template_id'   => 'nullable|exists:surgical_kit_templates,id',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $validated['code'] = InstrumentKit::generateCode();
        $validated['status'] = 'incomplete'; // Comienza vacío

        $kit = InstrumentKit::create($validated);

        return redirect()
            ->route('instrument-kits.show', $kit)
            ->with('success', 'Kit creado. Ahora puedes asignar instrumentos.');
    }

    public function show(InstrumentKit $instrumentKit)
    {
        $instrumentKit->load([
            'template',
            'instruments.category',
            'instruments.dependsOn',
            'instruments.dependents',
        ]);

        $stats = [
            'total'       => $instrumentKit->instruments->count(),
            'missing'     => $instrumentKit->missing_count,
            'completeness' => $instrumentKit->completeness,
            'by_condition' => $instrumentKit->instruments->groupBy('condition')->map->count(),
        ];
        $products = $instrumentKit->instruments()->with('product')->get();

        return view('instrument-kits.show', compact('instrumentKit', 'stats', 'products'));
    }

    public function edit(InstrumentKit $instrumentKit)
    {
        $templates = SurgicalKitTemplate::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('instrument-kits.edit', compact('instrumentKit', 'templates'));
    }

    public function update(Request $request, InstrumentKit $instrumentKit)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'serial_number' => "required|string|max:100|unique:instrument_kits,serial_number,{$instrumentKit->id}",
            'template_id'   => 'nullable|exists:surgical_kit_templates,id',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $instrumentKit->update($validated);
        $instrumentKit->refreshStatus();

        return redirect()
            ->route('instrument-kits.show', $instrumentKit)
            ->with('success', 'Kit actualizado.');
    }

    public function destroy(InstrumentKit $instrumentKit)
    {
        if ($instrumentKit->status === 'in_surgery') {
            return redirect()->back()
                ->with('error', 'No se puede eliminar un kit que está en cirugía.');
        }

        // Liberar instrumentos
        $instrumentKit->instruments()->update([
            'kit_id' => null,
            'status' => 'available',
        ]);

        $instrumentKit->delete();

        return redirect()
            ->route('instrument-kits.index')
            ->with('success', 'Kit eliminado. Los instrumentos fueron liberados.');
    }

    /**
     * Asignar instrumento al kit
     */
    public function assignInstrument(Request $request, InstrumentKit $instrumentKit)
    {
        //dd('Datos recibidos:', $request->all(), 'Kit ID:', $instrumentKit->id);
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Buscamos el instrumento que queremos meter al kit
        $product = Product::findOrFail($validated['product_id']);

        if (!$product->canBeAssignedToKit()) {
            $estado = $product->status_color['label'] ?? $product->status ?? 'No disponible';

            return redirect()->back()
                ->with('error', "El producto {$product->serial_number} no está disponible para asignación (Estado actual: {$estado}).");
        }

        // Le decimos al KIT que se asigne este INSTRUMENTO
        $instrumentKit->assignInstrument($product);
        
        // Actualizamos el estatus del instrumento (ahora está "in_kit")
        $product->refreshStatus();

        return redirect()->back()
            ->with('success', "{$product->full_label} asignado al kit exitosamente.");
    }

    /**
     * Remover instrumento del kit
     */
    public function removeInstrument(InstrumentKit $instrumentKit, Instrument $instrument)
    {
        if ($instrument->kit_id !== $instrumentKit->id) {
            return redirect()->back()
                ->with('error', 'Este instrumento no pertenece a este kit.');
        }

        if ($instrumentKit->status === 'in_surgery') {
            return redirect()->back()
                ->with('error', 'No se pueden remover instrumentos de un kit que está en cirugía.');
        }

        $instrumentKit->removeInstrument($instrument);
        $instrumentKit->refreshStatus();

        return redirect()->back()
            ->with('success', "{$instrument->full_label} removido del kit.");
    }
}