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
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                
                <form action="{{ route('products.store') }}" method="POST" class="p-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Nombre --}}
                        <div>
                            <label for="name" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tag text-gray-400 mr-2"></i>
                                {{ __('Nombre') }}
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   autocomplete="name"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Código --}}
                        <div>
                            <label for="code" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-barcode text-gray-400 mr-2"></i>
                                {{ __('Código') }}
                            </label>
                            <input type="text" name="code" id="code" value="{{ old('code') }}" required
                                   autocomplete="off"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Costo Unitario --}}
                        <div>
                            <label for="unit_cost" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-dollar-sign text-gray-400 mr-2"></i>
                                {{ __('Costo Unitario ($)') }}
                            </label>
                            <input type="number" name="unit_cost" id="unit_cost" step="0.01" min="0"
                                   autocomplete="off"
                                   value="{{ old('unit_cost') }}" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Fabricante --}}
                        <div>
                            <label for="manufacturer" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-industry text-gray-400 mr-2"></i>
                                {{ __('Fabricante') }}
                            </label>
                            <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer') }}"
                                   autocomplete="organization"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Modelo --}}
                        <div>
                            <label for="model" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-cogs text-gray-400 mr-2"></i>
                                {{ __('Modelo') }}
                            </label>
                            <input type="text" name="model" id="model" value="{{ old('model') }}"
                                   autocomplete="off"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Número de serie --}}
                        <div>
                            <label for="serial_number" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-key text-gray-400 mr-2"></i>
                                {{ __('Número de Serie') }}
                            </label>
                            <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}"
                                   autocomplete="off"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Categoría --}}
                        <div>
                            <label for="product_category_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-list text-gray-400 mr-2"></i>
                                {{ __('Categoría') }}
                            </label>
                            <select name="product_category_id" id="product_category_id"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                                <option value="">{{ __('Seleccione') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Especialidad Médica --}}
                        <div>
                            <label for="medical_specialty_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-stethoscope text-gray-400 mr-2"></i>
                                {{ __('Especialidad') }}
                            </label>
                            <select name="medical_specialty_id" id="medical_specialty_id"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                                <option value="">{{ __('Seleccione') }}</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" {{ old('medical_specialty_id') == $specialty->id ? 'selected' : '' }}>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Subcategoría --}}
                        <div>
                            <label for="specialty_subcategory_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-list-alt text-gray-400 mr-2"></i>
                                {{ __('Subcategoría') }}
                            </label>
                            <select name="specialty_subcategory_id" id="specialty_subcategory_id"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                                <option value="">{{ __('Seleccione') }}</option>
                                @foreach($subcategories as $subcategory)
                                    <option value="{{ $subcategory->id }}" {{ old('specialty_subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                        {{ $subcategory->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Stock Inicial --}}
                        <div>
                            <label for="current_stock" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-boxes text-gray-400 mr-2"></i>
                                {{ __('Stock Inicial') }}
                            </label>
                            <input type="number" name="current_stock" id="current_stock" min="0"
                                   value="{{ old('current_stock',0) }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Stock Mínimo --}}
                        <div>
                            <label for="minimum_stock" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-box text-gray-400 mr-2"></i>
                                {{ __('Stock Mínimo') }}
                            </label>
                            <input type="number" name="minimum_stock" id="minimum_stock" min="0"
                                   value="{{ old('minimum_stock',1) }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Ubicación --}}
                        <div class="md:col-span-2">
                            <label for="storage_location" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                {{ __('Ubicación') }}
                            </label>
                            <input type="text" name="storage_location" id="storage_location"
                                   value="{{ old('storage_location') }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>

                        {{-- Descripción --}}
                        <div class="md:col-span-2">
                            <label for="description" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-align-left text-gray-400 mr-2"></i>
                                {{ __('Descripción') }}
                            </label>
                            <textarea name="description" id="description" rows="3"
                                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">{{ old('description') }}</textarea>
                        </div>

                        {{-- Checkboxes --}}
                        <div class="md:col-span-2 flex flex-wrap gap-6 mt-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="rfid_enabled" value="1" class="form-checkbox" {{ old('rfid_enabled') ? 'checked' : '' }}>
                                <span class="ml-2">{{ __('RFID habilitado') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_consumable" value="1" class="form-checkbox" {{ old('is_consumable') ? 'checked' : '' }}>
                                <span class="ml-2">{{ __('Es Consumible') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="requires_sterilization" value="1" class="form-checkbox" {{ old('requires_sterilization') ? 'checked' : '' }}>
                                <span class="ml-2">{{ __('Requiere Esterilización') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_single_use" value="1" class="form-checkbox" {{ old('is_single_use') ? 'checked' : '' }}>
                                <span class="ml-2">{{ __('Uso Único') }}</span>
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

    {{-- FontAwesome desde CDN para evitar errores de CORS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</x-app-layout>
