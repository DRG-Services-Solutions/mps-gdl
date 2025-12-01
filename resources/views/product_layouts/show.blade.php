<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Detalles del Layout') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Ubicación <strong>{{ $productLayout->full_location_code }}</strong>
                </p>
            </div>
            
            <div class="flex gap-2">
                {{-- Botón de Asignar/Cambiar Producto --}}
                @if(is_null($productLayout->product_id))
                    <button @click="$dispatch('open-modal', 'assign-product-modal')"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                        <i class="fas fa-box mr-2"></i>
                        {{ __('Asignar Producto') }}
                    </button>
                @else
                    <button @click="$dispatch('open-modal', 'assign-product-modal')"
                        class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                        <i class="fas fa-exchange-alt mr-2"></i>
                        {{ __('Cambiar Producto') }}
                    </button>
                @endif

                {{-- Botón de Edición --}}
                <a href="{{ route('product_layouts.edit', $productLayout) }}" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    {{ __('Editar Layout') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Mensajes de éxito/error --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg" x-data="{ show: true }" x-show="show" x-transition>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-green-600"></i>
                            <p class="text-green-800 font-medium">{{ session('success') }}</p>
                        </div>
                        <button @click="show = false" class="text-green-600 hover:text-green-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                        <div>
                            <p class="text-red-800 font-medium">Errores encontrados:</p>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden divide-y divide-gray-200">
                
                {{-- SECCIÓN DE ESTADO --}}
                <div class="p-6 {{ !is_null($productLayout->product_id) ? 'bg-green-50' : 'bg-amber-50' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @if(!is_null($productLayout->product_id))
                                <div class="p-3 bg-green-100 rounded-full">
                                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-green-900">{{ __('Ubicación Ocupada') }}</h3>
                                    <p class="text-sm text-green-700">Esta ubicación tiene un producto asignado</p>
                                </div>
                            @else
                                <div class="p-3 bg-amber-100 rounded-full">
                                    <i class="fas fa-exclamation-triangle text-2xl text-amber-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-amber-900">{{ __('Ubicación Vacía') }}</h3>
                                    <p class="text-sm text-amber-700">Esta ubicación no tiene producto asignado</p>
                                </div>
                            @endif
                        </div>

                        @if(!is_null($productLayout->product_id))
                            <form action="{{ route('product_layouts.remove-product', $productLayout) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('¿Estás seguro de que deseas liberar esta ubicación?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                                    <i class="fas fa-times mr-2"></i>
                                    {{ __('Liberar Ubicación') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- SECCIÓN DE PRODUCTO ASIGNADO --}}
                @if(!is_null($productLayout->product_id) && $productLayout->product)
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-box text-indigo-600"></i>
                            {{ __('Producto Asignado') }}
                        </h3>
                        
                        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-6 border border-indigo-200">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="px-3 py-1 bg-indigo-600 text-white text-xs font-bold rounded-full">
                                            #{{ $productLayout->product->id }}
                                        </span>
                                        <h4 class="text-2xl font-bold text-gray-900">
                                            {{ $productLayout->product->name }}
                                        </h4>
                                    </div>
                                    
                                    <dl class="grid grid-cols-2 gap-4 mt-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-600">{{ __('SKU') }}</dt>
                                            <dd class="mt-1 text-base font-semibold text-gray-900">{{ $productLayout->product->sku ?? 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-600">{{ __('Categoría') }}</dt>
                                            <dd class="mt-1 text-base font-semibold text-gray-900">
                                                {{ $productLayout->product->subcategory->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        @if($productLayout->product->description)
                                            <div class="col-span-2">
                                                <dt class="text-sm font-medium text-gray-600">{{ __('Descripción') }}</dt>
                                                <dd class="mt-1 text-sm text-gray-700">{{ $productLayout->product->description }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>
                                
                                <a href="{{ route('products.show', $productLayout->product) }}" 
                                   class="ml-4 inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-indigo-600 font-medium rounded-lg border border-indigo-300 shadow-sm transition-all duration-200">
                                    <i class="fas fa-external-link-alt mr-2"></i>
                                    {{ __('Ver Producto') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- SECCIÓN DE UBICACIÓN FÍSICA --}}
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('Ubicación Geográfica') }}</h3>
                    
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        
                        {{-- Bodega Principal --}}
                        <div class="border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Bodega Principal') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">
                                {{ $productLayout->storageLocation->name ?? 'N/A' }}
                            </dd>
                            <dd class="text-sm text-gray-600">Código: {{ $productLayout->storageLocation->code ?? 'N/A' }}</dd>
                        </div>

                        {{-- Código de Ubicación Completo --}}
                        <div class="border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Código de Ubicación') }}</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-indigo-100 text-indigo-800">
                                    {{ $productLayout->full_location_code }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- SECCIÓN DE COORDENADAS EXACTAS --}}
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('Coordenadas del Layout') }}</h3>
                    
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-4">
                        
                        {{-- Estante (Shelf) --}}
                        <div class="border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">
                                <i class="fas fa-warehouse mr-1 text-indigo-500"></i> {{ __('Estante') }}
                            </dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $productLayout->shelf }}</dd>
                        </div>
                        
                        {{-- Nivel (Level) --}}
                        <div class="border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">
                                <i class="fas fa-layer-group mr-1 text-indigo-500"></i> {{ __('Nivel') }}
                            </dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $productLayout->level }}</dd>
                        </div>
                        
                        {{-- Posición (Position) --}}
                        <div class="border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">
                                <i class="fas fa-map-marker-alt mr-1 text-indigo-500"></i> {{ __('Posición') }}
                            </dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($productLayout->position, 2) }}</dd>
                        </div>
                    </dl>
                    
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-700">
                            <i class="fas fa-info-circle text-indigo-500 mr-2"></i>
                            {{ $productLayout->location_description }}
                        </p>
                    </div>
                </div>

                {{-- SECCIÓN DE METADATOS --}}
                <div class="p-6 bg-gray-50">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('Metadatos') }}</h3>
                    
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Creado el') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $productLayout->created_at->format('d/m/Y H:i') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Última Actualización') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $productLayout->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
                
                {{-- Botón Volver --}}
                <div class="p-6 pt-4 flex justify-end">
                    <a href="{{ route('product_layouts.index') }}" 
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 shadow-sm transition-colors">
                        {{ __('Volver al Listado') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE ASIGNACIÓN DE PRODUCTO --}}
    <x-modal name="assign-product-modal" :show="false" maxWidth="3xl">
        <div class="p-6" x-data="productAssignmentModal()">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ !is_null($productLayout->product_id) ? __('Cambiar Producto') : __('Asignar Producto') }}
                </h2>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('product_layouts.assign-product', $productLayout) }}" 
                  method="POST"
                  @submit="return validateForm()">
                @csrf
                
                {{-- Advertencia si ya tiene producto --}}
                @if(!is_null($productLayout->product_id) && $productLayout->product)
                    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-triangle text-amber-600 text-xl mt-0.5"></i>
                            <div>
                                <h4 class="font-semibold text-amber-900">{{ __('Producto actual será reemplazado') }}</h4>
                                <p class="text-sm text-amber-700 mt-1">
                                    El producto actual <strong>{{ $productLayout->product->name }}</strong> será removido de esta ubicación.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Búsqueda de Producto --}}
                <div class="mb-6">
                    <label for="product_search" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Buscar Producto') }}
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="product_search" 
                               x-model="searchQuery"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Buscar por nombre, SKU o código..."
                               autocomplete="off"
                               @input.debounce.300ms="searchProducts()">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                {{-- Resultados de Búsqueda --}}
                <div class="mb-6 max-h-96 overflow-y-auto">
                    {{-- Estado inicial --}}
                    <div x-show="!isSearching && !hasSearched" class="text-center py-8 text-gray-500">
                        <i class="fas fa-search text-4xl mb-3"></i>
                        <p>{{ __('Comienza a escribir para buscar productos') }}</p>
                    </div>

                    {{-- Buscando --}}
                    <div x-show="isSearching" class="text-center py-8 text-gray-500">
                        <i class="fas fa-spinner fa-spin text-4xl mb-3"></i>
                        <p>Buscando productos...</p>
                    </div>

                    {{-- Sin resultados --}}
                    <div x-show="!isSearching && hasSearched && products.length === 0" class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3"></i>
                        <p class="text-lg font-medium">No se encontraron productos</p>
                        <p class="text-sm mt-2">Intenta con otro término de búsqueda</p>
                    </div>

                    {{-- Resultados --}}
                    <div x-show="!isSearching && products.length > 0" class="space-y-2">
                        <template x-for="product in products" :key="product.id">
                            <div class="p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 cursor-pointer transition-all"
                                 @click="selectProduct(product)"
                                 :class="{ 'border-indigo-500 bg-indigo-50': selectedProduct === product.id }">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 text-xs font-bold rounded"
                                                  x-text="'#' + product.id"></span>
                                            <h4 class="font-semibold text-gray-900" x-text="product.name"></h4>
                                        </div>
                                        <p class="text-sm text-gray-600">SKU: <span x-text="product.sku || 'N/A'"></span></p>
                                        <span x-show="product.has_layout" 
                                              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Ya tiene ubicación asignada
                                        </span>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Error --}}
                    <div x-show="hasError" class="text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                        <p class="font-semibold">Error al buscar productos</p>
                        <p class="text-sm text-gray-600 mt-2" x-text="errorMessage"></p>
                    </div>
                </div>

                {{-- Input Hidden para Product ID --}}
                <input type="hidden" name="product_id" x-model="selectedProduct" required>

                {{-- Producto Seleccionado --}}
                <div x-show="selectedProduct" 
                     x-transition
                     class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            <div>
                                <p class="text-sm text-green-700">Producto seleccionado:</p>
                                <p class="font-semibold text-green-900" x-text="selectedProductName"></p>
                            </div>
                        </div>
                        <button type="button" 
                                @click="clearSelection()"
                                class="text-red-600 hover:text-red-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-3">
                    <button type="button" 
                            @click="closeModal()"
                            class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">
                        {{ __('Cancelar') }}
                    </button>
                    <button type="submit" 
                            :disabled="!selectedProduct"
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium transition-colors"
                            :class="selectedProduct ? 'hover:bg-indigo-700' : 'opacity-50 cursor-not-allowed'">
                        <i class="fas fa-save mr-2"></i>
                        {{ __('Asignar Producto') }}
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    @push('scripts')
    <script>
        function productAssignmentModal() {
            return {
                searchQuery: '',
                products: [],
                selectedProduct: null,
                selectedProductName: '',
                isSearching: false,
                hasSearched: false,
                hasError: false,
                errorMessage: '',

                searchProducts() {
                    const query = this.searchQuery.trim();
                    
                    if (!query || query.length < 2) {
                        this.products = [];
                        this.hasSearched = false;
                        this.hasError = false;
                        return;
                    }

                    this.isSearching = true;
                    this.hasError = false;

                    fetch(`{{ route('product_layouts.search-products') }}?q=${encodeURIComponent(query)}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Search results:', data);
                        this.products = data.products || [];
                        this.hasSearched = true;
                        this.isSearching = false;
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        this.hasError = true;
                        this.errorMessage = error.message;
                        this.products = [];
                        this.hasSearched = true;
                        this.isSearching = false;
                    });
                },

                selectProduct(product) {
                    console.log('Product selected:', product);
                    this.selectedProduct = product.id;
                    this.selectedProductName = product.name;
                    this.searchQuery = product.name;
                    this.products = [];
                    this.hasSearched = false;
                },

                clearSelection() {
                    this.selectedProduct = null;
                    this.selectedProductName = '';
                    this.searchQuery = '';
                    this.products = [];
                    this.hasSearched = false;
                },

                closeModal() {
                    this.clearSelection();
                    this.$dispatch('close');
                },

                validateForm() {
                    if (!this.selectedProduct) {
                        alert('Por favor selecciona un producto');
                        return false;
                    }
                    return true;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>