{{-- resources/views/invoices/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-file-invoice mr-2 text-yellow-600"></i>
                    Remisión {{ $invoice->invoice_number }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $invoice->folio }}</p>
            </div>
            <div class="flex items-center space-x-3">
                invoices.pdf
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-print mr-2"></i>
                    Imprimir
                </button>
                <a href="{{ route('invoices.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Información de la Remisión -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-yellow-100">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle mr-2 text-yellow-600"></i>
                        Información de la Remisión
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Número de Remisión</dt>
                            <dd class="text-sm text-gray-900 font-mono font-semibold">{{ $invoice->invoice_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Folio</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $invoice->folio }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Fecha de Emisión</dt>
                            <dd class="text-sm text-gray-900">{{ $invoice->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Hospital y Cirugía -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Hospital -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-hospital mr-2 text-indigo-600"></i>
                            Hospital
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Razón Social</p>
                                <p class="text-sm text-gray-900 font-semibold">{{ $invoice->hospital->business_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">RFC</p>
                                <p class="text-sm text-gray-900 font-mono">{{ $invoice->hospital->rfc }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Dirección</p>
                                <p class="text-sm text-gray-900">{{ $invoice->hospital->address }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cirugía -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-procedures mr-2 text-indigo-600"></i>
                            Cirugía
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Código de Cirugía</p>
                                <p class="text-sm text-gray-900 font-mono font-semibold">{{ $invoice->surgery->code }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Paciente</p>
                                <p class="text-sm text-gray-900 font-semibold">{{ $invoice->surgery->patient_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Doctor</p>
                                <p class="text-sm text-gray-900">{{ $invoice->surgery->doctor->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Tipo de Cirugía</p>
                                <p class="text-sm text-gray-900">{{ $invoice->surgery->checklist->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-box mr-2 text-yellow-600"></i>
                        Detalle de Productos ({{ $invoice->items->count() }})
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unitario</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($invoice->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $item->product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->product->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">
                                    ${{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                    ${{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Subtotal:</td>
                                <td class="px-6 py-4 text-right text-base font-bold text-gray-900">${{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-semibold text-gray-900">IVA (16%):</td>
                                <td class="px-6 py-4 text-right text-base font-bold text-gray-900">${{ number_format($invoice->iva, 2) }}</td>
                            </tr>
                            <tr class="bg-yellow-50">
                                <td colspan="3" class="px-6 py-4 text-right text-lg font-bold text-gray-900">Total:</td>
                                <td class="px-6 py-4 text-right text-2xl font-bold text-yellow-600">${{ number_format($invoice->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Notas -->
            @if($invoice->notes)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-sticky-note mr-2 text-yellow-600"></i>
                        Notas
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-900">{{ $invoice->notes }}</p>
                </div>
            </div>
            @endif

            <!-- Información del Sistema -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-xs text-gray-500">Generada por</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $invoice->creator->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Fecha de creación</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $invoice->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Última actualización</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $invoice->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</x-app-layout>