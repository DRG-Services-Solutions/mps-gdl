<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors duration-150">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ $product->name }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        <i class="fas fa-barcode mr-1"></i>
                        {{ $product->code }}
                        @if($product->model)
                            <span class="mx-2">•</span>
                            <i class="fas fa-tag mr-1"></i>
                            {{ $product->model }}
                        @endif
                    </p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <a href="{{ route('products.edit', $product) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
                <button @click="showDeleteModal = true"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Eliminar
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showDeleteModal: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg" x-data="{ show: true }" x-show="show" x-transition>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-emerald-600 mr-2"></i>
                            <span class="text-emerald-800 font-medium">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Columna Principal -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Información General -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                                Información General
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Código -->
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Código del Producto</label>
                                    <p class="mt-1 text-base font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-barcode text-gray-400 mr-2"></i>
                                        {{ $product->code }}
                                    </p>
                                </div>

                                <!-- Modelo -->
                                @if($product->model)
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Modelo</label>
                                        <p class="mt-1 text-base font-semibold text-gray-900 flex items-center">
                                            <i class="fas fa-tag text-gray-400 mr-2"></i>
                                            {{ $product->model }}
                                        </p>
                                    </div>
                                @endif

                                <!-- Categoría -->
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Categoría</label>
                                    <p class="mt-1">
                                        @if($product->category)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-folder mr-1"></i>
                                                {{ $product->category->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">Sin categoría</span>
                                        @endif
                                    </p>
                                </div>

                                <!-- Proveedor -->
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Proveedor</label>
                                    <p class="mt-1 text-base font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-building text-gray-400 mr-2"></i>
                                        {{ $product->supplier->name ?? 'Sin proveedor' }}
                                    </p>
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Estado</label>
                                    <p class="mt-1">
                                        @switch($product->status)
                                            @case('active')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Activo
                                                </span>
                                                @break
                                            @case('inactive')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-pause-circle mr-1"></i>
                                                    Inactivo
                                                </span>
                                                @break
                                            @case('discontinued')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-ban mr-1"></i>
                                                    Descontinuado
                                                </span>
                                                @break
                                        @endswitch
                                    </p>
                                </div>

                                <!-- Tipo de Tracking -->
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Tipo de Tracking</label>
                                    <p class="mt-1">
                                        @switch($product->tracking_type)
                                            @case('code')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-boxes mr-1"></i>
                                                    Por Lote/Código
                                                </span>
                                                @break
                                            @case('rfid')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-wifi mr-1"></i>
                                                    RFID Individual
                                                </span>
                                                @break
                                            @case('serial')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-hashtag mr-1"></i>
                                                    Número de Serie
                                                </span>
                                                @break
                                        @endswitch
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    @if($product->description)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-align-left text-indigo-600 mr-2"></i>
                                    Descripción
                                </h3>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Especificaciones Técnicas -->
                    @if($product->specifications)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-cogs text-indigo-600 mr-2"></i>
                                    Especificaciones Técnicas
                                </h3>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $product->specifications }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Inventario Actual (si aplica) -->
                    @if($product->tracking_type === 'code')
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-boxes text-green-600 mr-2"></i>
                                    Inventario Actual
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="text-center">
                                    <p class="text-4xl font-bold text-gray-900">{{ $product->stock_quantity ?? 0 }}</p>
                                    <p class="text-sm text-gray-500 mt-1">Unidades en Stock</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Unidades RFID/Serial (si aplica) -->
                    @if(in_array($product->tracking_type, ['rfid', 'serial']))
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-list text-blue-600 mr-2"></i>
                                    Unidades Registradas
                                </h3>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ $product->units->count() }} unidades
                                </span>
                            </div>
                            
                            @if($product->units->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                @if($product->tracking_type === 'rfid')
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">EPC</th>
                                                @else
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Serial</th>
                                                @endif
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Lote</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ubicación</th>
                                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($product->units->take(10) as $unit)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 text-sm font-mono text-gray-900">
                                                        {{ $product->tracking_type === 'rfid' ? $unit->epc : $unit->serial_number }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">
                                                        {{ $unit->batch_number ?? '-' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">
                                                        {{ $unit->currentLocation->name ?? '-' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @switch($unit->status)
                                                            @case('available')
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                    Disponible
                                                                </span>
                                                                @break
                                                            @case('in_use')
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                    En Uso
                                                                </span>
                                                                @break
                                                            @case('damaged')
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                    Dañado
                                                                </span>
                                                                @break
                                                        @endswitch
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                @if($product->units->count() > 10)
                                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-center">
                                        <p class="text-sm text-gray-500">
                                            Mostrando 10 de {{ $product->units->count() }} unidades
                                        </p>
                                    </div>
                                @endif
                            @else
                                <div class="p-8 text-center">
                                    <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                                    <p class="text-gray-500">No hay unidades registradas aún</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Columna Lateral -->
                <div class="space-y-6">
                    
                    <!-- Características -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Características</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Esterilización -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-syringe text-gray-400 mr-3 text-lg"></i>
                                    <span class="text-sm text-gray-700">Requiere Esterilización</span>
                                </div>
                                @if($product->requires_sterilization)
                                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                                @else
                                    <i class="fas fa-times-circle text-red-400 text-xl"></i>
                                @endif
                            </div>

                            <!-- Refrigeración -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-snowflake text-gray-400 mr-3 text-lg"></i>
                                    <span class="text-sm text-gray-700">Requiere Refrigeración</span>
                                </div>
                                @if($product->requires_refrigeration)
                                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                                @else
                                    <i class="fas fa-times-circle text-red-400 text-xl"></i>
                                @endif
                            </div>

                            <!-- Vida Útil -->
                            @if($product->shelf_life_days)
                                <div class="pt-3 border-t border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-700">Vida Útil</span>
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ $product->shelf_life_days }} días
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <!-- Ciclos de Esterilización -->
                            @if($product->max_sterilization_cycles)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">Ciclos Max. Esterilización</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ $product->max_sterilization_cycles }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Información Adicional</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Creado</span>
                                <span class="font-medium text-gray-900">
                                    {{ $product->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Última actualización</span>
                                <span class="font-medium text-gray-900">
                                    {{ $product->updated_at->format('d/m/Y') }}
                                </span>
                            </div>
                            @if($product->createdBy)
                                <div class="flex items-center justify-between text-sm pt-3 border-t border-gray-200">
                                    <span class="text-gray-600">Creado por</span>
                                    <span class="font-medium text-gray-900">
                                        {{ $product->createdBy->name }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Acciones Rápidas</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <a href="{{ route('products.edit', $product) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                <i class="fas fa-edit mr-2"></i>
                                Editar Producto
                            </a>
                            
                            <button @click="showDeleteModal = true"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                                <i class="fas fa-trash-alt mr-2"></i>
                                Eliminar Producto
                            </button>
                            
                            <a href="{{ route('products.index') }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Volver al Catálogo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación de Eliminación -->
        <div x-show="showDeleteModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="showDeleteModal = false"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showDeleteModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-2xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Confirmar Eliminación
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    ¿Estás seguro de eliminar el producto <strong>{{ $product->name }}</strong>?
                                    Esta acción no se puede deshacer y se eliminarán todos los registros asociados.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <i class="fas fa-trash-alt mr-2"></i>
                                Eliminar
                            </button>
                        </form>
                        <button @click="showDeleteModal = false" 
                                type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>