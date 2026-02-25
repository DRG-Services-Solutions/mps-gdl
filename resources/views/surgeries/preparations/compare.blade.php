{{-- resources/views/surgeries/preparations/compare.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-balance-scale mr-2 text-purple-600"></i>
                    Comparación: Checklist vs Paquete
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-indigo-600">{{ $surgery->code }}</span> 
                    <span class="mx-2">|</span> 
                    <i class="fas fa-user-injured mr-1"></i> {{ $surgery->patient->name ?? $surgery->patient_name }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                @if($summary['pending_items'] > 0)
                    <a href="{{ route('surgeries.preparations.picking', $surgery) }}" 
                       class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-barcode mr-2"></i> 
                        Surtir Faltantes ({{ $summary['pending_items'] }})
                    </a>
                @else
                    <form action="{{ route('surgeries.preparations.verify', $surgery) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-check-double mr-2"></i> 
                            Verificar y Finalizar
                        </button>
                    </form>
                @endif
                <a href="{{ route('surgeries.show', $surgery) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Información del Paquete Seleccionado --}}
            @if($preparation->preAssembledPackage)
            <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white bg-opacity-20 rounded-lg p-4">
                            <i class="fas fa-box-open text-4xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-purple-100 uppercase tracking-wider">Paquete Pre-Armado</p>
                            <h3 class="text-2xl font-bold">{{ $preparation->preAssembledPackage->name }}</h3>
                            <p class="text-sm text-purple-100 font-mono">{{ $preparation->preAssembledPackage->code }}</p>
                        </div>
                    </div>
                    @if($preparation->preAssembledPackage->storageLocation)
                    <div class="text-right">
                        <p class="text-sm text-purple-100">Ubicación</p>
                        <p class="text-xl font-bold">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            {{ $preparation->preAssembledPackage->storageLocation->code }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Barra de Progreso General --}}
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Progreso de Completitud</h3>
                        <p class="text-sm text-gray-600">
                            {{ $summary['total_quantity_satisfied'] }} de {{ $summary['total_quantity_required'] }} unidades completas
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold text-indigo-600">{{ number_format($summary['completion_percentage'], 1) }}%</p>
                    </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-4 rounded-full transition-all duration-700 ease-out flex items-center justify-end pr-2" 
                         style="width: {{ $summary['completion_percentage'] }}%">
                        @if($summary['completion_percentage'] > 10)
                            <span class="text-xs font-bold text-white">{{ number_format($summary['completion_percentage'], 0) }}%</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tarjetas de Resumen Mejoradas --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Total Requerido --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-gray-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Requerido</p>
                            <p class="text-3xl font-black text-gray-900">{{ $summary['total_quantity_required'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $summary['total_items'] }} productos diferentes</p>
                        </div>
                        <div class="bg-gray-100 rounded-lg p-3">
                            <i class="fas fa-clipboard-list text-2xl text-gray-600"></i>
                        </div>
                    </div>
                </div>

                {{-- En Paquete --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1">En Paquete</p>
                            <p class="text-3xl font-black text-blue-600">{{ $summary['total_quantity_in_package'] }}</p>
                            <p class="text-xs text-blue-600 mt-1">{{ $summary['in_package_items'] }} productos completos</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-3">
                            <i class="fas fa-box text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                {{-- Surtidas Manualmente --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-green-600 uppercase tracking-wider mb-1">Surtidas</p>
                            <p class="text-3xl font-black text-green-600">{{ $summary['total_quantity_picked'] }}</p>
                            <p class="text-xs text-green-600 mt-1">Escaneadas con RFID</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3">
                            <i class="fas fa-hand-holding text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>

                {{-- Faltantes --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-red-600 uppercase tracking-wider mb-1">Faltantes</p>
                            <p class="text-3xl font-black text-red-600">{{ $summary['total_quantity_missing'] }} Piezas</p> 
                            <p class="text-xs text-red-600 mt-1">{{ $summary['pending_items'] }} productos diferentes incompletos</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-3">
                            <i class="fas fa-exclamation-circle text-2xl text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alertas Importantes --}}
            @if($summary['mandatory_pending'] > 0)
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-bold text-red-800">
                            {{ $summary['mandatory_pending'] }} Productos Diferentes
                        </h4>
                        <p class="text-sm text-red-700 mt-1">
                            Con {{ $summary['total_quantity_missing'] }} Unidades faltantes.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($summary['pending_items'] > 0)
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-yellow-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-bold text-yellow-800">
                            {{ $summary['pending_items'] }} productos opcionales pendientes
                        </h4>
                        <p class="text-sm text-yellow-700 mt-1">
                            Puedes continuar sin estos productos, pero se recomienda completarlos.
                        </p>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-bold text-green-800">
                            ¡Preparación completa!
                        </h4>
                        <p class="text-sm text-green-700 mt-1">
                            Todos los productos están listos. Puedes proceder a verificar y finalizar.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Tabs de Filtrado --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" x-data="{ tab: 'all' }">
                {{-- Tab Headers --}}
                <div class="border-b border-gray-200 bg-gray-50">
                    <nav class="flex space-x-4 px-6 py-3">
                        <button @click="tab = 'all'" 
                                :class="tab === 'all' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors">
                            Todos ({{ $summary['total_items'] }})
                        </button>
                        <button @click="tab = 'complete'" 
                                :class="tab === 'complete' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-check-circle mr-1"></i>
                            Completos ({{ $itemsComplete->count() }})
                        </button>
                        <button @click="tab = 'pending'" 
                                :class="tab === 'pending' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Pendientes ({{ $itemsPending->count() }})
                        </button>
                    </nav>
                </div>

                {{-- Tabla de Comparación --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Producto
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-clipboard-list mr-1"></i>
                                    Requerido
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-blue-600 uppercase tracking-wider">
                                    <i class="fas fa-box mr-1"></i>
                                    En Paquete
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-green-600 uppercase tracking-wider">
                                    <i class="fas fa-hand-holding mr-1"></i>
                                    Surtidas
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-red-600 uppercase tracking-wider">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Faltante
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($preparation->items as $item)
                                @php
                                    $isComplete = $item->quantity_missing <= 0;
                                    $showInAll = true;
                                    $showInComplete = $isComplete;
                                    $showInPending = !$isComplete;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors" 
                                    x-show="tab === 'all' || (tab === 'complete' && {{ $showInComplete ? 'true' : 'false' }}) || (tab === 'pending' && {{ $showInPending ? 'true' : 'false' }})">
                                    
                                    {{-- Producto --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-start">
                                            @if($item->is_mandatory)
                                                <i class="fas fa-star text-yellow-500 mr-2 mt-1" title="Obligatorio"></i>
                                            @endif
                                            <div>
                                                <div class="text-sm font-bold text-gray-900 leading-tight">
                                                    {{ $item->product->name }}
                                                </div>
                                                <div class="text-xs font-mono text-gray-500 mt-1">
                                                    {{ $item->product->code }}
                                                </div>
                                                @if($item->storageLocation)
                                                    <div class="text-xs text-indigo-600 mt-1">
                                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                                        {{ $item->storageLocation->code }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Requerido --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gray-100 text-gray-900 font-black text-lg">
                                            {{ $item->quantity_required }}
                                        </span>
                                    </td>

                                    {{-- En Paquete --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg {{ $item->quantity_in_package > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-50 text-gray-400' }} font-black text-lg">
                                            {{ $item->quantity_in_package }}
                                        </span>
                                    </td>

                                    {{-- Surtidas --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg {{ $item->quantity_picked > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-50 text-gray-400' }} font-black text-lg">
                                            {{ $item->quantity_picked }}
                                        </span>
                                    </td>

                                    {{-- Faltante --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($item->quantity_missing > 0)
                                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-red-100 text-red-700 font-black text-lg animate-pulse">
                                                {{ $item->quantity_missing }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gray-50 text-gray-400 font-black text-lg">
                                                0
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Estado --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($isComplete)
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Completo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-orange-100 text-orange-800 border border-orange-200">
                                                <i class="fas fa-clock mr-1"></i>
                                                Incompleto
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Siguiente Paso --}}
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-6 border border-indigo-200">
                <div class="flex items-start">
                    <i class="fas fa-lightbulb text-indigo-600 text-3xl mr-4"></i>
                    <div class="flex-1">
                        <h4 class="text-lg font-bold text-indigo-900 mb-2">
                            Siguiente Paso
                        </h4>
                        @if($summary['pending_items'] > 0)
                            <p class="text-sm text-indigo-800 mb-4">
                                Tienes <strong>{{ $summary['total_quantity_missing'] }} piezas faltantes</strong> distribuidas en <strong>{{ $summary['pending_items'] }} productos</strong>. 
                                Ve a la sección de surtido para escanear estos productos con el lector RFID.
                            </p>
                            <a href="{{ route('surgeries.preparations.picking', $surgery) }}" 
                               class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                <i class="fas fa-barcode mr-2"></i>
                                Ir a Surtir Faltantes
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        @else
                            <p class="text-sm text-green-800 mb-4">
                                ¡Excelente! Todos los productos están completos. Ahora puedes verificar la preparación para marcarla como lista para cirugía.
                            </p>
                            <form action="{{ route('surgeries.preparations.verify', $surgery) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                    <i class="fas fa-check-double mr-2"></i>
                                    Verificar y Finalizar
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Debug Panel --}}
            {{-- 
            @if(config('app.debug'))
            <div class="bg-gray-900 text-green-400 font-mono text-xs rounded-lg p-4 opacity-80 hover:opacity-100 transition-opacity">
                <h4 class="border-b border-gray-700 mb-3 pb-2 text-white uppercase font-bold flex items-center">
                    <i class="fas fa-bug mr-2"></i> Debug - Resumen de Preparación
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-gray-500 text-[10px] mb-1">ITEMS</p>
                        <p>> Total: <span class="text-yellow-400">{{ $summary['total_items'] }}</span></p>
                        <p>> Completos: <span class="text-green-400">{{ $summary['completed_items'] }}</span></p>
                        <p>> En Paquete: <span class="text-blue-400">{{ $summary['in_package_items'] }}</span></p>
                        <p>> Pendientes: <span class="text-red-400">{{ $summary['pending_items'] }}</span></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-[10px] mb-1">CANTIDADES</p>
                        <p>> Requeridas: <span class="text-yellow-400">{{ $summary['total_quantity_required'] }}</span></p>
                        <p>> En Paquete: <span class="text-blue-400">{{ $summary['total_quantity_in_package'] }}</span></p>
                        <p>> Surtidas: <span class="text-green-400">{{ $summary['total_quantity_picked'] }}</span></p>
                        <p>> Faltantes: <span class="text-red-400">{{ $summary['total_quantity_missing'] }}</span></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-[10px] mb-1">ESTADOS</p>
                        <p>> Prep Status: <span class="text-cyan-400">{{ $preparation->status }}</span></p>
                        <p>> Completitud: <span class="text-cyan-400">{{ $summary['completion_percentage'] }}%</span></p>
                        <p>> Obligatorios: <span class="text-orange-400">{{ $summary['mandatory_pending'] }}</span></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-[10px] mb-1">PAQUETE</p>
                        <p>> ID: <span class="text-purple-400">{{ $preparation->pre_assembled_package_id }}</span></p>
                        <p>> Code: <span class="text-purple-400">{{ $preparation->preAssembledPackage->code ?? 'N/A' }}</span></p>
                        <p>> Contents: <span class="text-purple-400">{{ $packageContents->count() }} productos</span></p>
                    </div>
                </div>
            </div>
            @endif
             --}}

        </div>
    </div>

    {{-- Alpine.js para tabs --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @endpush
</x-app-layout>