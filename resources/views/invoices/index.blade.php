{{-- resources/views/invoices/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-file-invoice mr-2 text-yellow-600"></i>
                    {{ __('Remisiones') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Gestiona las remisiones de cirugías</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Estadísticas Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Remisiones -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Remisiones</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $invoices->total() }}</p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <i class="fas fa-file-invoice text-2xl text-yellow-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Subtotal -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Subtotal</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">${{ number_format($totalSubtotal, 2) }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-dollar-sign text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <!-- IVA -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">IVA</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">${{ number_format($totalIva, 2) }}</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <i class="fas fa-percent text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">${{ number_format($totalAmount, 2) }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-coins text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form method="GET" action="{{ route('invoices.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Búsqueda -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i>
                                Buscar
                            </label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Número, paciente, hospital..."
                                   class="w-full rounded-lg border-gray-300 focus:border-yellow-500 focus:ring-yellow-500">
                        </div>

                        <!-- Hospital -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-hospital mr-1"></i>
                                Hospital
                            </label>
                            <select name="hospital_id" 
                                    class="w-full rounded-lg border-gray-300 focus:border-yellow-500 focus:ring-yellow-500">
                                <option value="">Todos</option>
                                @foreach($hospitals as $hospital)
                                    <option value="{{ $hospital->id }}" {{ request('hospital_id') == $hospital->id ? 'selected' : '' }}>
                                        {{ $hospital->business_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fecha Desde -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1"></i>
                                Desde
                            </label>
                            <input type="date" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-yellow-500 focus:ring-yellow-500">
                        </div>

                        <!-- Fecha Hasta -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1"></i>
                                Hasta
                            </label>
                            <input type="date" 
                                   name="date_to" 
                                   value="{{ request('date_to') }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-yellow-500 focus:ring-yellow-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('invoices.index') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 transition-colors">
                            <i class="fas fa-search mr-1"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Listado -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Remisión
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cirugía / Paciente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hospital
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Items
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($invoices as $invoice)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-file-invoice text-yellow-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $invoice->invoice_number }}</div>
                                            <div class="text-xs text-gray-500">{{ $invoice->folio }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-procedures text-gray-400 mr-1"></i>
                                        {{ $invoice->surgery->code }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $invoice->surgery->patient_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $invoice->hospital->business_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ $invoice->items->count() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="text-sm font-semibold text-gray-900">${{ number_format($invoice->total, 2) }}</div>
                                    <div class="text-xs text-gray-500">
                                        Subtotal: ${{ number_format($invoice->subtotal, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm text-gray-900">{{ $invoice->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $invoice->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('invoices.show', $invoice) }}" 
                                       class="text-indigo-600 hover:text-indigo-900"
                                       title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('invoices.pdf', $invoice) }}" 
                                       class="text-red-600 hover:text-red-900"
                                       title="Descargar PDF"
                                       target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <button onclick="printInvoice({{ $invoice->id }})" 
                                            class="text-gray-600 hover:text-gray-900"
                                            title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-file-invoice text-5xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No hay remisiones</p>
                                        <p class="text-sm text-gray-600">Las remisiones se generan desde las cirugías completadas</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($invoices->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $invoices->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function printInvoice(invoiceId) {
            const url = '{{ route("invoices.pdf", ":id") }}'.replace(':id', invoiceId);
            const printWindow = window.open(url, '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        }
    </script>
    @endpush
</x-app-layout>