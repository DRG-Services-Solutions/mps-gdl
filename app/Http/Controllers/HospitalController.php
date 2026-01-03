<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\Modality;
use App\Models\LegalEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HospitalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hospitals = Hospital::with('modalities')->get();
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
    public function show(string $id)
    {
        //
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
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'rfc' => 'required|string',
            'configs' => 'required|array',  //Las modalidades deben de provenir de un array desde la UI
            'configs.*.legal_entity_id' => 'required_with:configs.*.selected|nullable|exists:legal_entities,id',
                ], [
            'configs.*.legal_entity_id.required_with' => 'Debes seleccionar una Razon Social para las modalidades activas.',
            'configs.*.legal_entity_id.exists' => 'La Razon Socual no es válida.',
    ]);        

        $hospital = Hospital::findOrFail($id);
        try {
        DB::transaction(function () use ($request, $hospital) {
            $hospital->update($request->only('name', 'rfc', 'is_active'));

            $syncData = [];
            if ($request->has('configs')) {
                foreach ($request->configs as $modalityId => $data) {
                    if (isset($data['selected'])) {
                        $syncData[$modalityId] = [
                            'legal_entity_id' => $data['legal_entity_id']
                        ];
                    }
                }
            }
            $hospital->modalities()->sync($syncData);
        });
            return redirect()->route('hospitals.index')->with('success', 'Hospital actualizado correctamente.');    
            
        } catch (\Exception $e) {
        return back()->withInput()->with('error', 'Ocurrió un error al guardar: ' . $e->getMessage());
        }
    }
        

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
