<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Quotation;
use App\Models\LegalEntity;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Display a listing of sales.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['quotation.hospital', 'legalEntity', 'productUnit.product']);

        // Búsqueda
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%");
        }

        // Filtro por tipo
        if ($request->filled('sale_type')) {
            $query->where('sale_type', $request->sale_type);
        }

        // Filtro por razón social
        if ($request->filled('legal_entity_id')) {
            $query->where('legal_entity_id', $request->legal_entity_id);
        }

        // Filtro por estado de facturación
        if ($request->filled('invoice_status')) {
            if ($request->invoice_status === 'invoiced') {
                $query->whereNotNull('invoice_number');
            } else {
                $query->whereNull('invoice_number');
            }
        }

        // Filtro por rango de fechas
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        $sales = $query->latest('sale_date')->paginate(20);

        // Datos para filtros
        $legalEntities = LegalEntity::where('is_active', true)->orderBy('name')->get();

        return view('sales.index', compact('sales', 'legalEntities'));
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        $sale->load([
            'quotation.hospital',
            'quotation.doctor',
            'legalEntity',
            'productUnit.product',
            'createdBy',
        ]);

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified sale.
     */
    public function edit(Sale $sale)
    {
        // Solo editar si no está facturada
        if ($sale->invoice_number) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', 'No se pueden editar ventas ya facturadas.');
        }

        return view('sales.edit', compact('sale'));
    }

    /**
     * Update the specified sale in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        // Solo editar si no está facturada
        if ($sale->invoice_number) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', 'No se pueden editar ventas ya facturadas.');
        }

        $validated = $request->validate([
            'sale_price' => 'required|numeric|min:0',
            'invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $sale->update($validated);

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Venta actualizada exitosamente.');
    }

    /**
     * Mark sale as invoiced.
     */
    public function markAsInvoiced(Request $request, Sale $sale)
    {
        if ($sale->invoice_number) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', 'Esta venta ya está facturada.');
        }

        $validated = $request->validate([
            'invoice_number' => 'required|string|max:100|unique:sales,invoice_number',
            'invoice_date' => 'required|date',
        ]);

        $sale->update([
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => $validated['invoice_date'],
        ]);

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Venta marcada como facturada exitosamente.');
    }

    /**
     * Remove the specified sale from storage.
     */
    public function destroy(Sale $sale)
    {
        // Solo eliminar si no está facturada
        if ($sale->invoice_number) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', 'No se pueden eliminar ventas ya facturadas.');
        }

        $sale->delete();

        return redirect()
            ->route('sales.index')
            ->with('success', 'Venta eliminada exitosamente.');
    }
}