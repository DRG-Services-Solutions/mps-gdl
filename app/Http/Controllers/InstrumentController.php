<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentCategory;
use App\Models\InstrumentKit;
use Illuminate\Http\Request;

class InstrumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Instrument::with(['category', 'kit', 'product', 'dependsOn']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assignment')) {
            match ($request->assignment) {
                'in_kit' => $query->inKit(),
                'loose'  => $query->loose(),
                default  => null,
            };
        }

        $instruments = $query->latest()->paginate(20)->withQueryString();

        // Contadores
        $totalCount     = Instrument::count();
        $availableCount = Instrument::where('status', 'available')->count();
        $inKitCount     = Instrument::where('status', 'in_kit')->count();
        $maintenanceCount = Instrument::where('status', 'maintenance')->count();

        $categories = InstrumentCategory::orderBy('name')->get();

        return view('instruments.index', compact(
            'instruments', 'categories',
            'totalCount', 'availableCount', 'inKitCount', 'maintenanceCount'
        ));
    }

    public function create()
    {
        $categories = InstrumentCategory::orderBy('name')->get();

        return view('instruments.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|max:100|unique:instruments,serial_number',
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:30',
            'category_id'   => 'required|exists:instrument_categories,id',
            'product_id'    => 'nullable|exists:products,id',
            'depends_on_id' => 'nullable|exists:instruments,id',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $validated['status'] = 'available';

        $instrument = Instrument::create($validated);

        return redirect()
            ->route('instruments.show', $instrument)
            ->with('success', 'Instrumento registrado exitosamente.');
    }

    public function show(Instrument $instrument)
    {
        $instrument->load(['category', 'kit', 'product', 'dependsOn', 'dependents']);

        return view('instruments.show', compact('instrument'));
    }

    public function edit(Instrument $instrument)
    {
        $categories = InstrumentCategory::orderBy('name')->get();
        $instrument->load(['category', 'kit']);

        return view('instruments.edit', compact('instrument', 'categories'));
    }

    public function update(Request $request, Instrument $instrument)
    {
        $validated = $request->validate([
            'serial_number' => "required|string|max:100|unique:instruments,serial_number,{$instrument->id}",
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:30',
            'category_id'   => 'required|exists:instrument_categories,id',
            'product_id'    => 'nullable|exists:products,id',
            'depends_on_id' => 'nullable|exists:instruments,id',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $instrument->update($validated);

        return redirect()
            ->route('instruments.show', $instrument)
            ->with('success', 'Instrumento actualizado.');
    }

    public function destroy(Instrument $instrument)
    {
        if ($instrument->kit_id) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar un instrumento que pertenece a un kit. Remuévelo del kit primero.');
        }

        if ($instrument->status === 'in_surgery') {
            return redirect()->back()
                ->with('error', 'No se puede eliminar un instrumento que está en cirugía.');
        }

        $instrument->delete();

        return redirect()
            ->route('instruments.index')
            ->with('success', 'Instrumento eliminado.');
    }

    /**
     * Cambiar estado
     */
    public function updateStatus(Request $request, Instrument $instrument)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,maintenance,retired,lost',
            'notes'  => 'nullable|string|max:500',
        ]);

        match ($validated['status']) {
            'maintenance' => $instrument->sendToMaintenance($validated['notes'] ?? null),
            'available'   => $instrument->returnFromMaintenance(),
            'lost'        => $instrument->markAsLost($validated['notes'] ?? null),
            'retired'     => $instrument->retire(),
        };

        return redirect()->back()
            ->with('success', "Estado actualizado a: {$instrument->status_color['label']}");
    }

    /**
     * API: Buscar instrumentos disponibles (para asignar a kits)
     */
    public function searchAvailable(Request $request)
    {
        $search = $request->input('search', '');
        $excludeKitId = $request->input('exclude_kit_id');

        $query = Instrument::available()->loose();

        if ($search) {
            $query->search($search);
        }

        $instruments = $query->with('category')->limit(20)->get();

        return response()->json([
            'results' => $instruments->map(fn($i) => [
                'id'   => $i->id,
                'text' => "{$i->serial_number} — {$i->name} ({$i->category->name})",
            ]),
        ]);
    }
}