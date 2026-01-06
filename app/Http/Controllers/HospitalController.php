<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\Modality;
use App\Models\LegalEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HospitalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hospitals = Hospital::with(['configs.modality', 'configs.legalEntity'])
                ->latest()
                ->paginate(10);

        return view('hospitals.index', compact('hospitals'));    
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $modalities = Modality::all();
        $legalEntities = LegalEntity::all();
        return view('hospitals.create', compact('modalities', 'legalEntities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'rfc' => 'required|string',
            'configs' => 'required|array',  //Las modalidades deben de provenir de un array desde la UI
        ]);

        DB::transaction(function () use ($validated) {
            //Creacion de hospital con los datos validados
            $hospital = Hospital::create($validated);

            $syncData = [];
            foreach ($validated['configs'] as $modalityId => $data) {
                if (isset($data['selected'])) { // validamos que el checkbox este marcado
                    $syncData[$modalityId] = [
                        'legal_entity_id' => $data['legal_entity_id'],
                    ];
                }
            }
            $hospital->modalities()->attach($syncData);
        });
        return redirect()->route('hospitals.index')->with('success', 'Hospital configurado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Hospital $hospital)
    {
    $hospital->load(['configs.modality', 'configs.legalEntity']);
    return view('hospitals.show', compact('hospital'));    
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hospital $hospital)
    {
        // Cargamos las relaciones para que estén disponibles en la vista
        $hospital->load('configs'); 
        $modalities = Modality::all();
        $legalEntities = LegalEntity::all();
        
        return view('hospitals.edit', compact('hospital', 'modalities', 'legalEntities'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Validaciones básicas del hospital
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'rfc' => 'required|string|max:13',
            'is_active' => 'required|boolean',
            'configs' => 'required|array',
        ], [
            'name.required' => 'El nombre del hospital es obligatorio.',
            'rfc.required' => 'El RFC es obligatorio.',
            'configs.required' => 'Debe configurar al menos una modalidad.',
        ]);

        // 2. Validar que al menos una modalidad esté seleccionada
        $hasSelectedModality = collect($request->configs)->contains(function($config) {
            return isset($config['selected']);
        });

        if (!$hasSelectedModality) {
            return back()
                ->withErrors(['configs' => 'Debe seleccionar al menos una modalidad (Seguro o Particular).'])
                ->withInput();
        }

        // 3. Validar que las modalidades seleccionadas tengan legal_entity_id válido
        foreach ($request->configs as $modalityId => $data) {
            if (isset($data['selected'])) {
                // Verificar que legal_entity_id no esté vacío
                if (empty($data['legal_entity_id'])) {
                    return back()
                        ->withErrors([
                            "configs.{$modalityId}.legal_entity_id" => 'Debe asignar una Razón Social para esta modalidad.'
                        ])
                        ->withInput();
                }
                
                // Verificar que el legal_entity_id exista en la base de datos
                if (!LegalEntity::where('id', $data['legal_entity_id'])->exists()) {
                    return back()
                        ->withErrors([
                            "configs.{$modalityId}.legal_entity_id" => 'La Razón Social seleccionada no es válida.'
                        ])
                        ->withInput();
                }

                // Opcional: Verificar que la modalidad existe
                if (!Modality::where('id', $modalityId)->exists()) {
                    return back()
                        ->withErrors([
                            "configs.{$modalityId}.selected" => 'La modalidad seleccionada no es válida.'
                        ])
                        ->withInput();
                }
            }
        }

        // 4. Buscar el hospital
        $hospital = Hospital::findOrFail($id);

        // 5. Ejecutar la actualización dentro de una transacción
        try {
            DB::transaction(function () use ($request, $hospital) {
                // Actualizar datos básicos del hospital
                $hospital->update([
                    'name' => $request->name,
                    'rfc' => $request->rfc,
                    'is_active' => $request->is_active,
                ]);

                // Preparar datos para sincronizar modalidades
                $syncData = [];
                
                foreach ($request->configs as $modalityId => $data) {
                    // Solo sincronizar las modalidades que estén seleccionadas
                    if (isset($data['selected'])) {
                        $syncData[$modalityId] = [
                            'legal_entity_id' => $data['legal_entity_id']
                        ];
                    }
                }

                // Sincronizar modalidades (esto elimina las no seleccionadas y actualiza/crea las seleccionadas)
                $hospital->modalities()->sync($syncData);
            });

            return redirect()
                ->route('hospitals.index')
                ->with('success', 'Hospital actualizado correctamente.');
                
        } catch (\Exception $e) {
            // Log del error para debugging (opcional pero recomendado)
            \Log::error('Error al actualizar hospital: ' . $e->getMessage(), [
                'hospital_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error al guardar. Por favor, intente nuevamente.']);
        }
    }

    public function getConfigs(Hospital $hospital)
    {
        $configs = $hospital->configs()
            ->with(['modality', 'legalEntity'])
            ->get();
        
        \Log::info('[SURGERY] Configuraciones cargadas', [
            'hospital_id' => $hospital->id,
            'hospital_name' => $hospital->name,
            'configs_count' => $configs->count(),
        ]);
        
        return response()->json($configs);
    }

        

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
