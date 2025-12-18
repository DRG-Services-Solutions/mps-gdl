<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="text-sm font-medium">Volver a productos</span>
                </a>
                <div class="border-l border-gray-300 pl-4">
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        Agregar Producto al Catálogo
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Cree la ficha maestra del producto.
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Alerta informativa --}}
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Nota importante:</strong> Este formulario crea la información maestra del producto en el catálogo. 
                            Los identificadores únicos (EPCs o números de serie) se asignarán al registrar las entradas de inventario.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                
                {{-- Errores de validación --}}
                @if ($errors->any())
                    <div class="px-6 py-4 bg-red-50 border-b border-red-200">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-0.5"></i>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-red-800">Por favor corrija los siguientes errores:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form action="{{ route('products.store') }}" method="POST" class="p-6">
                    @csrf
                    
                    {{-- SECCIÓN 1: INFORMACIÓN BÁSICA --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-id-card text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Información Básica del Producto</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Nombre --}}
                            <div>
                                <label for="name" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tag text-gray-400 mr-2"></i>
                                    Nombre del Producto
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('name') border-red-500 @enderror"
                                       placeholder="Ej: Bisturí Quirúrgico N°15">
                                @error('name')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Código --}}
                            <div>
                                <label for="code" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-barcode text-gray-400 mr-2"></i>
                                    Código del Producto
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="text" name="code" id="code" value="{{ old('code') }}" required
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('code') border-red-500 @enderror"
                                       placeholder="'Ej: AR-001'">
                                @error('code')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Estado --}}
                            <div>
                                <label for="status" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-toggle-on text-gray-400 mr-2"></i>
                                    Estado en Catálogo
                                </label>
                                <select name="status" id="status"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                        ✅ {{ __('Activo') }}
                                    </option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                        ⏸️ {{ __('Inactivo') }}
                                    </option>
                                    <option value="discontinued" {{ old('status') == 'discontinued' ? 'selected' : '' }}>
                                        🚫 {{ __('Descontinuado') }}
                                    </option>
                                </select>
                            </div>

                            {{-- Descripción --}}
                            <div class="md:col-span-2">
                                <label for="description" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-align-left text-gray-400 mr-2"></i>
                                    {{ __('Descripción') }}
                                </label>
                                <textarea name="description" id="description" rows="3"
                                          class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                          placeholder="{{ __('Descripción detallada del producto...') }}">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 2: CLASIFICACIÓN --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-sitemap text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Clasificación') }}</h3>
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
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
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
                                <select name="category_id" id="category_id" x-model="selectedCategory" 
                                        @change="onCategoryChange()"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                                    <option value="">{{ __('-- Seleccione --') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            

                            {{-- Especialidad Médica --}}
                            <div>
                                <label for="specialty_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-stethoscope text-gray-400 mr-2"></i>
                                    {{ __('Especialidad Médica') }}
                                </label>
                                <select name="specialty_id" id="specialty_id"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                                    <option value="">{{ __('-- Seleccione --') }}</option>
                                    @foreach($specialties as $specialty)
                                        <option value="{{ $specialty->id }}" {{ old('specialty_id') == $specialty->id ? 'selected' : '' }}>
                                            {{ $specialty->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: TIPO DE TRACKING Y CARACTERÍSTICAS --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-route text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Tipo de Trazabilidad y Requisitos') }}</h3>
                        </div>

                        {{-- Información explicativa --}}
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        <strong>Tipos de trazabilidad:</strong>
                                    </p>
                                    <ul class="mt-2 text-sm text-blue-700 space-y-1 list-disc list-inside">
                                        <li><strong>Code:</strong> Control numérico sin identificadores individuales</li>
                                        <li><strong>RFID:</strong> Cada unidad física tendrá etiqueta RFID (se genera al recibir inventario)</li>
                                        <li><strong>Serial:</strong> Instrumental con número de serie grabado de fábrica</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            
                            {{-- Tipo de Rastreo --}}
                            <div>
                                <label for="tracking_type" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-radar text-gray-400 mr-2"></i>
                                    {{ __('Tipo de Rastreo') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="tracking_type" id="tracking_type" required
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('tracking_type') border-red-500 @enderror">
                                    <option value="code" {{ old('tracking_type', 'code') == 'code' ? 'selected' : '' }}>
                                        📦 Solo Code (control numérico)
                                    </option>
                                    <option value="rfid" {{ old('tracking_type') == 'rfid' ? 'selected' : '' }}>
                                        📡 RFID (etiquetas al recibir)
                                    </option>
                                    <option value="serial" {{ old('tracking_type') == 'serial' ? 'selected' : '' }}>
                                        🔢 Número de Serie (grabado de fábrica)
                                    </option>
                                </select>
                                @error('tracking_type')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- REQUIERE ESTERILIZACIÓN (SIEMPRE HABILITADO) --}}
                            <div>
                                <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm bg-white hover:border-green-400 cursor-pointer transition-all duration-200">
                                    <input type="checkbox"
                                        name="requires_sterilization"
                                        id="requires_sterilization"
                                        value="1"
                                        {{ old('requires_sterilization') ? 'checked' : '' }}
                                        class="h-5 w-5 text-green-600 border-gray-300 rounded focus:ring-green-500 mt-0.5">

                                    <span class="ml-3 text-sm flex-1">
                                        <span class="block font-medium text-gray-900 flex items-center">
                                            <i class="fas fa-shield-virus text-green-600 mr-2"></i>
                                            {{ __('Requiere Esterilización') }}
                                        </span>
                                        <span class="block text-gray-500 mt-1">
                                            {{ __('Marque SOLO para productos clasificados como instrumental quirúrgico o médico reutilizable que deba pasar por ciclos de esterilización.') }}
                                        </span>
                                    </span>
                                </label>
                            </div>

                            {{-- REQUIERE REFRIGERACIÓN (SIEMPRE HABILITADO) --}}
                            <div>
                                <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm bg-white hover:border-blue-400 cursor-pointer transition-all duration-200">
                                    <input type="checkbox"
                                            name="requires_refrigeration"
                                            id="requires_refrigeration"
                                            value="1"
                                            {{ old('requires_refrigeration') ? 'checked' : '' }}
                                            class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">

                                    <span class="ml-3 text-sm flex-1">
                                        <span class="block font-medium text-gray-900 flex items-center">
                                            <i class="fas fa-snowflake text-blue-600 mr-2"></i>
                                            {{ __('Requiere Refrigeración') }}
                                        </span>
                                        <span class="block text-gray-500 mt-1">
                                            {{ __('Marque si el producto debe mantenerse en refrigeración constante (temperatura entre 2°C y 8°C típicamente).') }}
                                        </span>
                                    </span>
                                </label>
                            </div>

                            {{-- REQUIERE CONTROL DE TEMPERATURA < 45°C (SIEMPRE HABILITADO) --}}
                            <div>
                                <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm bg-white hover:border-orange-400 cursor-pointer transition-all duration-200">
                                    <input type="checkbox"
                                            name="requires_temperature"
                                            id="requires_temperature"
                                            value="1"
                                            {{ old('requires_temperature') ? 'checked' : '' }}
                                            class="h-5 w-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 mt-0.5">

                                    <span class="ml-3 text-sm flex-1">
                                        <span class="block font-medium text-gray-900 flex items-center">
                                            <i class="fas fa-thermometer-half text-orange-600 mr-2"></i>
                                            {{ __('Requiere Control de Temperatura') }}
                                        </span>
                                        <span class="block text-gray-500 mt-1">
                                            {{ __('Marque si el producto requiere almacenamiento con temperatura controlada menor a 45°C (pero no necesariamente refrigeración). Ejemplos: medicamentos termosensibles, reactivos, algunos dispositivos electrónicos médicos.') }}
                                        </span>
                                        <span class="block text-xs text-orange-600 mt-2 font-medium">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ __('Temperatura de almacenamiento: < 45°C') }}
                                        </span>
                                    </span>
                                </label>
                            </div>

                        </div>
                    </div>

                    {{-- SECCIÓN 4: INFORMACIÓN DE INVENTARIO --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-warehouse text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Información de Inventario') }}</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Stock Mínimo --}}
                            <div>
                                <label for="minimum_stock" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-box text-gray-400 mr-2"></i>
                                    Stock Mínimo Deseado
                                </label>
                                <input type="number" name="minimum_stock" id="minimum_stock" min="0"
                                       value="{{ old('minimum_stock', 0) }}"
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('minimum_stock') border-red-500 @enderror"
                                       placeholder="0">
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Cantidad mínima para generar alertas de reorden
                                </p>
                                @error('minimum_stock')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Precio de Lista --}}
                            <div>
                                <label for="list_price" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-dollar-sign text-gray-400 mr-2"></i>
                                    Precio Unitario
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 font-medium">
                                        $
                                    </span>
                                    <input type="number" name="list_price" id="list_price" min="0" step="0.01"
                                           value="{{ old('list_price', 0) }}"
                                           class="w-full pl-8 pr-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('list_price') border-red-500 @enderror"
                                           placeholder="0.00">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Precio de referencia del producto
                                </p>
                                @error('list_price')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('products.index') }}" 
                        class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5">
                            <i class="fas fa-save mr-2"></i>
                            Guardar en Catálogo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    function productForm(allSubcategories) {
        return {
            // Estado inicial
            selectedCategory: '{{ old("category_id") ?? "" }}',
            
            

            // Método que se ejecuta al cambiar la categoría
            onCategoryChange() {
                // Solo limpiar la subcategoría al cambiar la categoría
                this.selectedSubcategory = '';
            }
        }
    }
</script>
@endpush
</x-app-layout>