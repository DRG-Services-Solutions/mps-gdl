
<div class="overflow-x-auto">
    @if($groupedProducts->count() > 0)
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Producto</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Código</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Stock Disponible</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Detalle</th>
                </tr>
            </thead>
            
            {{-- Envolvemos las filas en un tbody principal --}}
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($groupedProducts as $product)
                    
                    {{-- Cada producto tiene su propio x-data para controlar su acordeón de forma independiente --}}
                    <tbody x-data="{ expanded: false }" class="divide-y divide-gray-100 group">
                        
                        <tr @click="expanded = !expanded" class="hover:bg-gray-50 cursor-pointer transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $product->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $product->code ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                    {{ $product->total_stock }} Unidades
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button type="button" class="text-gray-400 group-hover:text-gray-600 focus:outline-none">
                                    <svg class="w-6 h-6 transform transition-transform duration-200 inline-block" 
                                         :class="{ 'rotate-180': expanded }" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        <tr x-show="expanded" x-collapse x-cloak>
                            <td colspan="4" class="px-0 py-0 bg-gray-50 border-t border-gray-200">
                                <div class="p-6">
                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">
                                        Inventario detallado de {{ $product->name }}
                                    </h4>
                                    
                                    {{-- Sub-tabla con las unidades individuales --}}
                                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Identificador</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Lote</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ubicación</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Caducidad</th>
                                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                @foreach ($product->units as $unit)
                                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                        <td class="px-4 py-3 whitespace-nowrap">
                                                            <div class="flex items-center">
                                                                <div class="flex-shrink-0 h-8 w-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                                                                    @if($unit->epc)
                                                                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                                        </svg>
                                                                    @else
                                                                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                                                        </svg>
                                                                    @endif
                                                                </div>
                                                                <div class="ml-3">
                                                                    <div class="text-sm font-bold text-gray-900">{{ $unit->unique_identifier }}</div>
                                                                    <div class="text-xs text-gray-500">
                                                                        @if ($unit->unique_identifier === $unit->epc) RFID
                                                                        @elseif ($unit->unique_identifier === $unit->serial_number) Serial
                                                                        @elseif ($unit->unique_identifier === $unit->internal_code) Código
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td class="px-4 py-3 whitespace-nowrap">
                                                            <span class="text-sm text-gray-900">{{ $unit->batch_number ?? 'N/A' }}</span>
                                                        </td>

                                                        <td class="px-4 py-3 whitespace-nowrap">
                                                            <span @class([
                                                                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                                                                'bg-green-100 text-green-800'   => $unit->status_color === 'green',
                                                                'bg-blue-100 text-blue-800'     => $unit->status_color === 'blue',
                                                                'bg-yellow-100 text-yellow-800' => $unit->status_color === 'yellow',
                                                                'bg-purple-100 text-purple-800' => $unit->status_color === 'purple',
                                                                'bg-orange-100 text-orange-800' => $unit->status_color === 'orange',
                                                                'bg-red-100 text-red-800'       => $unit->status_color === 'red',
                                                                'bg-gray-100 text-gray-800'     => $unit->status_color === 'gray',
                                                            ])>
                                                                {{ $unit->status_label }}
                                                            </span>
                                                        </td>

                                                        <td class="px-4 py-3">
                                                            <div class="flex items-center text-sm">
                                                                <svg class="w-4 h-4 text-gray-400 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                                </svg>
                                                                <span class="text-gray-900">
                                                                    {{ $unit->currentPackage?->code ?? 'Sin asignar' }}
                                                                </span>
                                                            </div>
                                                        </td>

                                                        <td class="px-4 py-3 whitespace-nowrap">
                                                            @if($unit->expiration_date)
                                                                <div class="text-sm">
                                                                    <div class="text-gray-900">{{ $unit->expiration_date->format('d/m/Y') }}</div>
                                                                    @if($unit->isExpired())
                                                                        <span class="text-xs text-red-600 font-semibold">¡Caducado!</span>
                                                                    @elseif($unit->isExpiringSoon())
                                                                        <span class="text-xs text-orange-600 font-semibold">Próximo a caducar</span>
                                                                    @else
                                                                        <span class="text-xs text-green-600">{{ $unit->days_until_expiration }} días</span>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <span class="text-sm text-gray-400">N/A</span>
                                                            @endif
                                                        </td>

                                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                                            <a href="{{ route('product-units.show', $unit) }}"
                                                               title="Ver detalles"
                                                               class="text-blue-600 hover:text-blue-900 transition-colors duration-150 inline-block">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                                </svg>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron registros</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(request()->hasAny(['search', 'product_id', 'status']))
                    Ajusta los filtros para ver más resultados.
                @else
                    Comienza registrando la entrada de productos.
                @endif
            </p>
            @if(!request()->hasAny(['search', 'product_id', 'status']))
                <div class="mt-6">
                    <a href="{{ route('product-units.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Registrar Entrada
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>

@if($groupedProducts->hasPages())
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        {{ $groupedProducts->links() }}
    </div>
@endif