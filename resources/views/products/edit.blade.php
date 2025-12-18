<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="text-sm font-medium">{{ __('Volver a productos') }}</span>
                </a>
                <div class="border-l border-gray-300 pl-4">
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ __('Editar Producto') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Modifica los detalles del producto: ') }} <span class="font-semibold text-indigo-600">{{ $product->name }}</span>
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="productForm({{ json_encode($subcategories) }}, {{ $product->category_id ?? 'null' }})">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                
                {{-- Errores de validación --}}
                @if ($errors->any())
                    <div class="px-6 py-4 bg-red-50 border-b border-red-200">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-0.5"></i>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-red-800">{{ __('Por favor corrija los siguientes errores:') }}</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form action="{{ route('products.update', $product) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')
                    
                    {{-- SECCIÓN 1: IDENTIFICACIÓN --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-id-card text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Datos de Identificación') }}</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Nombre --}}
                            <div>
                                <label for="name" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tag text-gray-400 mr-2"></i>
                                    {{ __('Nombre del Producto') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('name') border-red-500 @enderror"
                                       placeholder="{{ __('Ej: Bisturí Quirúrgico N°15') }}">
                                @error('name')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Código --}}
                            <div>
                                <label for="code" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-barcode text-gray-400 mr-2"></i>
                                    {{ __('Código de Referencia') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="text" name="code" id="code" value="{{ old('code', $product->code) }}" required
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('code') border-red-500 @enderror"
                                       placeholder="{{ __('Ej: PROD-001') }}">
                                @error('code')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Descripción --}}
                            <div class="md:col-span-2">
                                <label for="description" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-align-left text-gray-400 mr-2"></i>
                                    {{ __('Descripción') }}
                                </label>
                                <textarea name="description" id="description" rows="3"
                                          class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                          placeholder="{{ __('Descripción detallada del producto...') }}">{{ old('description', $product->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 2: CLASIFICACIÓN --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-sitemap text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Clasificación y Aplicación') }}</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Proveedor --}}
                            <div>
                                <label for="supplier_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-industry text-gray-400 mr-2"></i>
                                    {{ __('Proveedor') }}
                                </label>
                                <select name="supplier_id" id="supplier_id"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('supplier_id') border-red-500 @enderror">
                                    <option value="">{{ __('-- Seleccione un proveedor --') }}</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" 
                                                {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Categoría --}}
                            <div>
                                <label for="category_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tags text-gray-400 mr-2"></i>
                                    {{ __('Categoría') }}
                                </label>
                                <select name="category_id" id="category_id"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('category_id') border-red-500 @enderror">
                                    <option value="">{{ __('-- Seleccione una categoría --') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>
                            
                            

                            {{-- Especialidad Médica --}}
                            <div>
                                <label for="specialty_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-stethoscope text-gray-400 mr-2"></i>
                                    {{ __('Especialidad Médica') }}
                                </label>
                                <select name="specialty_id" id="specialty_id"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('specialty_id') border-red-500 @enderror">
                                    <option value="">{{ __('-- Seleccione la especialidad --') }}</option>
                                    @foreach($specialties as $specialty)
                                        <option value="{{ $specialty->id }}" {{ old('specialty_id', $product->specialty_id) == $specialty->id ? 'selected' : '' }}>
                                            {{ $specialty->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('specialty_id')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: INVENTARIO Y CARACTERÍSTICAS --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-warehouse text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Inventario y Características') }}</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            {{-- Stock Mínimo --}}
                            <div>
                                <label for="minimum_stock" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-box text-gray-400 mr-2"></i>
                                    {{ __('Stock Mínimo') }}
                                </label>
                                <input type="number" name="minimum_stock" id="minimum_stock" min="0"
                                       value="{{ old('minimum_stock', $product->minimum_stock) }}"
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('minimum_stock') border-red-500 @enderror"
                                       placeholder="0">
                                @error('minimum_stock')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Precio de Lista --}}
                            <div>
                                <label for="list_price" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-dollar-sign text-gray-400 mr-2"></i>
                                    {{ __('Precio de Lista') }}
                                </label>
                                <input type="number" name="list_price" id="list_price" min="0" step="0.01"
                                       value="{{ old('list_price', $product->list_price) }}"
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('list_price') border-red-500 @enderror"
                                       placeholder="0.00">
                                @error('list_price')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>
                        </div>
                        
                        {{-- Características (Checkboxes) --}}
                        <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-check-square text-indigo-600 mr-2"></i>
                                {{ __('Características del Producto') }}
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                {{-- REQUIERE ESTERILIZACIÓN (SIEMPRE HABILITADO) --}}
                                <div class="relative group">
                                    <label class="inline-flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded transition-colors duration-200">
                                        <input type="checkbox" name="requires_sterilization" value="1" 
                                            class="form-checkbox h-5 w-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500" 
                                            {{ old('requires_sterilization', $product->requires_sterilization) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm font-medium text-gray-700 flex items-center">
                                            {{ __('Requiere Esterilización') }}
                                            <i class="fas fa-info-circle text-gray-400 ml-1"></i>
                                        </span>
                                    </label>
                                    <div class="absolute z-10 hidden group-hover:block px-3 py-2 text-xs font-normal text-white bg-indigo-600 rounded-lg whitespace-nowrap bottom-full mb-2 left-1/2 transform -translate-x-1/2 shadow-lg">
                                        {{ __('Solo los productos de tipo instrumental requieren esterilización.') }}
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                            <div class="border-4 border-transparent border-t-indigo-600"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- REQUIERE REFRIGERACIÓN (SIEMPRE HABILITADO) --}}
                                <div class="relative group">
                                    <label class="inline-flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded transition-colors duration-200">
                                        <input type="checkbox" name="requires_refrigeration" value="1" 
                                            class="form-checkbox h-5 w-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500" 
                                            {{ old('requires_refrigeration', $product->requires_refrigeration) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm font-medium text-gray-700 flex items-center">
                                            {{ __('Requiere Refrigeración') }}
                                            <i class="fas fa-info-circle text-gray-400 ml-1"></i>
                                        </span>
                                    </label>
                                    <div class="absolute z-10 hidden group-hover:block px-3 py-2 text-xs font-normal text-white bg-indigo-600 rounded-lg whitespace-nowrap bottom-full mb-2 left-1/2 transform -translate-x-1/2 shadow-lg">
                                        {{ __('Solo los productos clasificados requieren Refrigeración.') }}
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                            <div class="border-4 border-transparent border-t-indigo-600"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- REQUIERE CONTROL DE TEMPERATURA (SIEMPRE HABILITADO) --}}
                                <div class="relative group">
                                    <label class="inline-flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded transition-colors duration-200">
                                        <input type="checkbox" name="requires_temperature" value="1" 
                                            class="form-checkbox h-5 w-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500" 
                                            {{ old('requires_temperature', $product->requires_temperature) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm font-medium text-gray-700 flex items-center">
                                            {{ __('Control de Temperatura') }}
                                            <i class="fas fa-info-circle text-gray-400 ml-1"></i>
                                        </span>
                                    </label>
                                    <div class="absolute z-10 hidden group-hover:block px-3 py-2 text-xs font-normal text-white bg-indigo-600 rounded-lg whitespace-nowrap bottom-full mb-2 left-1/2 transform -translate-x-1/2 shadow-lg">
                                        {{ __('Productos que requieren monitoreo constante de temperatura.') }}
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                            <div class="border-4 border-transparent border-t-indigo-600"></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 4: TRACKING --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-satellite-dish text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Control y Trazabilidad') }}</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Tipo de Rastreo --}}
                            <div>
                                <label for="tracking_type" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-radar text-gray-400 mr-2"></i>
                                    {{ __('Tipo de Rastreo') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="tracking_type" id="tracking_type" required
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('tracking_type') border-red-500 @enderror">
                                    <option value="code" {{ old('tracking_type', $product->tracking_type) == 'code' ? 'selected' : '' }}>
                                        📦 {{ __('Code (Por Lote)') }}
                                    </option>
                                    <option value="rfid" {{ old('tracking_type', $product->tracking_type) == 'rfid' ? 'selected' : '' }}>
                                        📡 {{ __('RFID (Individual)') }}
                                    </option>
                                    <option value="serial" {{ old('tracking_type', $product->tracking_type) == 'serial' ? 'selected' : '' }}>
                                        🔢 {{ __('Serial (Único)') }}
                                    </option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ __('Define cómo se rastreará este producto en el sistema') }}
                                </p>
                                @error('tracking_type')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Estado del Producto --}}
                            <div>
                                <label for="status" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-toggle-on text-gray-400 mr-2"></i>
                                    {{ __('Estado del Producto') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="status" id="status" required
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                                    <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>
                                        ✅ {{ __('Activo') }}
                                    </option>
                                    <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>
                                        ⏸️ {{ __('Inactivo') }}
                                    </option>
                                    <option value="discontinued" {{ old('status', $product->status) == 'discontinued' ? 'selected' : '' }}>
                                        🚫 {{ __('Descontinuado') }}
                                    </option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ __('El estado determina la disponibilidad en el catálogo') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('products.index') }}" 
                           class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('Cancelar') }}
                        </a>
                        <div class="flex items-center space-x-3">
                            {{-- Botón de Eliminar --}}
                            <button type="button" onclick="if(confirm('¿Está seguro de eliminar este producto?')) { document.getElementById('delete-form').submit(); }"
                                    class="inline-flex items-center px-6 py-3 border border-red-300 shadow-sm text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                                <i class="fas fa-trash mr-2"></i>
                                {{ __('Eliminar') }}
                            </button>
                            
                            {{-- Botón de Actualizar --}}
                            <button type="submit"
                                    class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5">
                                <i class="fas fa-save mr-2"></i>
                                {{ __('Actualizar Producto') }}
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Formulario de Eliminación (oculto) --}}
                <form id="delete-form" action="{{ route('products.destroy', $product) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            {{-- Tarjeta de Información Adicional --}}
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-blue-900 mb-1">{{ __('Información del Registro') }}</h4>
                        <div class="text-xs text-blue-800 space-y-1">
                            <p><strong>{{ __('Creado:') }}</strong> {{ $product->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>{{ __('Última actualización:') }}</strong> {{ $product->updated_at->format('d/m/Y H:i') }}</p>
                            @if($product->deleted_at)
                                <p class="text-red-600"><strong>{{ __('Eliminado:') }}</strong> {{ $product->deleted_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function productForm(allSubcategories, initialCategoryId) {
            return {
                selectedCategory: initialCategoryId || '{{ old("category_id") }}',
            }
        }
    </script>
    @endpush
</x-app-layout>