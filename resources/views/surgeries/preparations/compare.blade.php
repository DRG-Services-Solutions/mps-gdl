<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-balance-scale mr-2 text-purple-600"></i>
                    {{ __('Comparación de Surtido') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-indigo-600">{{ $surgery->code }}</span> 
                    <span class="mx-2">|</span> 
                    <i class="fas fa-user-injured mr-1"></i> {{ $surgery->patient_name }}
                    <!-- TEST: {{  $product->count() }} -->
                </p>
            </div>
            <div class="flex items-center space-x-3">
                @if(!$preparation->isComplete())
                    <a href="{{ route('surgeries.preparations.picking', $surgery) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md transition-all">
                        <i class="fas fa-barcode mr-2"></i> Ir a Surtir Faltantes
                    </a>
                @else
                    <form action="{{ route('surgeries.preparations.verify', $surgery) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-md transition-all">
                            <i class="fas fa-check-double mr-2"></i> Verificar y Finalizar
                        </button>
                    </form>
                @endif
                <a href="{{ route('surgeries.show', $surgery) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition-all">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Tarjetas de Resumen con Lógica de Cantidades Totales --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Productos diferentes</p>
                            <p class="text-3xl font-black text-gray-900 mt-1">{{ $preparation->items->count() }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-gray-400">
                            <i class="fas fa-tags text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Cantidad de Piezas Totales</p>
                            <p class="text-3xl font-black text-indigo-600 mt-1">
                                {{ $preparation->items->sum('quantity_required') }}
                            </p>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-3 text-indigo-600">
                            <i class="fas fa-layer-group text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Piezas en Pre-Armado</p>
                            <p class="text-3xl font-black text-blue-600 mt-1">
                                {{  $product->count() }}
                                
                               
                            </p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-3 text-blue-600">
                            <i class="fas fa-box text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Piezas Faltantes</p>
                            @php
                                $totalFaltante = $preparation->items->sum(function($item) {
                                    return max(0, $item->quantity_required - ($item->quantity_in_package));
                                });
                                 
                            @endphp
                            <p class="text-3xl font-black text-red-600 mt-1">{{ $totalFaltante }}</p>
                        </div>
                        
                        <div class="bg-red-50 rounded-lg p-3 text-red-600">
                            <i class="fas fa-exclamation-circle text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla de Comparación Detallada --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ tab: 'all' }">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Producto</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Requerido</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-blue-500 uppercase tracking-widest">En Paquete</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-indigo-500 uppercase tracking-widest">Manual</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-red-500 uppercase tracking-widest">Faltante</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($preparation->items as $item)
                                @php
                                    $surtidoReal = $item->quantity_in_package + ($item->quantity_picked ?? 0);
                                    $faltaEsteItem = max(0, $item->quantity_required - $surtidoReal);
                                    $isOk = $faltaEsteItem <= 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900 leading-tight">{{ $item->product->name }}</div>
                                        <div class="text-[10px] font-mono text-gray-400 mt-1">REF: {{ $item->product->sku ?? $item->product->code }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-black text-gray-700">{{ $item->quantity_required }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-blue-600 {{ $item->quantity_in_package > 0 ? 'bg-blue-50/50' : '' }}">
                                        {{ $item->quantity_in_package }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-indigo-600">{{ $item->quantity_picked ?? 0 }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-2 py-1 rounded-md text-sm font-black {{ $faltaEsteItem > 0 ? 'bg-red-600 text-white' : 'text-gray-300' }}">
                                            {{ $faltaEsteItem }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($isOk)
                                            <span class="text-[10px] font-black text-green-600 bg-green-50 px-2 py-1 rounded border border-green-100 uppercase">Completo</span>
                                        @else
                                            <span class="text-[10px] font-black text-orange-600 bg-orange-50 px-2 py-1 rounded border border-orange-100 uppercase">Incompleto</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>