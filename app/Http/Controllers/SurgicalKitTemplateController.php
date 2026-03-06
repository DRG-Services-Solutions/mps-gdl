<?php

namespace App\Http\Controllers;

use App\Models\SurgicalKitTemplate;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSurgicalKitTemplateRequest;
use App\Http\Requests\UpdateSurgicalKitTemplateRequest;


class SurgicalKitTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = SurgicalKitTemplate::with('items')->paginate(10);
        return view('surgical_kit_templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('surgical_kit_templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurgicalKitTemplateRequest $request)
    {
        $validatedData = $request->validated();
        $template = SurgicalKitTemplate::create($validatedData);

        return redirect()->route('surgical-kit-templates.show', $template)->with('success', '¡Receta de kit quirúrgico creada con éxito!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SurgicalKitTemplate $surgicalKitTemplate)
    {
        $surgicalKitTemplate->load('items');
        return view('surgical_kit_templates.show', compact('surgicalKitTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SurgicalKitTemplate $surgicalKitTemplate)
    {
        return view('surgical_kit_templates.edit', compact('surgicalKitTemplate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurgicalKitTemplateRequest $request, SurgicalKitTemplate $surgicalKitTemplate)
    {
        $validatedData = $request->validated();
        $surgicalKitTemplate->update($validatedData);

        return redirect()->route('surgical-kit-templates.show', $surgicalKitTemplate)->with('success', '¡Receta de kit quirúrgico actualizada con éxito!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SurgicalKitTemplate $surgicalKitTemplate)
    {
        $surgicalKitTemplate->delete();
        return redirect()->route('surgical-kit-templates.index')->with('success', '¡Receta de kit quirúrgico eliminada con éxito!');
    }
}
