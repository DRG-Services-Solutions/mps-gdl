<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use Illuminate\Http\Request;

class ManufacturerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $manufacturers = Manufacturer::latest()->paginate(10);
        return view('manufacturers.index', compact('manufacturers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('manufacturers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:manufacturers,name',
            'description' => 'nullable|string',

        ]);
        Manufacturer::create($validatedData);
        return redirect()->route('manufacturers.index')
                         ->with('success', 'Fabricante agregado crrectamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Manufacturer $manufacturer)
    {
        manufacturer->load('products');
        return view('manufacturers.show', ('manufacturer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Manufacturer $manufacturer)
    {
        return view('manufacturers.edit', compact('manufacturer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Manufacturer $manufacturer)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:manufacturers,name,' . $manufacturer->id,
            'description' => 'nullable|string',
        ]);

        $manufacturer->update($request->all());

        return redurect()->route('manufacturers.index')
                         ->with('success', 'Manufacturer updated successfully.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manufacturer $manufacturer)
    {
        $manufacturer->delete();
        return redirect()->route('manufacturers.index')
                         ->with('success', 'Manufacturer deleted successfully.');
    }
}
