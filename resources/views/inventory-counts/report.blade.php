<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('inventory-counts.show', $inventoryCount) }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Reporte de Inventario
                    </h2>
                    <p class="text-sm text-gray-500">{{ $inventoryCount->count_number }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('inventory-counts.export-pdf', $inventoryCount) }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exportar PDF
                </a>
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimir
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12 print:py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full">
            
            {{-- Encabezado del Reporte --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6 print:shadow-none print:border">
                <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 border-b print:bg-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Reporte de Toma de Inventario</h1>
                            <p class="text-lg text-gray-600 mt-1">{{ $inventoryCount->count_number }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                ✓ APROBADO
                            </span>
                            <p class="text-sm text-gray-500 mt-2">
                                {{ $inventoryCount->approved_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Razón Social</p>
                            <p class="font-semibold text-gray-900">{{ $inventoryCount->legal_entities_names }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ubicación</p>
                            <p class="font-semibold text-gray-900">{{ $inventoryCount->location_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tipo de Inventario</p>
                            <p class="font-semibold text-gray-900">{{ $inventoryCount->type_label }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Método de Conteo</p>
                            <p class="font-semibold text-gray-900">{{ $inventoryCount->method_label }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha de Inicio</p>
                            <p class="font-semibold text-gray-900">{{ $inventoryCount->started_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha de Finalización</p>
                            <p class="font-semibold text-gray-900">{{ $inventoryCount->completed_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Creado por</p>
                            <p class="font-semibold text-gray-900">{{ $inventoryCount->createdBy->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Aprobado por</p>
                            <p class="font-semibold text-gray-900">{{ $inventoryCount->approvedBy->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Resumen Ejecutivo --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6 print:shadow-none print:border">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-bold text-gray-900">Resumen Ejecutivo</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-3xl font-bold text-gray-700">{{ $inventoryCount->items->count() }}</p>
                            <p class="text-sm text-gray-500">Total Productos</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-3xl font-bold text-gray-700">{{ $inventoryCount->total_expected }}</p>
                            <p class="text-sm text-gray-500">Unidades Esperadas</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-3xl font-bold text-gray-700">{{ $inventoryCount->total_counted }}</p>
                            <p class="text-sm text-gray-500">Unidades Contadas</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <p class="text-3xl font-bold text-green-600">{{ $inventoryCount->total_matched }}</p>
                            <p class="text-sm text-green-600">Coinciden</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 text-center">
                            <p class="text-3xl font-bold text-red-600">{{ $inventoryCount->total_discrepancies }}</p>
                            <p class="text-sm text-red-600">Discrepancias</p>
                        </div>
                    </div>

                    {{-- Barra de Precisión --}}
                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Precisión del Inventario</span>
                            <span class="text-2xl font-bold 
                                {{ $inventoryCount->accuracy_percentage >= 95 ? 'text-green-600' : '' }}
                                {{ $inventoryCount->accuracy_percentage >= 80 && $inventoryCount->accuracy_percentage < 95 ? 'text-yellow-600' : '' }}
                                {{ $inventoryCount->accuracy_percentage < 80 ? 'text-red-600' : '' }}">
                                {{ number_format($inventoryCount->accuracy_percentage, 2) }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-6">
                            <div class="h-6 rounded-full flex items-center justify-center text-white text-sm font-medium
                                {{ $inventoryCount->accuracy_percentage >= 95 ? 'bg-green-500' : '' }}
                                {{ $inventoryCount->accuracy_percentage >= 80 && $inventoryCount->accuracy_percentage < 95 ? 'bg-yellow-500' : '' }}
                                {{ $inventoryCount->accuracy_percentage < 80 ? 'bg-red-500' : '' }}"
                                 style="width: {{ $inventoryCount->accuracy_percentage }}%">
                                @if($inventoryCount->accuracy_percentage >= 20)
                                    {{ number_format($inventoryCount->accuracy_percentage, 1) }}%
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detalle de Discrepancias --}}
            @php
                $discrepancies = $inventoryCount->items->whereIn('status', ['surplus', 'shortage', 'not_found', 'unexpected']);
            @endphp

            @if($discrepancies->count() > 0)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6 print:shadow-none print:border print:break-before-page">
                    <div class="p-6 bg-red-50 border-b">
                        <h2 class="text-lg font-bold text-gray-900">Detalle de Discrepancias</h2>
                        <p class="text-sm text-gray-600">{{ $discrepancies->count() }} productos con diferencias</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Producto</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Sistema</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Contado</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Diferencia</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Justificación</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($discrepancies as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->product_code }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->product_name }}</td>
                                        <td class="px-4 py-3 text-center text-sm">{{ $item->expected_quantity }}</td>
                                        <td class="px-4 py-3 text-center text-sm">{{ $item->counted_quantity }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-bold {{ $item->difference > 0 ? 'text-blue-600' : 'text-red-600' }}">
                                                {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-xs font-semibold">{{ $item->status_label }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $item->discrepancy_reason ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Ajustes Aplicados --}}
            @if($inventoryCount->adjustments->count() > 0)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6 print:shadow-none print:border">
                    <div class="p-6 bg-indigo-50 border-b">
                        <h2 class="text-lg font-bold text-gray-900">Ajustes Aplicados</h2>
                        <p class="text-sm text-gray-600">{{ $inventoryCount->adjustments->where('status', 'approved')->count() }} ajustes aprobados</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Número</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Producto</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Tipo</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Cantidad</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Aprobado por</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($inventoryCount->adjustments as $adjustment)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $adjustment->adjustment_number }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $adjustment->product->code }}</td>
                                        <td class="px-4 py-3 text-center text-sm">{{ $adjustment->type_label }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-bold {{ $adjustment->is_positive ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $adjustment->formatted_quantity }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-xs font-semibold {{ $adjustment->status === 'approved' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $adjustment->status_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $adjustment->approvedBy->name ?? '-' }}
                                            @if($adjustment->approved_at)
                                                <br><span class="text-xs text-gray-400">{{ $adjustment->approved_at->format('d/m/Y H:i') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Listado Completo de Productos --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden print:shadow-none print:border print:break-before-page">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-bold text-gray-900">Listado Completo de Productos</h2>
                    <p class="text-sm text-gray-600">{{ $inventoryCount->items->count() }} productos contados</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Código</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Producto</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase">Esperado</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase">Contado</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase">Dif.</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($inventoryCount->items->sortBy('product_code') as $item)
                                <tr class="{{ $item->status === 'matched' ? '' : 'bg-yellow-50' }}">
                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $item->product_code }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ Str::limit($item->product_name, 40) }}</td>
                                    <td class="px-3 py-2 text-center">{{ $item->expected_quantity }}</td>
                                    <td class="px-3 py-2 text-center">{{ $item->counted_quantity }}</td>
                                    <td class="px-3 py-2 text-center font-semibold
                                        {{ $item->difference > 0 ? 'text-blue-600' : '' }}
                                        {{ $item->difference < 0 ? 'text-red-600' : '' }}
                                        {{ $item->difference == 0 ? 'text-green-600' : '' }}">
                                        {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                                    </td>
                                    <td class="px-3 py-2 text-center text-xs">{{ $item->status_label }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Firmas (para impresión) --}}
            <div class="bg-white rounded-xl shadow-sm p-6 mt-6 print:shadow-none print:border print:mt-12">
                <div class="grid grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="border-t-2 border-gray-400 pt-2 mt-16">
                            <p class="font-semibold text-gray-900">{{ $inventoryCount->createdBy->name }}</p>
                            <p class="text-sm text-gray-500">Responsable del Conteo</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="border-t-2 border-gray-400 pt-2 mt-16">
                            <p class="font-semibold text-gray-900">{{ $inventoryCount->approvedBy->name }}</p>
                            <p class="text-sm text-gray-500">Aprobado por</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="border-t-2 border-gray-400 pt-2 mt-16">
                            <p class="font-semibold text-gray-900">_______________________</p>
                            <p class="text-sm text-gray-500">Vo.Bo. Gerencia</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
            table { font-size: 10px; }
        }
    </style>
    @endpush
</x-app-layout>
