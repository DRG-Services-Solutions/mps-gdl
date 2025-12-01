<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Layouts de Productos') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Gestión de ubicaciones físicas en las bodegas (Estante, Nivel, Posición)') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showDeleteModal: false, layoutToDelete: null }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

                {{-- Encabezado y Botón de Creación --}}
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Listado de Layouts') }}</h3>
                        <p class="text-sm text-gray-600">{{ $productLayouts->total() }} {{ __('ubicaciones registradas') }}</p>
                    </div>
                    <a href="{{ route('product_layouts.create') }}" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('Nueva Ubicación') }}
                    </a>
                </div>

                {{-- Mensajes de Éxito --}}
                @if(session('success'))
                    <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-200" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition 
                         x-init="setTimeout(() => show = false, 5000)">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center text-emerald-800 font-medium">
                                <i class="fas fa-check-circle mr-2 text-emerald-600"></i>
                                {{ session('success') }}
                            </div>
                            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Tabla de Registros --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Código Ubicación
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Bodega
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Coordenadas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Producto Asignado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($productLayouts as $layout)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    
                                    {{-- ID --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-bold bg-gray-100 text-gray-800 rounded">
                                            #{{ $layout->id }}
                                        </span>
                                    </td>

                                    {{-- Código de Ubicación --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('product_layouts.show', $layout) }}" 
                                           class="font-mono text-sm font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">
                                            {{ $layout->full_location_code }}
                                        </a>
                                    </td>

                                    {{-- Bodega --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-warehouse text-gray-400 mr-2"></i>
                                            <div>
                                                <div class="font-semibold text-gray-900">
                                                    {{ $layout->storageLocation->name ?? 'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $layout->storageLocation->code ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Coordenadas --}}
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-700">
                                            <div class="flex items-center gap-2 mb-1">
                                                <i class="fas fa-layer-group text-indigo-500 text-xs"></i>
                                                <span class="font-medium">Estante:</span>
                                                <span class="font-bold">{{ $layout->shelf }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <i class="fas fa-sort text-indigo-500 text-xs"></i>
                                                <span class="font-medium">Nivel:</span>
                                                <span class="font-bold">{{ $layout->level }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-map-marker-alt text-indigo-500 text-xs"></i>
                                                <span class="font-medium">Posición:</span>
                                                <span class="font-bold">{{ number_format($layout->position, 2) }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Producto Asignado --}}
                                    <td class="px-6 py-4">
                                        @if(!is_null($layout->product_id) && $layout->product)
                                            <div class="flex items-center">
                                                <i class="fas fa-box text-green-500 mr-2"></i>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        {{ $layout->product->name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        SKU: {{ $layout->product->sku ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Sin asignar
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Estado --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(!is_null($layout->product_id))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Ocupada
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-circle mr-1"></i>
                                                Disponible
                                            </span>
                                        @endif
                                    </td>
                                    
                                    {{-- Acciones --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex justify-end items-center space-x-2">
                                            {{-- Botón Ver Detalles --}}
                                            <a href="{{ route('product_layouts.show', $layout) }}" 
                                                class="inline-flex items-center px-3 py-2 bg-indigo-50 border border-indigo-200 rounded-md hover:bg-indigo-100 text-indigo-700 shadow-sm transition-colors"
                                                title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            {{-- Botón Editar --}}
                                            <a href="{{ route('product_layouts.edit', $layout) }}" 
                                                class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 shadow-sm transition-colors"
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            {{-- Botón Asignar/Cambiar Producto --}}
                                            @if(is_null($layout->product_id))
                                                <a href="{{ route('product_layouts.show', $layout) }}" 
                                                    class="inline-flex items-center px-3 py-2 bg-green-50 border border-green-300 rounded-md hover:bg-green-100 text-green-700 shadow-sm transition-colors"
                                                    title="Asignar producto">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('product_layouts.show', $layout) }}" 
                                                    class="inline-flex items-center px-3 py-2 bg-amber-50 border border-amber-300 rounded-md hover:bg-amber-100 text-amber-700 shadow-sm transition-colors"
                                                    title="Cambiar producto">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </a>
                                            @endif

                                            {{-- Botón Eliminar --}}
                                            <button 
                                                @click="layoutToDelete = { 
                                                    id: {{ $layout->id }}, 
                                                    name: '{{ $layout->full_location_code }}',
                                                    hasProduct: {{ is_null($layout->product_id) ? 'false' : 'true' }}
                                                }; 
                                                showDeleteModal = true"
                                                class="inline-flex items-center px-3 py-2 bg-red-50 border border-red-300 rounded-md text-red-700 hover:bg-red-100 shadow-sm transition-colors"
                                                title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <i class="fas fa-warehouse text-gray-300 text-6xl mb-4"></i>
                                            <p class="text-lg font-medium">No hay ubicaciones registradas</p>
                                            <p class="text-sm mt-2">Comienza creando tu primera ubicación física</p>
                                            <a href="{{ route('product_layouts.create') }}" 
                                               class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                                                <i class="fas fa-plus mr-2"></i>
                                                {{ __('Nueva Ubicación') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($productLayouts->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Mostrando 
                                <span class="font-medium">{{ $productLayouts->firstItem() }}</span>
                                al 
                                <span class="font-medium">{{ $productLayouts->lastItem() }}</span>
                                de 
                                <span class="font-medium">{{ $productLayouts->total() }}</span>
                                ubicaciones
                            </div>
                            <div>
                                {{ $productLayouts->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
        <div x-show="showDeleteModal" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4">
            <div @click.away="showDeleteModal = false" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">
                    {{ __('Confirmar Eliminación') }}
                </h3>
                
                <p class="text-sm text-gray-600 mb-2 text-center">
                    Está a punto de eliminar la ubicación:
                </p>
                <p class="text-center mb-4">
                    <strong class="text-lg text-gray-900" x-text="layoutToDelete?.name"></strong>
                </p>

                {{-- Advertencia si tiene producto --}}
                <div x-show="layoutToDelete?.hasProduct" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5"></i>
                        <p class="text-sm text-amber-800">
                            <strong>Advertencia:</strong> Esta ubicación tiene un producto asignado. Al eliminarla, el producto quedará sin ubicación física.
                        </p>
                    </div>
                </div>

                <p class="text-sm text-gray-600 mb-6 text-center">
                    Esta acción es <strong class="text-red-600">permanente</strong> y no se puede deshacer.
                </p>

                <div class="flex justify-center gap-3">
                    <button @click="showDeleteModal = false" 
                            class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium shadow-sm transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <form x-bind:action="`{{ url('product_layouts') }}/${layoutToDelete?.id}`" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md transition-colors">
                            <i class="fas fa-trash-alt mr-2"></i>
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush
</x-app-layout>