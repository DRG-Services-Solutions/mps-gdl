<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ __('Catálogo de Productos') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Fichas maestras de productos quirúrgicos y consumibles') }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showDeleteModal: false, productToDelete: null }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                
                <!-- Header Section -->
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-book text-indigo-600 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Catálogo Maestro') }}</h3>
                                <p class="text-sm text-gray-600">{{ $products->total() }} {{ __('productos registrados') }}</p>
                            </div>
                        </div>
                        
                        <a href="{{ route('products.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                            <i class="fas fa-plus mr-2"></i>
                            {{ __('Agregar al Catálogo') }}
                        </a>
                    </div>
                </div>

                <!-- Success Message -->
                @if(session('success'))
                    <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-200" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-emerald-600 mr-2"></i>
                                <span class="text-emerald-800 font-medium">{{ session('success') }}</span>
                            </div>
                            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 transition-colors duration-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Table Section -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                  {{ __('Producto') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell">
                                    {{ __('Proveedor/Fabricante') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden md:table-cell">
                                    {{ __('Categoría') }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Tipo de Tracking') }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider relative group">
                                    <div class="flex items-center justify-center">
                                        {{ __('Esterilización') }}
                                    </div>
                                    {{-- TOOLTIP con el mensaje solicitado --}}
                                    <div class="absolute z-10 hidden group-hover:block px-3 py-2 text-xs font-normal text-white bg-indigo-600 rounded-lg whitespace-nowrap top-full mt-1 transform -translate-x-1/2 left-1/2 shadow-lg">
                                        {{ __('Solo los productos de tipo instrumental requieren esterilización.') }}
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider relative group">
                                    <div class="flex items-center justify-center">
                                        {{ __('Refrigeración') }}
                                    </div>
                                    {{-- TOOLTIP con el mensaje solicitado --}}
                                    <div class="absolute z-10 hidden group-hover:block px-3 py-2 text-xs font-normal text-white bg-indigo-600 rounded-lg whitespace-nowrap top-full mt-1 transform -translate-x-1/2 left-1/2 shadow-lg">
                                        {{ __('Solo los productos clasificados requieren Refrigeración.') }}
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider hidden xl:table-cell">
                                    {{ __('Estado') }}
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($products as $product)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <!-- Producto -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                                    <i class="fas fa-box text-white text-sm"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="{{ route('products.show', $product) }}" 
                                                class="text-sm font-semibold text-gray-900 hover:text-indigo-600 transition-colors duration-150">
                                                    {{ $product->name }}
                                                </a>
                                                <div class="text-xs text-gray-500 flex items-center space-x-2 mt-1">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-barcode mr-1"></i>
                                                        {{ $product->code }}
                                                    </span>
                                                    @if($product->model)
                                                        <span class="text-gray-400">|</span>
                                                        <span class="flex items-center">
                                                            <i class="fas fa-tag mr-1"></i>
                                                            {{ $product->model }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                    
                                    <!-- Fabricante -->
                                    <td class="px-6 py-4 hidden lg:table-cell">
                                        <div class="text-sm text-gray-900 text-center">{{ $product->supplier->name ?? 'Sin Proveedor' }}</div>
                                    </td>

                                    <!-- Categoría -->
                                    <td class="px-6 py-4 hidden md:table-cell">
                                        @if($product->category)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-tag mr-1 text-xs"></i>
                                                {{ $product->category->name }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Tipo de Tracking -->
                                    <td class="px-6 py-4 text-center">
                                        @switch($product->tracking_type)
                                            @case('code')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-boxes mr-1"></i>
                                                    Code
                                                </span>
                                                @break
                                            @case('rfid')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-wifi mr-1"></i>
                                                    RFID
                                                </span>
                                                @break
                                            @case('serial')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-hashtag mr-1"></i>
                                                    Serial
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($product->requires_sterilization)
                                            {{-- Icono de Check (Sí requiere) --}}
                                            <i class="fas fa-check-circle text-green-500 text-lg" title="Requiere Esterilización"></i>
                                        @else
                                            {{-- Icono de Cruz o guion (No requiere) --}}
                                            <i class="fas fa-times-circle text-red-400 text-lg" title="No Requiere Esterilización"></i>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($product->requires_refrigeration)
                                            {{-- Icono de Check (Sí requiere) --}}
                                            <i class="fas fa-check-circle text-green-500 text-lg" title="Requiere Refrigeration"></i>
                                        @else
                                            {{-- Icono de Cruz o guion (No requiere) --}}
                                            <i class="fas fa-times-circle text-red-400 text-lg" title="No Requiere Refrigeration"></i>
                                        @endif
                                    </td>
                                    <!-- Estado -->
                                    <td class="px-6 py-4 text-center hidden xl:table-cell">
                                        @switch($product->status)
                                            @case('active')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Activo
                                                </span>
                                                @break
                                            @case('inactive')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-pause-circle mr-1"></i>
                                                    Inactivo
                                                </span>
                                                @break
                                            @case('discontinued')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-ban mr-1"></i>
                                                    Descontinuado
                                                </span>
                                                @break
                                        @endswitch
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('products.edit', $product->id) }}" 
                                               class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                                               title="Editar producto">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <button @click="productToDelete = { id: {{ $product->id }}, name: '{{ addslashes($product->name) }}' }; showDeleteModal = true"
                                                    class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
                                                    title="Eliminar producto">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-book-open text-gray-400 text-5xl mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Catálogo Vacío') }}</h3>
                                            <p class="text-sm text-gray-500 mb-4">{{ __('Comienza agregando productos al catálogo maestro') }}</p>
                                            <a href="{{ route('products.create') }}" 
                                               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                                                <i class="fas fa-plus mr-2"></i>
                                                {{ __('Agregar Primer Producto') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                {{ __('Mostrando') }} {{ $products->firstItem() }} {{ __('al') }} {{ $products->lastItem() }} {{ __('de') }} {{ $products->total() }} {{ __('productos') }}
                            </div>
                            <div>
                                {{ $products->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Info Cards -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-box text-indigo-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total en Catálogo</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $products->total() }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-wifi text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Con Tracking Code</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $products->where('tracking_type', 'code')->count() }}
                            </p>
                        </div>
                    </div>
                </div>


                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-wifi text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Con Tracking RFID</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $products->where('tracking_type', 'rfid')->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hashtag text-yellow-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Con Número de Serie</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $products->where('tracking_type', 'serial')->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
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
                                {{ __('Confirmar Eliminación') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('¿Estás seguro de eliminar') }} 
                                    <span class="font-semibold" x-text="productToDelete?.name"></span>{{ __('?') }}
                                    {{ __('Esta acción no se puede deshacer.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <form x-bind:action="`{{ url('products') }}/${productToDelete?.id}`" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <i class="fas fa-trash-alt mr-2"></i>
                                {{ __('Eliminar') }}
                            </button>
                        </form>
                        <button @click="showDeleteModal = false" 
                                type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                                {{ __('Cancelar') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>