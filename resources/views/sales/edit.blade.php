<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                💰 Detalle de Venta
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('sales.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 transition">
                    ← Volver
                </a>
                @if(!$sale->invoice_number)
                    <a href="{{ route('sales.edit', $sale) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        Editar
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Estado de Facturación -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($sale->invoice_number)
                        <div class="rounded-md bg-green-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Venta Facturada</h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p>Factura: <strong>{{ $sale->invoice_number }}</strong></p>
                                        <p>Fecha: {{ $sale->invoice_date->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-md bg-yellow-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1 md:flex md:justify-between">
                                    <p class="text-sm text-yellow-700">Esta venta aún no ha sido facturada</p>
                                    <p class="mt-3 text-sm md:mt-0 md:ml-6">
                                        <button onclick="document.getElementById('invoice-modal').classList.remove('hidden')"
                                                class="whitespace-nowrap font-medium text-yellow-700 hover:text-yellow-600">
                                            Marcar como Facturada →
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Información General -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información General</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Fecha de Venta</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $sale->sale_date->format('d/m/Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Tipo de Venta</label>
                            <p class="mt-1">
                                @if($sale->sale_type === 'rental')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Renta
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Consignación
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Cotización</label>
                            @if($sale->quotation)
                                <a href="{{ route('quotations.show', $sale->quotation) }}" 
                                   class="mt-1 text-sm text-blue-600 hover:text-blue-900">
                                    {{ $sale->quotation->quotation_number }}
                                </a>
                            @else
                                <p class="mt-1 text-sm text-gray-400">N/A</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Razón Social</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $sale->legalEntity->business_name ?? 'N/A' }}</p>
                            @if($sale->legalEntity)
                                <p class="text-xs text-gray-500">RFC: {{ $sale->legalEntity->rfc }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Creado por</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $sale->createdBy->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Hospital -->
            @if($sale->quotation && $sale->quotation->hospital)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Hospital</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Nombre</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $sale->quotation->hospital->name }}</p>
                        </div>

                        @if($sale->quotation->doctor)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Doctor</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $sale->quotation->doctor->full_name }}</p>
                            @if($sale->quotation->doctor->specialty)
                                <p class="text-xs text-gray-500">{{ $sale->quotation->doctor->specialty }}</p>
                            @endif
                        </div>
                        @endif

                        @if($sale->quotation->surgery_type)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Tipo de Cirugía</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $sale->quotation->surgery_type }}</p>
                        </div>
                        @endif

                        @if($sale->quotation->surgery_date)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Fecha de Cirugía</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $sale->quotation->surgery_date->format('d/m/Y') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Información del Producto -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Producto</h3>
                    @if($sale->productUnit && $sale->productUnit->product)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Nombre</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $sale->productUnit->product->name }}</p>
                            </div>

                            @if($sale->productUnit->product->sku)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">SKU</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $sale->productUnit->product->sku }}</p>
                            </div>
                            @endif

                            @if($sale->productUnit->epc)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">EPC</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $sale->productUnit->epc }}</p>
                            </div>
                            @endif

                            @if($sale->productUnit->serial_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Número de Serie</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $sale->productUnit->serial_number }}</p>
                            </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-400">No hay información del producto disponible</p>
                    @endif
                </div>
            </div>

            <!-- Información Financiera -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información Financiera</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-500">Precio de Costo</label>
                            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($sale->cost_price, 2) }}</p>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-blue-700">Precio de Venta</label>
                            <p class="mt-2 text-2xl font-bold text-blue-900">${{ number_format($sale->sale_price, 2) }}</p>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-green-700">Margen</label>
                            <p class="mt-2 text-2xl font-bold text-green-900">${{ number_format($sale->sale_price - $sale->cost_price, 2) }}</p>
                            <p class="text-sm text-green-600">
                                {{ $sale->cost_price > 0 ? number_format((($sale->sale_price - $sale->cost_price) / $sale->cost_price) * 100, 2) : 0 }}% de ganancia
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notas -->
            @if($sale->notes)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Notas</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $sale->notes }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Acciones -->
            @if(!$sale->invoice_number)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones</h3>
                    <div class="flex gap-4">
                        <form action="{{ route('sales.destroy', $sale) }}" 
                              method="POST" 
                              onsubmit="return confirm('¿Estás seguro de eliminar esta venta?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                Eliminar Venta
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Marcar como Facturada -->
    <div id="invoice-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('sales.mark-as-invoiced', $sale) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Marcar como Facturada</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="invoice_number" class="block text-sm font-medium text-gray-700">Número de Factura *</label>
                                <input type="text" 
                                       name="invoice_number" 
                                       id="invoice_number" 
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="invoice_date" class="block text-sm font-medium text-gray-700">Fecha de Factura *</label>
                                <input type="date" 
                                       name="invoice_date" 
                                       id="invoice_date" 
                                       value="{{ date('Y-m-d') }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Confirmar
                        </button>
                        <button type="button"
                                onclick="document.getElementById('invoice-modal').classList.add('hidden')"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>