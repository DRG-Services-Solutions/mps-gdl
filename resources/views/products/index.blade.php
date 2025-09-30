<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ __('Catálogo de Productos Quirúrgicos') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Administra equipos y consumibles utilizados en procedimientos MPS') }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showDeleteModal: false, productToDelete: null }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                
                <!-- Header Section -->
                <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-cyan-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-boxes text-teal-600 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Lista de Artículos') }}</h3>
                                <p class="text-sm text-gray-600">{{ $products->total() }} {{ __('artículos registrados') }}</p>
                            </div>
                        </div>
                        
                        <a href="{{ route('products.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                            <i class="fas fa-plus mr-2"></i>
                            {{ __('Crear Producto') }}
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
                                    {{ __('Fabricante') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden md:table-cell">
                                    {{ __('Categoría') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden xl:table-cell">
                                    {{ __('Subcategoría') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Especialidad') }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Stock') }}
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
                                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center">
                                                    <i class="fas fa-box text-white text-sm"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                                <div class="text-xs text-gray-500 flex items-center space-x-2">
                                                    <span>Ref: {{ $product->code ?? 'N/A' }}</span>
                                                    @if($product->rfid_enabled)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                            <i class="fas fa-wifi mr-1 text-xs"></i>
                                                            RFID
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Fabricante -->
                                    <td class="px-6 py-4 hidden lg:table-cell">
                                        <div class="text-sm text-gray-900">{{ $product->manufacturer->name ?? 'Sin fabricante' }}</div>
                                    </td>

                                    <!-- Categoría -->
                                    <td class="px-6 py-4 hidden md:table-cell">
                                        @if($product->category)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-tag mr-1 text-xs"></i>
                                                {{ $product->category->name }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">Sin categoría</span>
                                        @endif
                                    </td>

                                    <!-- Subcategoría -->
                                    <td class="px-6 py-4 hidden xl:table-cell">
                                        @if($product->subcategory)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                <i class="fas fa-layer-group mr-1 text-xs"></i>
                                                {{ $product->subcategory->name }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">N/A</span>
                                        @endif
                                    </td>

                                    <!-- Especialidad -->
                                    <td class="px-6 py-4">
                                        @if($product->medicalSpecialty)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                                                <i class="fas fa-stethoscope mr-1 text-xs"></i>
                                                {{ $product->medicalSpecialty->name }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                General
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Stock -->
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-2xl font-bold {{ $product->current_stock <= $product->minimum_stock ? 'text-red-600' : 'text-gray-900' }}">
                                                    {{ $product->current_stock }}
                                                </span>
                                                @if($product->current_stock <= $product->minimum_stock)
                                                    <i class="fas fa-exclamation-triangle text-red-500" title="Stock bajo"></i>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Mín: {{ $product->minimum_stock }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('products.edit', $product->id) }}" 
                                               class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                                               title="Editar producto">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <button @click="productToDelete = { id: {{ $product->id }}, name: '{{ $product->name }}' }; showDeleteModal = true"
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
                                            <i class="fas fa-box-open text-gray-400 text-5xl mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No hay productos registrados') }}</h3>
                                            <p class="text-sm text-gray-500 mb-4">{{ __('Comienza agregando tu primer producto al catálogo') }}</p>
                                            <a href="{{ route('products.create') }}" 
                                               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                                                <i class="fas fa-plus mr-2"></i>
                                                {{ __('Crear Primer Producto') }}
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
                                    {{ __('¿Estás seguro de que quieres eliminar el producto') }} 
                                    <span class="font-semibold" x-text="productToDelete?.name"></span>?
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