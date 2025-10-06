<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('product-units.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors duration-150">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Registrar Entrada de Productos') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('product-units.index') }}" 
                           class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            Inventario
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Nueva Entrada</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Tarjeta Principal -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Header con gradiente -->
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-8">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-white bg-opacity-20 rounded-lg p-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-white">Registrar Entrada de Productos</h3>
                            <p class="mt-1 text-green-100">Ingresa nuevos productos al inventario</p>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form action="{{ route('product-units.store') }}" method="POST" class="p-8" x-data="{ 
                    submitting: false,
                    selectedProduct: null,
                    products: {{ $products->toJson() }},
                    updateProductInfo() {
                        const productId = this.$refs.productSelect.value;
                        this.selectedProduct = this.products.find(p => p.id == productId);
                    }
                }">
                    @csrf

                    <div class="space-y-6">
                        <!-- Selección de Producto -->
                        <div>
                            <label for="product_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Producto
                                <span class="text-red-500">*</span>
                            </label>
                            <select name="product_id" 
                                    id="product_id"
                                    x-ref="productSelect"
                                    @change="updateProductInfo()"
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 @error('product_id') border-red-300 @enderror" 
                                    required>
                                <option value="">-- Selecciona un producto --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->code }} - {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            

                            <!-- Info del producto seleccionado -->
                            <div x-show="selectedProduct" x-cloak class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="text-sm">
                                        <p class="font-semibold text-blue-900 mb-1">Información del producto:</p>
                                        <ul class="space-y-1 text-blue-700">
                                            <li x-show="selectedProduct?.rfid_tracking">✓ Usa rastreo RFID (se generará EPC automático)</li>
                                            <li x-show="!selectedProduct?.rfid_tracking">✓ Usa número de serie (se generará automático)</li>
                                            <li x-show="selectedProduct?.has_expiration">⚠️ Producto con fecha de caducidad</li>
                                            <li x-show="!selectedProduct?.has_expiration">✓ Producto sin caducidad</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cantidad y Lote -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Cantidad -->
                            <div>
                                <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Cantidad de Unidades
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                        </svg>
                                    </div>
                                    <input type="number" 
                                           name="quantity" 
                                           id="quantity" 
                                           min="1"
                                           max="100"
                                           value="{{ old('quantity', 1) }}"
                                           class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 @error('quantity') border-red-300 @enderror" 
                                           placeholder="1"
                                           required>
                                </div>
                                @error('quantity')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @else
                                    <p class="mt-2 text-sm text-gray-500">Número de unidades a ingresar (máx. 100)</p>
                                @enderror
                            </div>

                            <!-- Número de Lote -->
                            <div>
                                <label for="batch_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Número de Lote
                                    <span class="text-gray-400 text-xs font-normal">(Opcional)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <input type="text" 
                                           name="batch_number" 
                                           id="batch_number" 
                                           value="{{ old('batch_number') }}"
                                           class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 @error('batch_number') border-red-300 @enderror" 
                                           placeholder="Ej: LOTE-2025-001">
                                </div>
                                @error('batch_number')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @else
                                    <p class="mt-2 text-sm text-gray-500">Número de lote del fabricante</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Fechas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Fecha de Fabricación -->
                            <div>
                                <label for="manufacture_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Fecha de Fabricación
                                    <span class="text-gray-400 text-xs font-normal">(Opcional)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <input type="date" 
                                           name="manufacture_date" 
                                           id="manufacture_date" 
                                           value="{{ old('manufacture_date') }}"
                                           max="{{ date('Y-m-d') }}"
                                           class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 @error('manufacture_date') border-red-300 @enderror">
                                </div>
                                @error('manufacture_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @else
                                    <p class="mt-2 text-sm text-gray-500">Fecha de fabricación del producto</p>
                                @enderror
                            </div>

                            <!-- Fecha de Caducidad -->
                            <div>
                                <label for="expiration_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Fecha de Caducidad
                                    <span class="text-gray-400 text-xs font-normal" x-show="!selectedProduct?.has_expiration">(Opcional)</span>
                                    <span class="text-red-500" x-show="selectedProduct?.has_expiration">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <input type="date" 
                                           name="expiration_date" 
                                           id="expiration_date" 
                                           value="{{ old('expiration_date') }}"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                           class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 @error('expiration_date') border-red-300 @enderror">
                                </div>
                                @error('expiration_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @else
                                    <p class="mt-2 text-sm text-gray-500">Fecha en que el producto caduca</p>
                                @enderror
                            </div>
                        </div>

                        
                        <!-- Información de Costos (Opcional) -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Información de Costos (Opcional)</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Costo de Adquisición -->
                                <div>
                                    <label for="acquisition_cost" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Costo de Adquisición
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">$</span>
                                        </div>
                                        <input type="number" 
                                               name="acquisition_cost" 
                                               id="acquisition_cost" 
                                               step="0.01"
                                               min="0"
                                               value="{{ old('acquisition_cost') }}"
                                               class="pl-8 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 @error('acquisition_cost') border-red-300 @enderror" 
                                               placeholder="0.00">
                                    </div>
                                    @error('acquisition_cost')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @else
                                        <p class="mt-2 text-sm text-gray-500">Costo por unidad</p>
                                    @enderror
                                </div>

                                <!-- Número de Factura -->
                                <div>
                                    <label for="supplier_invoice" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Número de Factura
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <input type="text" 
                                               name="supplier_invoice" 
                                               id="supplier_invoice" 
                                               value="{{ old('supplier_invoice') }}"
                                               class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 @error('supplier_invoice') border-red-300 @enderror" 
                                               placeholder="Ej: FAC-2025-001">
                                    </div>
                                    @error('supplier_invoice')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @else
                                        <p class="mt-2 text-sm text-gray-500">Número de factura del proveedor</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div>
                            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                Notas u Observaciones
                                <span class="text-gray-400 text-xs font-normal">(Opcional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </div>
                                <textarea name="notes" 
                                          id="notes" 
                                          rows="4"
                                          class="pl-10 pt-3 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 @error('notes') border-red-300 @enderror" 
                                          placeholder="Información adicional sobre este ingreso...">{{ old('notes') }}</textarea>
                            </div>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-2 text-sm text-gray-500">Cualquier información adicional relevante</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Información importante -->
                    <div class="mt-8 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">
                                    <strong>Importante:</strong> Los códigos EPC o números de serie se generarán automáticamente para cada unidad. Puedes visualizarlos después del registro.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="mt-8 flex flex-col sm:flex-row-reverse gap-3 pt-6 border-t border-gray-200">
                        <button type="submit" 
                                :disabled="submitting"
                                @click="submitting = true"
                                class="inline-flex justify-center items-center px-6 py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-show="!submitting">Registrar Entrada</span>
                            <span x-show="submitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Registrando...
                            </span>
                        </button>
                        <a href="{{ route('product-units.index') }}" 
                           class="inline-flex justify-center items-center px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 font-semibold rounded-lg border-2 border-gray-300 shadow-sm hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
