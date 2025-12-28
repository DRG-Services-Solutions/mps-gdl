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
                <a href="{{ route('surgeries.preparations.picking', $surgery) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-arrow-right mr-2"></i>
                    Continuar al Surtido
                </a>
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
            
            <!-- Resumen de Preparación -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Items -->
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

                <!-- Completos -->
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

                <!-- En Paquete -->
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

                <!-- Faltantes -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Faltantes</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $preparation->items->where('status', 'missing')->count() }}</p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de Progreso General -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-900">Progreso de Preparación</h3>
                    <span class="text-sm font-bold text-purple-600">{{ number_format($preparation->getCompletenessPercentage(), 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-4 rounded-full transition-all duration-500" 
                         style="width: {{ $preparation->getCompletenessPercentage() }}%"></div>
                </div>
            </div>

            <!-- Paquete Asignado -->
            @if($preparation->preAssembledPackage)
            <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-green-500 rounded-full p-3 mr-4">
                            <i class="fas fa-box-open text-white text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-green-900">Paquete Pre-Armado Asignado</h4>
                            <p class="text-sm text-green-700">{{ $preparation->preAssembledPackage->name }} ({{ $preparation->preAssembledPackage->code }})</p>
                        </div>
                    </div>
                    <a href="{{ route('pre-assembled.show', $preparation->preAssembledPackage) }}" 
                       class="text-green-700 hover:text-green-900"
                       target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
            @endif

            <!-- Tabs: Completos / Faltantes -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden" x-data="{ tab: 'all' }">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button @click="tab = 'all'" 
                                :class="{ 'border-indigo-500 text-indigo-600': tab === 'all', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'all' }"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-list mr-2"></i>
                            Todos ({{ $preparation->items->count() }})
                        </button>
                        <button @click="tab = 'complete'" 
                                :class="{ 'border-green-500 text-green-600': tab === 'complete', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'complete' }"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-check-circle mr-2"></i>
                            Completos ({{ $preparation->items->where('status', 'complete')->count() }})
                        </button>
                        <button @click="tab = 'in_package'" 
                                :class="{ 'border-blue-500 text-blue-600': tab === 'in_package', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'in_package' }"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-box mr-2"></i>
                            En Paquete ({{ $preparation->items->where('status', 'in_package')->count() }})
                        </button>
                        <button @click="tab = 'missing'" 
                                :class="{ 'border-red-500 text-red-600': tab === 'missing', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'missing' }"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Faltantes ({{ $preparation->items->where('status', 'missing')->count() }})
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
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Faltante</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($preparation->items as $item)
                            <tr class="hover:bg-gray-50" 
                                x-show="tab === 'all' || tab === '{{ $item->status }}'">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $item->product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->product->code }}</div>
                                            @if($item->is_mandatory)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mt-1">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    Obligatorio
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                        {{ $item->quantity_required }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ $item->quantity_in_package }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($item->quantity_missing > 0)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                            {{ $item->quantity_missing }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->storageLocation)
                                        <span class="inline-flex items-center text-sm text-gray-700">
                                            <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                            {{ $item->storageLocation->code }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusConfig = [
                                            'complete' => ['color' => 'green', 'label' => 'Completo', 'icon' => 'check-circle'],
                                            'in_package' => ['color' => 'blue', 'label' => 'En Paquete', 'icon' => 'box'],
                                            'missing' => ['color' => 'red', 'label' => 'Faltante', 'icon' => 'exclamation-triangle'],
                                            'pending' => ['color' => 'yellow', 'label' => 'Pendiente', 'icon' => 'clock'],
                                        ];
                                        $config = $statusConfig[$item->status] ?? ['color' => 'gray', 'label' => $item->status, 'icon' => 'circle'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                                        <i class="fas fa-{{ $config['icon'] }} mr-1"></i>
                                        {{ $config['label'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900 mb-2">Siguiente Paso</h4>
                        <p class="text-sm text-blue-800">
                            Revisa la comparación y continúa al módulo de surtido para completar los productos faltantes.
                            Los productos marcados como "En Paquete" ya están disponibles en el paquete pre-armado seleccionado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>