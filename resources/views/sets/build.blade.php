<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('sets.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">Constructor de Receta</h2>
                    <p class="text-sm text-purple-600 font-semibold">{{ $product->code }} - {{ $product->name }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8" 
         x-data="recipeBuilder(@js($existingComponents), '{{ route('sets.save', $product) }}')">
         
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- PANEL IZQUIERDO: BUSCADOR DE PRODUCTOS --}}
            <div class="lg:col-span-1 bg-white shadow-sm rounded-xl border border-gray-200 p-6 h-fit sticky top-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">1. Agregar Componentes</h3>
                
                <div class="relative" style="overflow: visible;">
                    <div class="relative">
                        <input type="text" 
                            x-model="searchQuery"
                            @input.debounce.300ms="searchProducts"
                            @focus="if(searchQuery.length >= 2) showDropdown = true"
                            @click.away="showDropdown = false"
                            placeholder="Buscar código o nombre..."
                            class="block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-purple-500 pr-10 shadow-sm"
                            autocomplete="off">
                        
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400" x-show="!isSearching"></i>
                            <i class="fas fa-circle-notch fa-spin text-purple-500" x-show="isSearching" x-cloak></i>
                        </div>
                    </div>

                    {{-- Dropdown de Resultados --}}
                    <div x-show="showDropdown" x-cloak x-transition
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                        
                        <template x-for="result in searchResults" :key="result.id">
                            <div @click="addItem(result)"
                                class="px-4 py-3 hover:bg-purple-50 cursor-pointer border-b border-gray-100 transition-colors">
                                <div class="font-medium text-gray-900 text-sm" x-text="result.code"></div>
                                <div class="text-xs text-gray-600 truncate" x-text="result.name"></div>
                            </div>
                        </template>

                        <div x-show="searchResults.length === 0 && !isSearching && searchQuery.length >= 2"
                             class="px-4 py-4 text-center text-gray-500 text-sm">
                            No se encontraron productos.
                        </div>
                    </div>
                </div>

                <div class="mt-6 bg-purple-50 rounded-lg p-4 text-sm text-purple-800 border border-purple-100">
                    <i class="fas fa-lightbulb text-purple-600 mr-2"></i>
                    <strong>Tip:</strong> Busca y haz clic en los productos para agregarlos a la receta a la derecha. Si haces clic varias veces, la cantidad aumentará.
                </div>
            </div>

            {{-- PANEL DERECHO: LA RECETA --}}
            <div class="lg:col-span-2 bg-white shadow-sm rounded-xl border border-gray-200 flex flex-col h-[700px]">
                <div class="p-6 border-b border-gray-200 bg-gray-50 rounded-t-xl flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">2. Lista de Materiales (BOM)</h3>
                    <span class="bg-purple-100 text-purple-800 text-xs font-bold px-3 py-1 rounded-full" x-text="items.length + ' ítems distintos'"></span>
                </div>

                {{-- Lista scrolleable de items --}}
                <div class="flex-1 overflow-y-auto p-6 bg-white">
                    <template x-if="items.length === 0">
                        <div class="h-full flex flex-col items-center justify-center text-gray-400 space-y-4">
                            <i class="fas fa-box-open text-6xl text-gray-200"></i>
                            <p>La receta está vacía. Agrega componentes desde el buscador.</p>
                        </div>
                    </template>

                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="item.product_id">
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-purple-300 transition-colors bg-white shadow-sm">
                                
                                <div class="flex-1 min-w-0 pr-4">
                                    <h4 class="text-sm font-bold text-gray-900 truncate" x-text="item.code"></h4>
                                    <p class="text-xs text-gray-500 truncate" x-text="item.name"></p>
                                </div>

                                <div class="flex items-center space-x-6">
                                    {{-- Obligatorio Toggle --}}
                                    <label class="flex items-center cursor-pointer" title="¿Es indispensable para el Set?">
                                        <div class="relative">
                                            <input type="checkbox" class="sr-only" x-model="item.is_mandatory">
                                            <div class="block bg-gray-200 w-10 h-6 rounded-full transition-colors" :class="{'bg-purple-500': item.is_mandatory}"></div>
                                            <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform" :class="{'transform translate-x-4': item.is_mandatory}"></div>
                                        </div>
                                        <span class="ml-2 text-xs font-medium text-gray-600" x-text="item.is_mandatory ? 'Obligatorio' : 'Opcional'"></span>
                                    </label>

                                    {{-- Cantidad Input --}}
                                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden w-28">
                                        <button type="button" @click="if(item.quantity > 1) item.quantity--" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold transition-colors">-</button>
                                        <input type="number" x-model.number="item.quantity" min="1" class="w-full text-center border-0 p-1 text-sm focus:ring-0 appearance-none m-0">
                                        <button type="button" @click="item.quantity++" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold transition-colors">+</button>
                                    </div>

                                    {{-- Eliminar --}}
                                    <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 transition-colors p-2">
                                        <i class="fas fa-trash-alt text-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Footer Guardar --}}
                <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-xl flex justify-end">
                    <button type="button" 
                            @click="saveRecipe" 
                            :disabled="isSaving || items.length === 0"
                            class="bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white font-bold py-3 px-8 rounded-lg shadow-md transition-all flex items-center">
                        <i class="fas fa-save mr-2" x-show="!isSaving"></i>
                        <i class="fas fa-circle-notch fa-spin mr-2" x-show="isSaving" x-cloak></i>
                        <span x-text="isSaving ? 'Guardando...' : 'Guardar Receta Maestro'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('recipeBuilder', (initialItems, saveUrl) => ({
                items: initialItems || [],
                searchQuery: '',
                searchResults: [],
                showDropdown: false,
                isSearching: false,
                isSaving: false,
                saveEndpoint: saveUrl,

                async searchProducts() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        this.showDropdown = false;
                        return;
                    }
                    this.isSearching = true;
                    this.showDropdown = true;
                    
                    try {
                        const response = await fetch(`{{ route('products.search') }}?q=${encodeURIComponent(this.searchQuery)}`);
                        if (!response.ok) throw new Error('Error de red');
                        this.searchResults = await response.json();
                    } catch (error) {
                        console.error(error);
                        this.searchResults = [];
                    } finally {
                        this.isSearching = false;
                    }
                },

                addItem(product) {
                    // Verifica si ya existe en la receta
                    const existingIndex = this.items.findIndex(item => item.product_id === product.id);
                    
                    if (existingIndex !== -1) {
                        // Si existe, le suma 1
                        this.items[existingIndex].quantity++;
                    } else {
                        // Si no existe, lo agrega al inicio
                        this.items.unshift({
                            product_id: product.id,
                            code: product.code,
                            name: product.name,
                            quantity: 1,
                            is_mandatory: true
                        });
                    }
                    
                    this.searchQuery = '';
                    this.showDropdown = false;
                    this.searchResults = [];
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                async saveRecipe() {
                    if (this.items.length === 0) return;
                    
                    this.isSaving = true;
                    
                    try {
                        const response = await fetch(this.saveEndpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ items: this.items })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message || 'Ocurrió un error al guardar.');
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Error de conexión al intentar guardar.');
                    } finally {
                        this.isSaving = false;
                    }
                }
            }));
        });
    </script>
    <style>
        /* Estilo sutil para el switch de obligatorio */
        .dot { transition: all 0.3s ease-in-out; }
    </style>
    @endpush
</x-app-layout>