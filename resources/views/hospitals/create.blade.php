<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                    {{ __('Nuevo Hospital') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Registrar un nuevo hospital o cliente</p>
            </div>
            <a href="{{ route('hospitals.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('hospitals.store') }}" class="p-6 space-y-6">
                    @csrf

                    <!-- Información General -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            <i class="fas fa-info-circle mr-2 text-indigo-600"></i>Información General
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre del Hospital <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       required
                                       placeholder="Ej: Hospital General, Clínica San José..."
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="md:col-span-2">

                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Razon Social <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="razon_social" 
                                       name="razon_social" 
                                       value="{{ old('razon_social') }}"
                                       required
                                       placeholder="Hospital del Centro S.A. de C.V. "
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('narazon_social') border-red-500 @enderror">
                                @error('razon_social')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">

                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    RFC <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="rfc" 
                                       name="rfc" 
                                       value="{{ old('rfc') }}"
                                       required
                                       placeholder="ABCD123456A11"
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('rfc') border-red-500 @enderror">
                                @error('rfc')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>


                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            <i class="fas fa-address-book mr-2 text-indigo-600"></i>Información de Contacto
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            

                            <!-- Teléfono -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Teléfono
                                </label>
                                <input type="text" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}"
                                       placeholder="Ej: (81) 1234-5678, 8112345678..."
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('phone') border-red-500 @enderror">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="md:col-span-2">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Correo Electrónico
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}"
                                       placeholder="Ej: contacto@hospital.com"
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('email') border-red-500 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>Dirección
                        </h3>

                        <div class="space-y-4">
                            <!-- Dirección -->
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                    Dirección Completa
                                </label>
                                <textarea id="address" 
                                          name="address" 
                                          rows="2"
                                          placeholder="Calle, número, colonia..."
                                          class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            
                        </div>
                    </div>

                    <!-- Notas -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-sticky-note mr-1 text-indigo-600"></i>
                            Notas / Observaciones
                        </label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="3"
                                  placeholder="Información adicional sobre el hospital..."
                                  class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('hospitals.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 transition">
                            <i class="fas fa-save mr-2"></i>
                            Crear Hospital
                        </button>
                    </div>

                </form>
            </div>
            
        </div>
    </div>
</x-app-layout>