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
                        {{ __('Crear Nuevo Producto') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Ingrese los detalles del nuevo artículo para el inventario.') }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden" 
                 x-data="productForm({{ json_encode($subcategories) }})">
                
                {{-- Muestra errores de validación si existen --}}
                @if ($errors->any())
                    <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                        <p class="font-bold">¡Corrija los siguientes errores!</p>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="{{ route('products.store') }}" method="POST" class="p-6">
                    @csrf
                    
                    {{-- SECCIÓN 1: IDENTIFICACIÓN Y CÓDIGOS --}}
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">{{ __('1. Datos de Identificación') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        
                        {{-- Nombre --}}
                        <div>
                            <label for="name" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-tag text-gray-400 mr-2"></i>{{ __('Nombre') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 @error('name') border-red-500 @enderror">
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Código --}}
                        <div>
                            <label for="code" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-barcode text-gray-400 mr-2"></i>{{ __('Código') }}</label>
                            <input type="text" name="code" id="code" value="{{ old('code') }}" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 @error('code') border-red-500 @enderror">
                            @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Modelo --}}
                        <div>
                            <label for="model" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-cogs text-gray-400 mr-2"></i>{{ __('Modelo') }}</label>
                            <input type="text" name="model" id="model" value="{{ old('model') }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Número de serie --}}
                        <div>
                            <label for="serial_number" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-key text-gray-400 mr-2"></i>{{ __('Número de Serie') }}</label>
                            <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Descripción (span 2 columnas) --}}
                        <div class="md:col-span-2">
                            <label for="description" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-align-left text-gray-400 mr-2"></i>{{ __('Descripción') }}</label>
                            <textarea name="description" id="description" rows="3"
                                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    {{-- SECCIÓN 2: CLASIFICACIÓN (Dropdowns) --}}
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">{{ __('2. Clasificación y Aplicación') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        
                        {{-- Fabricante (manufacturer_id) --}}
                        <div>
                            <label for="manufacturer_id" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-industry text-gray-400 mr-2"></i>{{ __('Fabricante') }}</label>
                            <select name="manufacturer_id" id="manufacturer_id"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 @error('manufacturer_id') border-red-500 @enderror">
                                <option value="">-- {{ __('Seleccione un fabricante') }} --</option>
                                @foreach($manufacturers as $manufacturer)
                                    <option value="{{ $manufacturer->id }}" {{ old('manufacturer_id') == $manufacturer->id ? 'selected' : '' }}>
                                        {{ $manufacturer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('manufacturer_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Categoría (category_id) - CLAVE para Subcategoría --}}
                        <div>
                            <label for="category_id" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-list text-gray-400 mr-2"></i>{{ __('Categoría') }}</label>
                            <select name="category_id" id="category_id" x-model="selectedCategory"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 @error('category_id') border-red-500 @enderror">
                                <option value="">-- {{ __('Seleccione una categoría') }} --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        
                        {{-- Especialidad Médica (specialty_id) --}}
                        <div>
                            <label for="specialty_id" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-stethoscope text-gray-400 mr-2"></i>{{ __('Especialidad Aplicación') }}</label>
                            <select name="specialty_id" id="specialty_id"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 @error('specialty_id') border-red-500 @enderror">
                                <option value="">-- {{ __('Seleccione la aplicación clínica') }} --</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" {{ old('specialty_id') == $specialty->id ? 'selected' : '' }}>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('specialty_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Subcategoría (subcategory_id) - FILTRADA POR Alpine.js --}}
                        <div>
                            <label for="subcategory_id" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-list-alt text-gray-400 mr-2"></i>{{ __('Subcategoría') }}</label>
                            <select name="subcategory_id" id="subcategory_id" :disabled="!filteredSubcategories.length"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 @error('subcategory_id') border-red-500 @enderror">
                                <option value="">-- {{ __('Seleccione subcategoría') }} --</option>
                                <template x-for="subcategory in filteredSubcategories" :key="subcategory.id">
                                    <option :value="subcategory.id" 
                                            :selected="subcategory.id == {{ old('subcategory_id', 'null') }}">
                                        <span x-text="subcategory.name"></span>
                                    </option>
                                </template>
                            </select>
                            @error('subcategory_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- SECCIÓN 3: GESTIÓN DE INVENTARIO Y CARACTERÍSTICAS --}}
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">{{ __('3. Inventario y Características') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        
                        {{-- Costo Unitario --}}
                        <div>
                            <label for="unit_cost" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-dollar-sign text-gray-400 mr-2"></i>{{ __('Costo ($)') }}</label>
                            <input type="number" name="unit_cost" id="unit_cost" step="0.01" min="0"
                                   value="{{ old('unit_cost') }}" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Stock Inicial --}}
                        <div>
                            <label for="current_stock" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-boxes text-gray-400 mr-2"></i>{{ __('Stock Inicial') }}</label>
                            <input type="number" name="current_stock" id="current_stock" min="0"
                                   value="{{ old('current_stock', 0) }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 @error('current_stock') border-red-500 @enderror">
                            @error('current_stock')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Stock Mínimo --}}
                        <div>
                            <label for="minimum_stock" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-box text-gray-400 mr-2"></i>{{ __('Stock Mínimo') }}</label>
                            <input type="number" name="minimum_stock" id="minimum_stock" min="0"
                                   value="{{ old('minimum_stock', 1) }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 @error('minimum_stock') border-red-500 @enderror">
                             @error('minimum_stock')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Ubicación --}}
                        <div>
                            <label for="storage_location" class="flex items-center text-sm font-medium text-gray-700 mb-2"><i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>{{ __('Ubicación') }}</label>
                            <input type="text" name="storage_location" id="storage_location"
                                   value="{{ old('storage_location') }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>
                        
                        {{-- Campos de Chequeo (Checkboxes) (span 4 columnas) --}}
                        <div class="md:col-span-4 flex flex-wrap gap-x-8 gap-y-4 mt-2 p-3 bg-gray-50 rounded-lg border">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="rfid_enabled" value="1" class="form-checkbox text-blue-600 rounded" {{ old('rfid_enabled') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm font-medium text-gray-700">{{ __('RFID Habilitado') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_consumable" value="1" class="form-checkbox text-blue-600 rounded" {{ old('is_consumable') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm font-medium text-gray-700">{{ __('Es Consumible') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="requires_sterilization" value="1" class="form-checkbox text-blue-600 rounded" {{ old('requires_sterilization') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm font-medium text-gray-700">{{ __('Requiere Esterilización') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_single_use" value="1" class="form-checkbox text-blue-600 rounded" {{ old('is_single_use') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm font-medium text-gray-700">{{ __('Uso Único') }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- Botón Guardar --}}
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-teal-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all duration-200 transform hover:-translate-y-0.5">
                            <i class="fas fa-save mr-2"></i>
                            {{ __('Guardar Producto') }}
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
            // Inicializa la categoría seleccionada al valor antiguo si existe
            selectedCategory: '{{ old('category_id') }}', 
            
            // Computa la lista de subcategorías filtradas
            get filteredSubcategories() {
                if (!this.selectedCategory) {
                    return [];
                }
                // Filtra el array de todas las subcategorías por el category_id seleccionado
                return allSubcategories.filter(sub => sub.category_id == this.selectedCategory);
            }
        }
    }
</script>
@endpush
</x-app-layout>

