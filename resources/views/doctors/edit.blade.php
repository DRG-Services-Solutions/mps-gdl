<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-edit mr-2 text-indigo-600"></i>
                    {{ __('Editar Doctor') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $doctor->full_name }}</p>
            </div>
            <a href="{{ route('doctors.show', $doctor) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('doctors.update', $doctor) }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Información General -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            <i class="fas fa-info-circle mr-2 text-indigo-600"></i>Información General
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre(s) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="{{ old('first_name', $doctor->first_name) }}"
                                       required
                                       placeholder="Ej: Juan Carlos"
                                       class="w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('first_name') border-red-500 @enderror">
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Apellido -->
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Apellido(s) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="{{ old('last_name', $doctor->last_name) }}"
                                       required
                                       placeholder="Ej: Pérez García"
                                       class="w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('last_name') border-red-500 @enderror">
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Código -->
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                    Código / Identificador
                                </label>
                                <input type="text" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code', $doctor->code) }}"
                                       placeholder="Ej: DR-001, MED-JC..."
                                       class="w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('code') border-red-500 @enderror">
                                @error('code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Código único para identificar al doctor</p>
                            </div>

                            <!-- Especialidad -->
                            <div>
                                <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">
                                    Especialidad
                                </label>
                                <input type="text" 
                                       id="specialty" 
                                       name="specialty" 
                                       value="{{ old('specialty', $doctor->specialty) }}"
                                       placeholder="Ej: Traumatología, Ortopedia..."
                                       class="w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('specialty') border-red-500 @enderror">
                                @error('specialty')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Hospital -->
                            <div>
                                <label for="hospital_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-hospital mr-1 text-indigo-600"></i>
                                    Hospital <span class="text-red-500">*</span>
                                </label>
                                <select id="hospital_id" 
                                        name="hospital_id" 
                                        required
                                        class="w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('hospital_id') border-red-500 @enderror">
                                    <option value="">Seleccionar hospital...</option>
                                    @foreach($hospitals as $hospital)
                                        <option value="{{ $hospital->id }}" {{ old('hospital_id', $doctor->hospital_id) == $hospital->id ? 'selected' : '' }}>
                                            {{ $hospital->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('hospital_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">
                                    Estado
                                </label>
                                <select id="is_active" 
                                        name="is_active" 
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                    <option value="1" {{ old('is_active', $doctor->is_active) == 1 ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active', $doctor->is_active) == 0 ? 'selected' : '' }}>Inactivo</option>
                                </select>
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
                                       value="{{ old('phone', $doctor->phone) }}"
                                       placeholder="Ej: (81) 1234-5678, 8112345678..."
                                       class="w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('phone') border-red-500 @enderror">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Correo Electrónico
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $doctor->email) }}"
                                       placeholder="Ej: doctor@hospital.com"
                                       class="w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('email') border-red-500 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Información Profesional -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            <i class="fas fa-id-card mr-2 text-indigo-600"></i>Información Profesional
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Cédula Profesional -->
                            <div>
                                <label for="license_number" class="block text-sm font-medium text-gray-700 mb-1">
                                    Cédula Profesional
                                </label>
                                <input type="text" 
                                       id="license_number" 
                                       name="license_number" 
                                       value="{{ old('license_number', $doctor->license_number) }}"
                                       placeholder="Ej: 1234567"
                                       class="w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('license_number') border-red-500 @enderror">
                                @error('license_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cédula de Especialidad -->
                            <div>
                                <label for="specialty_license" class="block text-sm font-medium text-gray-700 mb-1">
                                    Cédula de Especialidad
                                </label>
                                <input type="text" 
                                       id="specialty_license" 
                                       name="specialty_license" 
                                       value="{{ old('specialty_license', $doctor->specialty_license) }}"
                                       placeholder="Ej: 7654321"
                                       class="w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('specialty_license') border-red-500 @enderror">
                                @error('specialty_license')
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
                                  placeholder="Información adicional sobre el doctor..."
                                  class="w-full  focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('notes') border-red-500 @enderror">{{ old('notes', $doctor->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('doctors.show', $doctor) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 transition">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Cambios
                        </button>
                    </div>

                </form>
            </div>
            
        </div>
    </div>
</x-app-layout>