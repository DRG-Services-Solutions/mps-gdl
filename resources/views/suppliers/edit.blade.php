<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Editar Proveedor') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Actualiza la información del proveedor') }} <span class="font-semibold">{{ $supplier->name }}</span>
                </p>
            </div>
            <a href="{{ route('suppliers.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                {{ __('Volver') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                {{-- Header del Formulario --}}
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-edit text-blue-600 text-2xl"></i>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Información del Proveedor') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Los campos marcados con * son obligatorios') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Formulario --}}
                <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        {{-- Código y Nombre --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Código --}}
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Código') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-hashtag text-gray-400"></i>
                                    </div>
                                    <input type="text" 
                                           name="code" 
                                           id="code" 
                                           value="{{ old('code', $supplier->code) }}"
                                           class="block w-full pl-10 pr-3 py-2.5 border  rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('code') border-red-500 @enderror"
                                           required>
                                </div>
                                @error('code')
                                    <p class="mt-1 text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Nombre --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Nombre del Proveedor') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-building text-gray-400"></i>
                                    </div>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name', $supplier->name) }}"
                                           class="block w-full pl-10 pr-3 py-2.5 border  rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                                           required>
                                </div>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Persona de Contacto y Email --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Persona de Contacto --}}
                            <div>
                                <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Persona de Contacto') }}
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" 
                                           name="contact_person" 
                                           id="contact_person" 
                                           value="{{ old('contact_person', $supplier->contact_person) }}"
                                           class="block w-full pl-10 pr-3 py-2.5 border  rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('contact_person') border-red-500 @enderror">
                                </div>
                                @error('contact_person')
                                    <p class="mt-1 text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Correo Electrónico') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           value="{{ old('email', $supplier->email) }}"
                                           class="block w-full pl-10 pr-3 py-2.5 border  rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror"
                                           required>
                                </div>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Teléfono --}}
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Teléfono') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       name="phone" 
                                       id="phone" 
                                       value="{{ old('phone', $supplier->phone) }}"
                                       class="block w-full pl-10 pr-3 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('phone') border-red-500 @enderror">
                            </div>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- rfc --}}
                        <div>
                            <label for="rfc" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('RFC') }}
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                </div>
                                <input    type="text"
                                          name="rfc" 
                                          id="rfc" 
                                          value="{{ old('rfc', $supplier->rfc) }}"
                                          class="block w-full pl-10 pr-3 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('rfc') border-red-500 @enderror">
                            </div>
                            @error('rfc')
                                <p class="mt-1 text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>


                        {{-- razon social --}}
                        <div>
                            <label for="razon_social" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Razon Social') }}
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                </div>
                                <input    type="text"
                                          name="razon_social" 
                                          id="razon_social" 
                                          value="{{ old('razon_social', $supplier->razon_social) }}"
                                          class="block w-full pl-10 pr-3 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('razon_social') border-red-500 @enderror">
                            </div>
                            @error('razon_social')
                                <p class="mt-1 text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>


                        

                        {{-- Dirección --}}
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Dirección') }}
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                </div>
                                <textarea name="address" 
                                          id="address" 
                                          rows="3"
                                          class="block w-full pl-10 pr-3 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('address') border-red-500 @enderror">{{ old('address', $supplier->address) }}</textarea>
                            </div>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Estado Activo --}}
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active" 
                                   value="1"
                                   {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                {{ __('Proveedor activo') }}
                            </label>
                        </div>

                        {{-- Divider --}}
                        <div class="border-t border-gray-200"></div>

                        {{-- Botones --}}
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('suppliers.index') }}" 
                               class="inline-flex items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition-all duration-200 hover:shadow-md">
                                <i class="fas fa-save mr-2"></i>
                                {{ __('Actualizar Proveedor') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>