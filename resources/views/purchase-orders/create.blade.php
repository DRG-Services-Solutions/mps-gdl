<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('purchase-orders.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors duration-150">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Nueva Orden de Compra') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('purchase-orders.store') }}" 
                  method="POST" 
                  x-data="purchaseOrderForm()"
                  @submit="submitForm">
                @csrf

                <div class="space-y-6">
                    <!-- Información General -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                            <h3 class="text-xl font-bold text-white">Información General</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Proveedor -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Proveedor <span class="text-red-500">*</span>
                                </label>
                                <select name="supplier_id" 
                                        class="block w-full rounded-lg focus:ring-2 focus:ring-blue-500 @error('supplier_id') border-red-300 @enderror" 
                                        required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Razón Social (Legal Entity) -->
                            <div>
                                <label for="legal_entity_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Razón Social') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="legal_entity_id" 
                                        id="legal_entity_id"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('legal_entity_id') border-red-500 @enderror"
                                        required>
                                    <option value="">{{ __('Seleccionar razón social...') }}</option>
                                    @foreach(\App\Models\LegalEntity::active()->orderBy('name')->get() as $entity)
                                        <option value="{{ $entity->id }}" 
                                                {{ old('legal_entity_id', $purchaseOrder->legal_entity_id ?? '') == $entity->id ? 'selected' : '' }}>
                                            {{ $entity->name }} - {{ $entity->rfc }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('legal_entity_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ __('Selecciona la razón social con la que se realiza esta compra') }}
                                </p>
                            </div>

                            <div>
                                <label for="sub_warehouse_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Sub-Almacén Virtual
                                    <span class="text-gray-500 text-xs">(Opcional - organización interna)</span>
                                </label>
                                <select name="sub_warehouse_id" 
                                        id="sub_warehouse_id"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Sin asignar</option>
                                    <!-- Se llenará dinámicamente con JavaScript -->
                                </select>
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Los productos recibidos se organizarán en este sub-almacén virtual
                                </p>
                                @error('sub_warehouse_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>


                            <!-- Fecha Esperada -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Fecha Esperada de Entrega
                                </label>
                                <input type="date" 
                                       name="expected_date" 
                                       value="{{ old('expected_date') }}"
                                       min="{{ date('Y-m-d') }}"
                                       class="block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Campo oculto para items -->
                    <input type="hidden" name="items_json" :value="JSON.stringify(items)">

                    <!-- Items de la Orden -->
                    <div class="bg-white shadow-xl sm:rounded-lg" style="min-height: 400px; overflow: visible;">
                        <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 flex justify-between items-center">
                            <h3 class="text-xl font-bold text-white">Productos</h3>
                            <button type="button" 
                                    @click="addItem"
                                    class="px-4 py-2 bg-white text-green-600 rounded-lg hover:bg-green-50 font-semibold transition-all">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Agregar Producto
                            </button>
                        </div>
                        <div class="p-6" style="overflow: visible;">
                            <template x-if="items.length === 0">
                                <div class="text-center py-12 bg-gray-50 rounded-lg">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">No hay productos agregados</p>
                                    <button type="button" 
                                            @click="addItem"
                                            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Agregar Primer Producto
                                    </button>
                                </div>
                            </template>

                            <div class="space-y-4" style="overflow: visible;">

                                <template x-for="(item, index) in items" :key="index">
                                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50" style="position: relative; overflow: visible;">


                                        <div class="flex justify-between items-start mb-3">
                                            <h4 class="font-semibold text-gray-700">Producto <span x-text="index + 1"></span></h4>
                                            <button type="button" 
                                                    @click="removeItem(index)"
                                                    class="text-red-600 hover:text-red-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4" style="overflow: visible;">

                                            <!-- Producto con Búsqueda AJAX -->
                                           <div class="md:col-span-2" 
                                            x-data="{
                                                searchQuery: '',
                                                showDropdown: false,
                                                searchResults: [],
                                                isSearching: false,
                                                selectedProduct: null
                                            }"
                                            style="position: relative; overflow: visible; z-index: 100;">

                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    Producto * 
                                                    <span x-show="isSearching" class="text-xs text-gray-500">(Buscando...)</span>
                                                </label>
                                                
                                                <div class="relative" style="overflow: visible;">

                                                    <!-- Input de Búsqueda -->
                                                    <div class="relative" x-show="!selectedProduct">
                                                        <input type="text" 
                                                            x-model="searchQuery"
                                                            @input.debounce.300ms="async () => {
                                                                if (searchQuery.length < 2) {
                                                                    searchResults = [];
                                                                    showDropdown = false;
                                                                    return;
                                                                }
                                                                
                                                                isSearching = true;
                                                                showDropdown = true;
                                                                
                                                                try {
                                                                    const response = await fetch(`{{ route('products.search') }}?q=${encodeURIComponent(searchQuery)}`);
                                                                    searchResults = await response.json();
                                                                } catch (error) {
                                                                    console.error('Error buscando productos:', error);
                                                                    searchResults = [];
                                                                } finally {
                                                                    isSearching = false;
                                                                }
                                                            }"
                                                            @focus="if(searchQuery.length >= 2) showDropdown = true"
                                                            @click.away="showDropdown = false"
                                                            placeholder="Escribe al menos 2 caracteres..."
                                                            class="block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 pr-10"
                                                            autocomplete="off">
                                                        
                                                        <!-- Icono de búsqueda/loading -->
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                            <svg x-show="!isSearching" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                            </svg>
                                                            <svg x-show="isSearching" x-cloak class="w-5 h-5 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>

                                                    <!-- Dropdown de Resultados -->
                                                    <div x-show="showDropdown && !selectedProduct" 
                                                        x-cloak
                                                        x-transition
                                                        class="absolute z-[999] w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-80 overflow-y-auto"
                                                        style="min-width: 500px;">
                                                        
                                                        <!-- Resultados -->
                                                        <template x-for="product in searchResults" :key="product.id">
                                                            <div @click="
                                                                    item.product_id = product.id;
                                                                    item.unit_price = product.price || 0;
                                                                    calculateSubtotal(index);
                                                                    selectedProduct = product;
                                                                    searchQuery = '';
                                                                    showDropdown = false;
                                                                "
                                                                class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex-1">
                                                                        <div class="font-medium text-gray-900 text-sm" x-text="product.code"></div>
                                                                        <div class="text-xs text-gray-600" x-text="product.name"></div>
                                                                        <div x-show="product.description" x-cloak class="text-xs text-gray-500 mt-0.5" x-text="product.description"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>

                                                        <!-- Mensaje cuando no hay resultados -->
                                                        <div x-show="searchResults.length === 0 && !isSearching && searchQuery.length >= 2"
                                                            x-cloak
                                                            class="px-4 py-6 text-center text-gray-500 text-sm">
                                                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            <p>No se encontraron productos</p>
                                                            <p class="text-xs text-gray-400 mt-1">Intenta con otro término de búsqueda</p>
                                                        </div>

                                                        <!-- Mensaje de búsqueda mínima -->
                                                        <div x-show="searchQuery.length > 0 && searchQuery.length < 2"
                                                            x-cloak
                                                            class="px-4 py-6 text-center text-gray-500 text-sm">
                                                            <p>Escribe al menos 2 caracteres para buscar</p>
                                                        </div>
                                                    </div>

                                                    <!-- Producto seleccionado -->
                                                    <div x-show="selectedProduct" 
                                                        x-cloak
                                                        class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                                                        <div class="flex-1 min-w-0">
                                                            <div class="text-sm font-semibold text-green-900 truncate" x-text="selectedProduct?.code"></div>
                                                            <div class="text-xs text-green-700 truncate" x-text="selectedProduct?.name"></div>
                                                        </div>
                                                        <button type="button" 
                                                                @click="
                                                                    item.product_id = '';
                                                                    selectedProduct = null;
                                                                    item.unit_price = 0;
                                                                    calculateSubtotal(index);
                                                                "
                                                                class="ml-3 flex-shrink-0 text-green-600 hover:text-green-800 transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Cantidad -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
                                                <input type="number" 
                                                       x-model.number="item.quantity_ordered"
                                                       @input="calculateSubtotal(index)"
                                                       min="1"
                                                       class="block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500"
                                                       required>
                                            </div>

                                            <!-- Precio Unitario -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Precio Unitario *</label>
                                                <input type="number" 
                                                       x-model.number="item.unit_price"
                                                       @input="calculateSubtotal(index)"
                                                       step="0.01"
                                                       min="0"
                                                       class="block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500"
                                                       required>
                                            </div>
                                        </div>

                                        <!-- Subtotal -->
                                        <div class="mt-3 text-right">
                                            <span class="text-sm font-medium text-gray-600">Subtotal: </span>
                                            <span class="text-lg font-bold text-blue-600" x-text="'$' + item.subtotal.toFixed(2)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen y Notas -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Notas -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Notas</label>
                                    <textarea name="notes" 
                                              rows="6"
                                              class="block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500"
                                              placeholder="Observaciones adicionales...">{{ old('notes') }}</textarea>
                                </div>

                                <!-- Resumen de Totales -->
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6">
                                    <h4 class="text-lg font-bold text-gray-900 mb-4">Resumen de la Orden</h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                            <span class="text-sm text-gray-600">Total de Productos:</span>
                                            <span class="font-semibold text-gray-900" x-text="items.length"></span>
                                        </div>
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                            <span class="text-sm text-gray-600">Total de Piezas:</span>
                                            <span class="font-semibold text-gray-900" x-text="totalQuantity"></span>
                                        </div>
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                            <span class="text-sm text-gray-600">Subtotal:</span>
                                            <span class="font-semibold text-gray-900" x-text="'$' + subtotal.toFixed(2)"></span>
                                        </div>
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                            <span class="text-sm text-gray-600">IVA (16%):</span>
                                            <span class="font-semibold text-gray-900" x-text="'$' + tax.toFixed(2)"></span>
                                        </div>
                                        <div class="flex justify-between items-center pt-2">
                                            <span class="text-lg font-bold text-gray-900">Total:</span>
                                            <span class="text-2xl font-bold text-blue-600" x-text="'$' + total.toFixed(2)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('purchase-orders.index') }}" 
                           class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-all">
                            Cancelar
                        </a>
                        <button type="submit" 
                                :disabled="items.length === 0"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition-all">
                            Crear Orden de Compra
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function purchaseOrderForm() {
            return {
                items: [],
                
                addItem() {
                    this.items.push({
                        product_id: '',
                        quantity_ordered: 1,
                        unit_price: 0,
                        subtotal: 0
                    });
                },
                
                removeItem(index) {
                    this.items.splice(index, 1);
                },

                calculateSubtotal(index) {
                    const item = this.items[index];
                    item.subtotal = (item.quantity_ordered || 0) * (item.unit_price || 0);
                },
                
                get totalQuantity() {
                    return this.items.reduce((sum, item) => sum + (item.quantity_ordered || 0), 0);
                },
                
                get subtotal() {
                    return this.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
                },
                
                get tax() {
                    return this.subtotal * 0.16;
                },
                
                get total() {
                    return this.subtotal + this.tax;
                },
                
                submitForm(e) {
                    if (this.items.length === 0) {
                        e.preventDefault(); 
                        alert('Debes agregar al menos un producto');
                        return;
                    }
                    
                    // Validar que todos los items tengan producto seleccionado
                    const hasEmptyProduct = this.items.some(item => !item.product_id);
                    if (hasEmptyProduct) {
                        e.preventDefault();
                        alert('Todos los productos deben estar seleccionados');
                        return;
                    }
                }
            }
        }

        const subWarehousesData = @json($subWarehouses);

        // Función para actualizar el selector de sub-almacenes
        function updateSubWarehouses() {
            const legalEntitySelect = document.getElementById('legal_entity_id');
            const subWarehouseSelect = document.getElementById('sub_warehouse_id');
            
            const selectedEntityId = legalEntitySelect.value;
            
            // Limpiar opciones actuales (excepto la primera)
            subWarehouseSelect.innerHTML = '<option value="">Sin asignar</option>';
            
            if (!selectedEntityId) {
                subWarehouseSelect.disabled = true;
                return;
            }
            
            // Habilitar el selector
            subWarehouseSelect.disabled = false;
            
            // Obtener sub-almacenes de la entidad seleccionada
            const subWarehouses = subWarehousesData[selectedEntityId] || [];
            
            if (subWarehouses.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No hay sub-almacenes disponibles';
                option.disabled = true;
                subWarehouseSelect.appendChild(option);
                return;
            }
            
            // Agregar opciones de sub-almacenes
            subWarehouses.forEach(subWarehouse => {
                const option = document.createElement('option');
                option.value = subWarehouse.id;
                option.textContent = subWarehouse.name;
                
                // Mantener selección si existe (para old values)
                if ("{{ old('sub_warehouse_id') }}" == subWarehouse.id) {
                    option.selected = true;
                }
                
                subWarehouseSelect.appendChild(option);
            });
        }

        // Ejecutar al cargar la página si hay una entidad seleccionada
        document.addEventListener('DOMContentLoaded', function() {
            updateSubWarehouses();
        });

    </script>
    @endpush
</x-app-layout>