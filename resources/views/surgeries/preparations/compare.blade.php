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
                    <i class="fas fa-user-injured mr-1"></i> {{ $surgery->patient_name }}
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

            {{-- Tarjetas de Resumen --}}
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
                        @if($packageExtras->isNotEmpty())
                        <button @click="tab = 'extras'" 
                                :class="tab === 'extras' ? 'border-yellow-600 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-box-open mr-1"></i>
                            Sobrantes en Paquete ({{ $packageExtras->count() }})
                        </button>
                        @endif
                        @if(count($excludedByConditionals) > 0)
                        <button @click="tab = 'excluded'" 
                                :class="tab === 'excluded' ? 'border-orange-600 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-ban mr-1"></i>
                            Excluidos ({{ count($excludedByConditionals) }})
                        </button>
                        @endif
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
                                <th class="px-6 py-4 text-center text-xs font-bold text-purple-600 uppercase tracking-wider">
                                    <i class="fas fa-filter mr-1"></i>
                                    Condicional
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
                                                     {{ $item->product->code }}
                                                </div>
                                                <div class="text-xs font-mono text-gray-500 mt-1">
                                                   {{ $item->product->name }} 
                                                </div>
                                                @if($item->notes && str_starts_with($item->notes, 'Dependencia de:'))
                                                    <div class="mt-1">
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-blue-100 text-blue-700">
                                                            <i class="fas fa-link"></i> {{ $item->notes }}
                                                        </span>
                                                    </div>
                                                @elseif($item->notes && str_starts_with($item->notes, 'Reemplazo de:'))
                                                    <div class="mt-1">
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-orange-100 text-orange-700">
                                                            <i class="fas fa-exchange-alt"></i> {{ $item->notes }}
                                                        </span>
                                                    </div>
                                                @endif
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

                                    {{-- Condicional aplicado --}}
                                    <td class="px-6 py-4">
                                        @php
                                            $applicable = $item->getApplicableConditional();
                                        @endphp

                                        @if($applicable)
                                            <div class="w-full max-w-xs mx-auto">
                                                <div class="flex items-start gap-1.5 px-2.5 py-1.5 rounded-lg
                                                    @switch($applicable->action_type)
                                                        @case('exclude')        bg-red-50 border border-red-200 @break
                                                        @case('adjust_quantity') bg-amber-50 border border-amber-200 @break
                                                        @case('add_product')     bg-purple-50 border border-purple-200 @break
                                                        @case('replace')         bg-orange-50 border border-orange-200 @break
                                                        @case('add_dependency')  bg-blue-50 border border-blue-200 @break
                                                        @default                 bg-gray-50 border border-gray-200
                                                    @endswitch
                                                ">
                                                    <div class="min-w-0">
                                                        <p class="text-[10px] font-bold uppercase tracking-wide leading-tight
                                                            @switch($applicable->action_type)
                                                                @case('exclude')        text-red-700 @break
                                                                @case('adjust_quantity') text-amber-700 @break
                                                                @case('add_product')     text-purple-700 @break
                                                                @case('replace')         text-orange-700 @break
                                                                @case('add_dependency')  text-blue-700 @break
                                                                @default                 text-gray-700
                                                            @endswitch
                                                        ">
                                                            @switch($applicable->action_type)
                                                                @case('adjust_quantity')
                                                                    <i class="fas fa-edit mr-0.5"></i> Ajuste: {{ $applicable->quantity_override }} uds.
                                                                    @break
                                                                @case('add_product')
                                                                    <i class="fas fa-plus-circle mr-0.5"></i> +{{ $applicable->additional_quantity }} uds.
                                                                    @break
                                                                @case('exclude')
                                                                    <i class="fas fa-times-circle mr-0.5"></i> Excluido
                                                                    @break
                                                                @case('replace')
                                                                    <i class="fas fa-exchange-alt mr-0.5"></i> Reemplazar
                                                                    @break
                                                                @case('add_dependency')
                                                                    <i class="fas fa-link mr-0.5"></i> Dependencia ×{{ $applicable->dependency_quantity }}
                                                                    @break
                                                            @endswitch
                                                        </p>

                                                        {{-- Criterios --}}
                                                        <p class="text-[10px] mt-0.5 leading-tight truncate text-gray-500">
                                                            @if($applicable->doctor)
                                                                Dr. {{ $applicable->doctor->first_name }} {{ $applicable->doctor->last_name }}
                                                            @endif
                                                            @if($applicable->hospital)
                                                                @if($applicable->doctor) · @endif
                                                                {{ $applicable->hospital->name }}
                                                            @endif
                                                            @if($applicable->modality)
                                                                @if($applicable->doctor || $applicable->hospital) · @endif
                                                                {{ $applicable->modality->name }}
                                                            @endif
                                                        </p>

                                                        {{-- Producto objetivo --}}
                                                        @if($applicable->targetProduct)
                                                            <p class="text-[10px] mt-0.5 truncate italic text-gray-400">
                                                                → {{ $applicable->targetProduct->name }}
                                                            </p>
                                                        @endif

                                                        {{-- Badge cortesía --}}
                                                        @if($applicable->exclude_from_invoice)
                                                            <span class="inline-flex items-center mt-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-700">
                                                                <i class="fas fa-gift mr-0.5"></i> Sin cargo
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center">
                                                <span class="text-xs text-gray-300 italic">— Estándar —</span>
                                            </div>
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

                            {{-- Sobrantes del paquete --}}
                            @if($packageExtras->isNotEmpty())
                                @foreach($packageExtras as $productId => $extra)
                                    <tr class="bg-yellow-50 hover:bg-yellow-100 transition-colors"
                                        x-show="tab === 'all' || tab === 'extras'">
                                        <td class="px-6 py-4">
                                            <div class="flex items-start">
                                                <i class="fas fa-box-open text-yellow-500 mr-2 mt-1" title="Sobrante en paquete"></i>
                                                <div>
                                                    <div class="text-sm font-bold text-gray-900 leading-tight">
                                                        {{ $extra['product']->code ?? '-' }}
                                                    </div>
                                                    <div class="text-xs font-mono text-gray-500 mt-1">
                                                        {{ $extra['product']->name ?? 'Producto desconocido' }}
                                                    </div>
                                                    <div class="mt-1">
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-yellow-200 text-yellow-800">
                                                            <i class="fas fa-info-circle"></i> No requerido por checklist
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gray-100 text-gray-400 font-black text-lg">0</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-yellow-100 text-yellow-700 font-black text-lg">{{ $extra['total_quantity'] }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gray-50 text-gray-400 font-black text-lg">0</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gray-50 text-gray-400 font-black text-lg">0</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-xs text-yellow-600 italic">Sobrante</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                <i class="fas fa-box-open mr-1"></i> Extra
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                            {{-- Productos Excluidos por Condicionales --}}
                            @if(count($excludedByConditionals) > 0)
                                @foreach($excludedByConditionals as $excluded)
                                    @php
                                        $actionStyle = match($excluded['action_type'] ?? '') {
                                            'exclude' => [
                                                'row'   => 'bg-red-50 hover:bg-red-100',
                                                'badge' => 'bg-red-100 text-red-800 border-red-200',
                                                'icon'  => 'fa-times-circle',
                                                'label' => 'Excluido',
                                            ],
                                            'replace' => [
                                                'row'   => 'bg-orange-50 hover:bg-orange-100',
                                                'badge' => 'bg-orange-100 text-orange-800 border-orange-200',
                                                'icon'  => 'fa-exchange-alt',
                                                'label' => 'Reemplazado',
                                            ],
                                            default => [
                                                'row'   => 'bg-gray-50 hover:bg-gray-100',
                                                'badge' => 'bg-gray-100 text-gray-800 border-gray-200',
                                                'icon'  => 'fa-ban',
                                                'label' => 'Removido',
                                            ],
                                        };
                                    @endphp
                                    <tr class="{{ $actionStyle['row'] }} transition-colors"
                                        x-show="tab === 'all' || tab === 'excluded'">
                                        {{-- Producto --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-start">
                                                <i class="fas fa-ban text-orange-400 mr-2 mt-1" title="Excluido por condicional"></i>
                                                <div>
                                                    <div class="text-sm font-bold text-gray-900 leading-tight line-through opacity-60">
                                                        {{ $excluded['product_code'] }}
                                                    </div>
                                                    <div class="text-xs font-mono text-gray-500 mt-1 line-through opacity-60">
                                                        {{ $excluded['product_name'] }}
                                                    </div>
                                                    @if($excluded['target_product'])
                                                        <div class="mt-1">
                                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-orange-100 text-orange-700">
                                                                <i class="fas fa-arrow-right"></i> Reemplazado por: {{ $excluded['target_product'] }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    @if($excluded['description'])
                                                        <p class="text-[10px] text-gray-400 mt-1 italic">{{ $excluded['description'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Cantidad Base (tachada) --}}
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gray-100 text-gray-400 font-black text-lg line-through">
                                                {{ $excluded['base_quantity'] }}
                                            </span>
                                        </td>

                                        {{-- En Paquete --}}
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gray-50 text-gray-300 font-black text-lg">—</span>
                                        </td>

                                        {{-- Surtidas --}}
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gray-50 text-gray-300 font-black text-lg">—</span>
                                        </td>

                                        {{-- Faltante --}}
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gray-50 text-gray-300 font-black text-lg">—</span>
                                        </td>

                                        {{-- Condicional --}}
                                        <td class="px-6 py-4 text-center">
                                            <div class="w-full max-w-xs mx-auto">
                                                <div class="px-2.5 py-1.5 rounded-lg border {{ $actionStyle['badge'] }}">
                                                    <p class="text-[10px] font-bold uppercase tracking-wide">
                                                        <i class="fas {{ $actionStyle['icon'] }} mr-0.5"></i>
                                                        {{ $actionStyle['label'] }}
                                                    </p>
                                                    @if($excluded['criteria'])
                                                        <p class="text-[10px] mt-0.5 text-gray-500 truncate">{{ $excluded['criteria'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Estado --}}
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold {{ $actionStyle['badge'] }} border">
                                                <i class="fas {{ $actionStyle['icon'] }} mr-1"></i>
                                                {{ $actionStyle['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
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

        </div>
    </div>
</x-app-layout>