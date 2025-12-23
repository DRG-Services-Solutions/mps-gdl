<?php
// app/Http/Controllers/InvoiceController.php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ScheduledSurgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; // Necesitarás: composer require barryvdh/laravel-dompdf

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::query()
            ->with(['scheduledSurgery', 'hospital', 'creator']);

        // Filtros
        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('hospital_id')) {
            $query->where('hospital_id', $request->hospital_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhere('hospital_name', 'like', '%' . $request->search . '%');
            });
        }

        $invoices = $query->latest('invoice_date')->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Crear remisión desde una cirugía
     */
    public function createFromSurgery(ScheduledSurgery $surgery)
    {
        // Verificar que la cirugía esté lista
        if (!$surgery->isReady()) {
            return back()->with('error', 'La cirugía debe estar preparada para generar la remisión.');
        }

        // Verificar que no tenga remisión ya
        if ($surgery->invoice) {
            return redirect()
                ->route('invoices.show', $surgery->invoice)
                ->with('info', 'Esta cirugía ya tiene una remisión.');
        }

        $surgery->load([
            'hospital',
            'preparation.items.product',
            'preparation.items.units.productUnit'
        ]);

        return view('invoices.create', compact('surgery'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ScheduledSurgery $surgery)
    {
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.product_unit_ids' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Crear remisión
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'scheduled_surgery_id' => $surgery->id,
                'hospital_id' => $surgery->hospital_id,
                'hospital_name' => $surgery->hospital->business_name,
                'hospital_address' => $surgery->hospital->address,
                'hospital_rfc' => $surgery->hospital->rfc,
                'invoice_date' => $validated['invoice_date'],
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Crear items
            foreach ($validated['items'] as $itemData) {
                $product = \App\Models\Product::find($itemData['product_id']);
                
                $item = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->commercial_name,
                    'product_unit_ids' => $itemData['product_unit_ids'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                ]);

                // Calcular totales del item
                $item->calculateTotals();
            }

            // Calcular totales de la remisión
            $invoice->calculateTotals();

            DB::commit();

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Remisión creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear remisión: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'scheduledSurgery.checklist',
            'hospital',
            'items.product',
            'creator'
        ]);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Emitir remisión (cambiar a issued)
     */
    public function issue(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Solo se pueden emitir remisiones en borrador.');
        }

        $invoice->issue();

        return back()->with('success', 'Remisión emitida exitosamente.');
    }

    /**
     * Cancelar remisión
     */
    public function cancel(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'No se puede cancelar una remisión pagada.');
        }

        $invoice->cancel();

        return back()->with('success', 'Remisión cancelada exitosamente.');
    }

    /**
     * Marcar como pagada
     */
    public function markAsPaid(Invoice $invoice)
    {
        if ($invoice->status !== 'issued') {
            return back()->with('error', 'Solo se pueden marcar como pagadas las remisiones emitidas.');
        }

        $invoice->markAsPaid();

        return back()->with('success', 'Remisión marcada como pagada.');
    }

    /**
     * Generar PDF
     */
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load([
            'scheduledSurgery.checklist',
            'hospital',
            'items.product',
        ]);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));

        return $pdf->download("remision-{$invoice->invoice_number}.pdf");
    }

    /**
     * Vista previa PDF
     */
    public function previewPdf(Invoice $invoice)
    {
        $invoice->load([
            'scheduledSurgery.checklist',
            'hospital',
            'items.product',
        ]);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));

        return $pdf->stream();
    }
}