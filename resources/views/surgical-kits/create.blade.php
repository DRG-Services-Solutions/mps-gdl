<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Crear Prearmado Quirúrgico
            </h2>
            <a href="{{ route('surgical-kits.index') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            {{-- Mensajes de error --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div x-data="surgicalKitForm({{ $products->toJson() }})" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('surgical-kits.store') }}">
                    @csrf

                    <div class="p-6 space-y-6">
                        
                        {{-- Información Básica --}}
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                            
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                {{-- Nombre --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">
                                        Nombre del Prearmado <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name') }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Tipo de Cirugía --}}
                                <div>
                                    <label for="surgery_type" class="block text-sm font-medium text-gray-700">
                                        Tipo de Cirugía <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="surgery_type" 
                                           id="surgery_type" 
                                           value="{{ old('surgery_type') }}"
                                           required
                                           placeholder="Ej: Ortopédica, Cardiovascular, etc."
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    @error('surgery_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Descripción --}}
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700">
                                        Descripción
                                    </label>
                                    <textarea name="description" 
                                              id="description" 
                                              rows="3"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Estado --}}
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Prearmado activo</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Productos del Prearmado --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">
                                        Productos <span class="text-red-500">*</span>
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">Agrega los productos necesarios para este prearmado</p>
                                </div>
                            </div>

                            {{-- Buscador y selector de productos --}}
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <label for="product_search" class="block text-sm font-medium text-gray-700 mb-2">
                                    Buscar y agregar producto
                                </label>
                                <div class="flex gap-3">
                                    <div class="flex-1">
                                        <input type="text" 
                                               x-model="searchTerm"
                                               @input="filterProducts()"
                                               placeholder="Buscar por código o nombre..."
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                {{-- Lista de productos filtrados --}}
                                <div x-show="searchTerm.length > 0" 
                                     class="mt-3 max-h-60 overflow-y-auto bg-white rounded-md border border-gray-200">
                                    <template x-for="product in filteredProducts" :key="product.id">
                                        <div @click="addProductFromList(product)"
                                             class="flex items-center justify-between p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors duration-150">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <p class="text-sm font-medium text-gray-900" x-text="product.code"></p>
                                                    <span x-show="product.available_stock === 0" 
                                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        Sin Stock
                                                    </span>
                                                    <span x-show="product.available_stock > 0 && product.available_stock <= 5" 
                                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Stock Bajo
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600" x-text="product.name"></p>
                                            </div>
                                            <div class="ml-4 text-right">
                                                <p class="text-sm font-semibold" 
                                                   :class="product.available_stock === 0 ? 'text-red-600' : product.available_stock <= 5 ? 'text-yellow-600' : 'text-green-600'">
                                                    <span x-text="product.available_stock"></span> disponibles
                                                </p>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <div x-show="filteredProducts.length === 0" 
                                         class="p-4 text-center text-sm text-gray-500">
                                        No se encontraron productos
                                    </div>
                                </div>
                            </div>

                            {{-- Tabla de productos seleccionados --}}
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Producto</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">Stock Disponible</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">Cantidad</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Notas</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="(item, index) in selectedProducts" :key="index">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    <input type="hidden" :name="'products[' + index + '][product_id]'" :value="item.product_id">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900" x-text="item.code"></p>
                                                        <p class="text-sm text-gray-600" x-text="item.name"></p>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                                          :class="item.available_stock === 0 ? 'bg-red-100 text-red-800' : 
                                                                  item.available_stock <= 5 ? 'bg-yellow-100 text-yellow-800' : 
                                                                  'bg-green-100 text-green-800'">
                                                        <span x-text="item.available_stock"></span>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <input type="number" 
                                                           :name="'products[' + index + '][quantity]'" 
                                                           x-model="item.quantity"
                                                           min="1"
                                                           required
                                                           class="block w-24 mx-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-center">
                                                </td>
                                                <td class="px-6 py-4">
                                                    <input type="text" 
                                                           :name="'products[' + index + '][notes]'" 
                                                           x-model="item.notes"
                                                           placeholder="Opcional"
                                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <button type="button" 
                                                            @click="removeProduct(index)"
                                                            class="text-red-600 hover:text-red-900 transition-colors duration-150">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>

                                        {{-- Mensaje cuando no hay productos --}}
                                        <template x-if="selectedProducts.length === 0">
                                            <tr>
                                                <td colspan="5" class="px-6 py-12 text-center">
                                                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                                    </svg>
                                                    <p class="mt-2 text-sm text-gray-500">
                                                        No hay productos agregados. Busca y selecciona productos arriba.
                                                    </p>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Resumen --}}
                            <div x-show="selectedProducts.length > 0" 
                                 class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <p class="text-sm font-medium text-gray-600">Total de productos</p>
                                        <p class="text-2xl font-bold text-blue-900" x-text="selectedProducts.length"></p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-sm font-medium text-gray-600">Total de piezas</p>
                                        <p class="text-2xl font-bold text-blue-900" x-text="totalPieces"></p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-sm font-medium text-gray-600">Productos sin stock</p>
                                        <p class="text-2xl font-bold" 
                                           :class="productsWithoutStock > 0 ? 'text-red-600' : 'text-green-600'"
                                           x-text="productsWithoutStock"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Botones de Acción --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('surgical-kits.index') }}" 
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Crear Prearmado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function surgicalKitForm(availableProducts) {
            return {
                availableProducts: availableProducts,
                selectedProducts: [],
                filteredProducts: [],
                searchTerm: '',
                
                filterProducts() {
                    if (this.searchTerm.length === 0) {
                        this.filteredProducts = [];
                        return;
                    }
                    
                    const term = this.searchTerm.toLowerCase();
                    this.filteredProducts = this.availableProducts.filter(product => {
                        // Excluir productos ya seleccionados
                        const isAlreadySelected = this.selectedProducts.some(p => p.product_id === product.id);
                        if (isAlreadySelected) return false;
                        
                        return product.code.toLowerCase().includes(term) || 
                               product.name.toLowerCase().includes(term);
                    });
                },
                
                addProductFromList(product) {
                    // Verificar si ya está agregado
                    const exists = this.selectedProducts.some(p => p.product_id === product.id);
                    if (exists) {
                        alert('Este producto ya ha sido agregado al prearmado.');
                        return;
                    }
                    
                    this.selectedProducts.push({
                        product_id: product.id,
                        code: product.code,
                        name: product.name,
                        available_stock: product.available_stock,
                        quantity: 1,
                        notes: ''
                    });
                    
                    // Limpiar búsqueda
                    this.searchTerm = '';
                    this.filterProducts();
                },
                
                removeProduct(index) {
                    this.selectedProducts.splice(index, 1);
                },
                
                get totalPieces() {
                    return this.selectedProducts.reduce((sum, product) => {
                        return sum + parseInt(product.quantity || 0);
                    }, 0);
                },
                
                get productsWithoutStock() {
                    return this.selectedProducts.filter(p => p.available_stock === 0).length;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>