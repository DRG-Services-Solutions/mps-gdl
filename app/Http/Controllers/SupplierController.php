<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index()
    {
        $suppliers = Supplier::withCount('purchaseOrders')
            ->latest()
            ->paginate(15);
        
        return view('suppliers.index', compact('suppliers'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:suppliers,code',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:suppliers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'rfc' => 'nullable|string|max:250',
            'razon_social' => 'nullable|string|max:250',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create($validated);

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('success', 'Proveedor creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load('purchaseOrders');
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:suppliers,code,' . $supplier->id,
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:suppliers,email,' . $supplier->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'rfc' => 'nullable|string|max:255',
            'razon_social' => 'nullable|string|max:250',
        ]);

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('success', 'Proveedor actualizado exitosamente.');    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Proveedor eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', 'No se pudo eliminar el proveedor. Puede tener órdenes de compra asociadas.');
        }
    }

    public function toggleStatus(Supplier $supplier)
        {
        $supplier->update(['is_active' => !$supplier->is_active]);
        
        $status = $supplier->is_active ? 'activado' : 'desactivado';
        return back()->with('success', "Proveedor {$status} exitosamente.");
        }

}
