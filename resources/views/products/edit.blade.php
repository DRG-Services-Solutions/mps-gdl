<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Editar Producto') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Modifica los detalles del producto seleccionado.') }}
                </p>
            </div>
            <a href="{{ route('products.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
                {{ __('Volver al Listado') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                
                <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-cyan-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-box-open w-5 h-5 text-teal-600 mr-3"></i>
                        {{ __('Editar Información del Producto') }}
                    </h3>
                </div>

                <form action="{{ route('products.update', $product->id) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Nombre --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Nombre del Producto') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Código --}}
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">{{ __('Código/Referencia') }}</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $product->code) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('code') border-red-500 @enderror">
                            @error('code')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Costo Unitario --}}
                        <div>
                            <label for="unit_cost" class="block text-sm font-medium text-gray-700">{{ __('Costo Unitario ($)') }}</label>
                            <input type="number" step="0.01" min="0" name="unit_cost" id="unit_cost" value="{{ old('unit_cost', $product->unit_cost) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('unit_cost') border-red-500 @enderror">
                            @error('unit_cost')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fabricante --}}
                        <div>
                            <label for="manufacturer" class="block text-sm font-medium text-gray-700">{{ __('Fabricante') }}</label>
                            <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer', $product->manufacturer) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        </div>

                        {{-- Categoría --}}
                        <div>
                            <label for="product_category_id" class="block text-sm font-medium text-gray-700">{{ __('Categoría del Producto') }}</label>
                            <select name="product_category_id" id="product_category_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('product_category_id') border-red-500 @enderror">
                                <option value="">{{ __('Seleccione una categoría') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('product_category_id', $product->product_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Especialidad Médica --}}
                        <div>
                            <label for="medical_specialty_id" class="block text-sm font-medium text-gray-700">{{ __('Especialidad Médica') }}</label>
                            <select name="medical_specialty_id" id="medical_specialty_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                <option value="">{{ __('Sin especialidad') }}</option>
                                @foreach ($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" {{ old('medical_specialty_id', $product->medical_specialty_id) == $specialty->id ? 'selected' : '' }}>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Subcategoría --}}
                        <div>
                            <label for="specialty_subcategory_id" class="block text-sm font-medium text-gray-700">{{ __('Subcategoría') }}</label>
                            <select name="specialty_subcategory_id" id="specialty_subcategory_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                <option value="">{{ __('Sin subcategoría') }}</option>
                                @foreach ($subcategories as $subcategory)
                                    <option value="{{ $subcategory->id }}" {{ old('specialty_subcategory_id', $product->specialty_subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                        {{ $subcategory->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Stock Actual --}}
                        <div>
                            <label for="current_stock" class="block text-sm font-medium text-gray-700">{{ __('Stock Actual') }}</label>
                            <input type="number" min="0" name="current_stock" id="current_stock" value="{{ old('current_stock', $product->current_stock) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        </div>

                        {{-- Stock Mínimo --}}
                        <div>
                            <label for="minimum_stock" class="block text-sm font-medium text-gray-700">{{ __('Stock Mínimo') }}</label>
                            <input type="number" min="0" name="minimum_stock" id="minimum_stock" value="{{ old('minimum_stock', $product->minimum_stock) }}" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        </div>

                        {{-- Ubicación de Almacenamiento --}}
                        <div class="md:col-span-2">
                            <label for="storage_location" class="block text-sm font-medium text-gray-700">{{ __('Ubicación de Almacenamiento') }}</label>
                            <input type="text" name="storage_location" id="storage_location" value="{{ old('storage_location', $product->storage_location) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        </div>

                        {{-- RFID --}}
                        <div class="md:col-span-2">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="rfid_enabled" name="rfid_enabled" type="checkbox" value="1" {{ old('rfid_enabled', $product->rfid_enabled) ? 'checked' : '' }}
                                        class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="rfid_enabled" class="font-medium text-gray-700">{{ __('Rastreo RFID habilitado') }}</label>
                                </div>
                            </div>
                        </div>

                        {{-- Consumible --}}
                        <div class="md:col-span-2">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_consumable" name="is_consumable" type="checkbox" value="1" {{ old('is_consumable', $product->is_consumable) ? 'checked' : '' }}
                                        class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_consumable" class="font-medium text-gray-700">{{ __('Es Consumible') }}</label>
                                </div>
                            </div>
                        </div>

                        {{-- Uso Único --}}
                        <div class="md:col-span-2">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_single_use" name="is_single_use" type="checkbox" value="1" {{ old('is_single_use', $product->is_single_use) ? 'checked' : '' }}
                                        class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_single_use" class="font-medium text-gray-700">{{ __('De un solo uso') }}</label>
                                </div>
                            </div>
                        </div>

                        {{-- Requiere Esterilización --}}
                        <div class="md:col-span-2">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="requires_sterilization" name="requires_sterilization" type="checkbox" value="1" {{ old('requires_sterilization', $product->requires_sterilization) ? 'checked' : '' }}
                                        class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="requires_sterilization" class="font-medium text-gray-700">{{ __('Requiere Esterilización') }}</label>
                                </div>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="md:col-span-2">
                            <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Estado del Producto') }}</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                @foreach (['active' => 'Activo', 'inactive' => 'Inactivo', 'maintenance' => 'Mantenimiento', 'retired' => 'Retirado'] as $key => $label)
                                    <option value="{{ $key }}" {{ old('status', $product->status) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="mt-8 pt-4 border-t border-gray-200">
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors duration-200 transform hover:scale-[1.01]">
                            {{ __('Actualizar Producto') }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</x-app-layout>
