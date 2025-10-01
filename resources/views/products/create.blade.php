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
                        {{ __('Agregar Producto al Catálogo') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Cree la ficha maestra del producto. Las unidades físicas se registrarán al recibir inventario.') }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="productForm({{ json_encode($subcategories) }})">
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
                
                <form action="{{ route('products.store') }}" method="POST" class="p-6">
                    @csrf
                    
                    {{-- SECCIÓN 1: INFORMACIÓN BÁSICA --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-id-card text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Información Básica del Producto') }}</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Nombre --}}
                            <div>
                                <label for="name" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tag text-gray-400 mr-2"></i>
                                    {{ __('Nombre del Producto') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('name') border-red-500 @enderror"
                                       placeholder="{{ __('Ej: Bisturí Quirúrgico N°15') }}">
                                @error('name')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Código --}}
                            <div>
                                <label for="code" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-barcode text-gray-400 mr-2"></i>
                                    {{ __('Código del Catálogo') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="text" name="code" id="code" value="{{ old('code') }}" required
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('code') border-red-500 @enderror"
                                       placeholder="{{ __('Ej: PROD-001') }}">
                                @error('code')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Modelo --}}
                            <div>
                                <label for="model" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-cogs text-gray-400 mr-2"></i>
                                    {{ __('Modelo del Fabricante') }}
                                </label>
                                <input type="text" name="model" id="model" value="{{ old('model') }}"
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                       placeholder="{{ __('Opcional') }}">
                            </div>

                            {{-- Estado --}}
                            <div>
                                <label for="status" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-toggle-on text-gray-400 mr-2"></i>
                                    {{ __('Estado en Catálogo') }}
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
                            {{-- Fabricante --}}
                            <div>
                                <label for="manufacturer_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-industry text-gray-400 mr-2"></i>
                                    {{ __('Fabricante') }}
                                </label>
                                <select name="manufacturer_id" id="manufacturer_id"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                                    <option value="">{{ __('-- Seleccione --') }}</option>
                                    @foreach($manufacturers as $manufacturer)
                                        <option value="{{ $manufacturer->id }}" {{ old('manufacturer_id') == $manufacturer->id ? 'selected' : '' }}>
                                            {{ $manufacturer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Categoría --}}
                            <div>
                                <label for="category_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tags text-gray-400 mr-2"></i>
                                    {{ __('Categoría') }}
                                </label>
                                <select name="category_id" id="category_id" x-model="selectedCategory" @change="$nextTick(() => { document.getElementById('subcategory_id').value = '' })"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                                    <option value="">{{ __('-- Seleccione --') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            {{-- Subcategoría --}}
                            <div>
                                <label for="subcategory_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-layer-group text-gray-400 mr-2"></i>
                                    {{ __('Subcategoría') }}
                                </label>
                                <select name="subcategory_id" id="subcategory_id" 
                                        :disabled="!selectedCategory || filteredSubcategories.length === 0"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 disabled:bg-gray-100 disabled:cursor-not-allowed">
                                    <option value="">{{ __('-- Seleccione --') }}</option>
                                    <template x-for="subcategory in filteredSubcategories" :key="subcategory.id">
                                        <option :value="subcategory.id" 
                                                :selected="subcategory.id == {{ old('subcategory_id', 'null') }}"
                                                x-text="subcategory.name"></option>
                                    </template>
                                </select>
                                <p class="mt-1 text-xs text-gray-500" x-show="!selectedCategory">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ __('Primero seleccione una categoría') }}
                                </p>
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
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Tipo de Trazabilidad') }}</h3>
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
                                        <li><strong>Stock:</strong> Control numérico sin identificadores individuales</li>
                                        <li><strong>RFID:</strong> Cada unidad física tendrá etiqueta RFID (se genera al recibir inventario)</li>
                                        <li><strong>Serial:</strong> Instrumental con número de serie grabado de fábrica</li>
                                        <li><strong>Ninguno:</strong> Sin seguimiento de inventario</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Tipo de Rastreo --}}
                            <div class="md:col-span-2">
                                <label for="tracking_type" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-radar text-gray-400 mr-2"></i>
                                    {{ __('Tipo de Rastreo') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="tracking_type" id="tracking_type" required
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('tracking_type') border-red-500 @enderror">
                                    <option value="stock" {{ old('tracking_type', 'stock') == 'stock' ? 'selected' : '' }}>
                                        📦 Solo Stock (control numérico)
                                    </option>
                                    <option value="rfid" {{ old('tracking_type') == 'rfid' ? 'selected' : '' }}>
                                        📡 RFID (etiquetas al recibir)
                                    </option>
                                    <option value="serial" {{ old('tracking_type') == 'serial' ? 'selected' : '' }}>
                                        🔢 Número de Serie (grabado de fábrica)
                                    </option>
                                    <option value="none" {{ old('tracking_type') == 'none' ? 'selected' : '' }}>
                                        🚫 Sin rastreo
                                    </option>
                                </select>
                                @error('tracking_type')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>
                        </div>

                        {{-- Características --}}
                        <div class="mt-6 bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-check-square text-indigo-600 mr-2"></i>
                                {{ __('Características del Producto') }}
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <label class="inline-flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded transition-colors duration-200">
                                    <input type="checkbox" name="requires_sterilization" value="1" 
                                           class="form-checkbox h-5 w-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500" 
                                           {{ old('requires_sterilization') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        <i class="fas fa-bacteria text-purple-500 mr-1"></i>
                                        {{ __('Requiere Esterilización') }}
                                    </span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded transition-colors duration-200">
                                    <input type="checkbox" name="is_consumable" value="1" 
                                           class="form-checkbox h-5 w-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500" 
                                           {{ old('is_consumable') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        <i class="fas fa-recycle text-green-500 mr-1"></i>
                                        {{ __('Es Consumible') }}
                                    </span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded transition-colors duration-200">
                                    <input type="checkbox" name="is_single_use" value="1" 
                                           class="form-checkbox h-5 w-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500" 
                                           {{ old('is_single_use') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        <i class="fas fa-ban text-red-500 mr-1"></i>
                                        {{ __('Uso Único') }}
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
                            {{-- Costo Unitario --}}
                            <div>
                                <label for="unit_cost" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-dollar-sign text-gray-400 mr-2"></i>
                                    {{ __('Costo Unitario Promedio ($)') }}
                                </label>
                                <input type="number" name="unit_cost" id="unit_cost" step="0.01" min="0"
                                       value="{{ old('unit_cost', '0.00') }}" 
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                       placeholder="0.00">
                            </div>

                            {{-- Stock Mínimo --}}
                            <div>
                                <label for="minimum_stock" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-box text-gray-400 mr-2"></i>
                                    {{ __('Stock Mínimo Deseado') }}
                                </label>
                                <input type="number" name="minimum_stock" id="minimum_stock" min="0"
                                       value="{{ old('minimum_stock', '0') }}"
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                       placeholder="0">
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ __('Cantidad mínima para generar alertas de reorden') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 5: ESPECIFICACIONES TÉCNICAS --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-list-ul text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Especificaciones Técnicas (Opcional)') }}</h3>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <textarea name="specifications" id="specifications" rows="4"
                                      class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                      placeholder="{{ __('Dimensiones, peso, materiales, características técnicas...') }}">{{ old('specifications') }}</textarea>
                        </div>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('products.index') }}" 
                        class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5">
                            <i class="fas fa-save mr-2"></i>
                            {{ __('Guardar en Catálogo') }}
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
                selectedCategory: '{{ old("category_id") }}',
                
                get filteredSubcategories() {
                    if (!this.selectedCategory) {
                        return [];
                    }
                    return allSubcategories.filter(sub => sub.category_id == this.selectedCategory);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>