{{-- resources/views/surgeries/preparations/compare.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-balance-scale mr-2 text-purple-600"></i>
                    {{ __('Comparación de Productos') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $surgery->code }} - {{ $surgery->patient_name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @if(!$preparation->isComplete())
                    <a href="{{ route('surgeries.preparations.picking', $surgery) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-barcode mr-2"></i>
                        Ir a Surtir Faltantes
                    </a>
                @else
                    <form action="{{ route('surgeries.preparations.verify', $surgery) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-check-double mr-2"></i>
                            Verificar y Finalizar
                        </button>
                    </form>
                @endif
                <a href="{{ route('surgeries.show', $surgery) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Items</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $preparation->items->count() }}</p>
                        </div>
                        <div class="bg-indigo-100 rounded-full p-3">
                            <i class="fas fa-list text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Completos</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $preparation->items->where('status', 'complete')->count() }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-check-circle text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">En Paquete</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $preparation->items->where('status', 'in_package')->count() }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-box text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pendientes</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $preparation->items->where('status', 'pending')->count() }}</p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-900">Progreso del Surtido</h3>
                    <span class="text-sm font-bold text-purple-600">{{ number_format($preparation->getCompletenessPercentage(), 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-4 rounded-full transition-all duration-700 shadow-inner" 
                         style="width: {{ $preparation->getCompletenessPercentage() }}%"></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden" x-data="{ tab: 'all' }">
                <div class="border-b border-gray-200 bg-gray-50/50">
                    <nav class="flex -mb-px">
                        <button @click="tab = 'all'" 
                                :class="{ 'border-indigo-500 text-indigo-600 bg-white': tab === 'all', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'all' }"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-all">
                            Todos
                        </button>
                        <button @click="tab = 'pending'" 
                                :class="{ 'border-red-500 text-red-600 bg-white': tab === 'pending', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'pending' }"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-all">
                            Pendientes ({{ $preparation->items->where('status', 'pending')->count() }})
                        </button>
                        <button @click="tab = 'in_package'" 
                                :class="{ 'border-blue-500 text-blue-600 bg-white': tab === 'in_package', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'in_package' }"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-all">
                            En Paquete
                        </button>
                        <button @click="tab = 'complete'" 
                                :class="{ 'border-green-500 text-green-600 bg-white': tab === 'complete', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'complete' }"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-all">
                            Surtidos
                        </button>
                    </nav>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Requerido</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">En Paquete</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Surtido</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Faltante</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($preparation->items as $item)
                            <tr class="hover:bg-gray-50 transition-colors" 
                                x-show="tab === 'all' || tab === '{{ $item->status }}'"
                                x-transition>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $item->product->name }}</div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-xs text-gray-500 font-mono bg-gray-100 px-1 rounded">
                                                    {{ $item->product->sku ?? $item->product->code }}
                                                </span>
                                                @if($item->is_mandatory)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-red-100 text-red-700 uppercase">
                                                        CRÍTICO
                                                    </span>
                                                @endif
                                            </div>
                                            {{-- NUEVO: Ubicación para surtido --}}
                                            @if($item->quantity_missing > 0)
                                                <div class="text-[11px] text-orange-700 mt-2 font-medium">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    Ubicar en: <span class="font-bold">{{ $item->storageLocation->code ?? 'Sin asignar' }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-gray-700 bg-gray-50/30">{{ $item->quantity_required }}</td>
                                <td class="px-6 py-4 text-center text-blue-600 font-medium italic">{{ $item->quantity_in_package }}</td>
                                <td class="px-6 py-4 text-center text-indigo-600 font-bold">{{ $item->quantity_picked }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded {{ $item->quantity_missing > 0 ? 'bg-red-50 text-red-700 font-black ring-1 ring-red-200' : 'text-gray-300' }}">
                                        {{ $item->quantity_missing }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $totalFound = $item->quantity_in_package + $item->quantity_picked;
                                        $statusLabel = $item->status;
                                        
                                        // Lógica visual dinámica
                                        if ($totalFound > 0 && $totalFound < $item->quantity_required) {
                                            $config = ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'hourglass-half', 'label' => 'Incompleto'];
                                        } else {
                                            $config = [
                                                'complete'   => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'check-double', 'label' => 'Completo'],
                                                'in_package' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'box', 'label' => 'En Paquete'],
                                                'pending'    => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'times-circle', 'label' => 'Faltante'],
                                            ][$item->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'clock', 'label' => 'Pendiente'];
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $config['bg'] }} {{ $config['text'] }} shadow-sm">
                                        <i class="fas fa-{{ $config['icon'] }} mr-1.5"></i>
                                        {{ $config['label'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg text-purple-600 mr-4">
                        <i class="fas fa-hospital-alt text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Ubicación de Paquete Físico</h4>
                        <p class="text-xs text-gray-500 italic">El paquete debe ser trasladado a la mesa de preparación principal.</p>
                    </div>
                </div>
                <div class="text-right font-mono text-lg font-bold text-gray-700">
                    {{ $preparation->preAssembledPackage->storageLocation->code ?? 'N/A' }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>