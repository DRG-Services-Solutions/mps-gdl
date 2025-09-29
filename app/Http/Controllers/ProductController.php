<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\MedicalSpecialty;
use App\Models\SpecialtySubcategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = ProductCategory::all();
        $specialties = MedicalSpecialty::all();
        $subcategories = SpecialtySubcategory::all();

        return view('products.create', compact('categories','specialties','subcategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        dd($request->all());

        $validated = $request->validate([
            'product_category_id' => 'nullable|exists:product_categories,id',
            'medical_specialty_id' => 'nullable|exists:medical_specialties,id',
            'specialty_subcategory_id' => 'nullable|exists:specialty_subcategories,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,code',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'rfid_enabled' => 'nullable|boolean',
            'rfid_tag_id' => 'nullable|string|unique:products,rfid_tag_id',
            'requires_sterilization' => 'nullable|boolean',
            'is_consumable' => 'nullable|boolean',
            'is_single_use' => 'nullable|boolean',
            'unit_cost' => 'nullable|numeric',
            'minimum_stock' => 'required|integer',
            'current_stock' => 'required|integer',
            'storage_location' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date',
            'lot_number' => 'nullable|string|max:255',
            'specifications' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance,retired',
        ]);

        // Manejar checkbox: si no vienen, se deben marcar como false
        $validated['rfid_enabled'] = $request->has('rfid_enabled');
        $validated['is_consumable'] = $request->has('is_consumable');
        $validated['requires_sterilization'] = $request->has('requires_sterilization');
        $validated['is_single_use'] = $request->has('is_single_use');

        // Manejar rfid_tag_id vacío
        $validated['rfid_tag_id'] = $request->input('rfid_tag_id');

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product): View
    {
        $categories = ProductCategory::all();
        $specialties = MedicalSpecialty::all();
        $subcategories = SpecialtySubcategory::all();

        return view('products.edit', compact('product','categories','specialties','subcategories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'product_category_id' => 'nullable|exists:product_categories,id',
            'medical_specialty_id' => 'nullable|exists:medical_specialties,id',
            'specialty_subcategory_id' => 'nullable|exists:specialty_subcategories,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,code,' . $product->id,
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'rfid_enabled' => 'nullable|boolean',
            'rfid_tag_id' => 'nullable|string|unique:products,rfid_tag_id,' . $product->id,
            'requires_sterilization' => 'nullable|boolean',
            'is_consumable' => 'nullable|boolean',
            'is_single_use' => 'nullable|boolean',
            'unit_cost' => 'nullable|numeric',
            'minimum_stock' => 'required|integer',
            'current_stock' => 'required|integer',
            'storage_location' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date',
            'lot_number' => 'nullable|string|max:255',
            'specifications' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance,retired',
        ]);

        
        $validated['rfid_enabled'] = $request->has('rfid_enabled');
        $validated['is_consumable'] = $request->has('is_consumable');
        $validated['requires_sterilization'] = $request->has('requires_sterilization');
        $validated['is_single_use'] = $request->has('is_single_use');

        
        $validated['rfid_tag_id'] = $request->input('rfid_tag_id');

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Producto eliminado correctamente.');
    }
}
