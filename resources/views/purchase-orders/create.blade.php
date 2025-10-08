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

                            <input type="hidden" name="items_json" :value="JSON.stringify(items)">

                            <div class="flex justify-end gap-3">
                                </div>

                            <!-- Almacén Destino -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Almacén Destino <span class="text-red-500">*</span>
                                </label>
                                <select name="destination_warehouse_id" 
                                        class="block w-full rounded-lg focus:ring-2 focus:ring-blue-500 @error('destination_warehouse_id') border-red-300 @enderror" 
                                        required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('destination_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('destination_warehouse_id')
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

                    <!-- Items de la Orden -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
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
                        <div class="p-6">
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

                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
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

                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <!-- Producto -->
                                            <div class="md:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Producto *</label>
                                                <select
                                                        x-model="item.product_id"
                                                        @change="updateProduct(index)"
                                                        class="block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500"
                                                        required>
                                                    <option value="">Seleccionar...</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" 
                                                                data-code="{{ $product->code }}"
                                                                data-name="{{ $product->name }}"
                                                                data-description="{{ $product->description }}"
                                                                data-price="{{ $product->price ?? 0 }}">
                                                            {{ $product->code }} - {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
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
                
                updateProduct(index) {
                    const select = document.querySelector(`select[name="items[${index}][product_id]"]`);
                    const option = select.options[select.selectedIndex];
                    
                    if (option.value) {
                        this.items[index].unit_price = parseFloat(option.dataset.price) || 0;
                        this.calculateSubtotal(index);
                    }
                },
                
                calculateSubtotal(index) {
                    const item = this.items[index];
                    item.subtotal = item.quantity_ordered * item.unit_price;
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
                        // Si no hay productos, AHORA SÍ prevenimos el envío.
                        e.preventDefault(); 
                        alert('Debes agregar al menos un producto');
                    }
                    // Si hay productos, no hacemos nada y dejamos que el formulario se envíe solo.
                }
            }
        }
    </script>
    @endpush
</x-app-layout>