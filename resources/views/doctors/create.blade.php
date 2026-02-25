<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                    Nuevo Doctor
                </h2>
                <p class="text-sm text-gray-600 mt-1">Registrar un nuevo doctor o cirujano</p>
            </div>
            <a href="{{ route('doctors.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('doctors.store') }}" class="p-6 space-y-6">
                    @csrf

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
                                       value="{{ old('first_name') }}"
                                       required
                                       placeholder="Ej: Juan Carlos"
                                       class="w-full  focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('first_name') border-red-500 @enderror">
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Apellido -->
                            <div>
                                <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Segundo Nombre o Primer Apellido <span class="text-red-500">(Opcional)</span>
                                </label>
                                <input type="text" 
                                       id="middle_name" 
                                       name="middle_name" 
                                       value="{{ old('middle_name') }}"
                                       
                                       placeholder="Ej: Juan Carlos"
                                       class="w-full  focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('middle_name') border-red-500 @enderror">
                                @error('middle_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Apellido -->
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Segundo Apellido(s) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="{{ old('last_name') }}"
                                       required
                                       placeholder="Ej: Pérez García"
                                       class="w-full  focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('last_name') border-red-500 @enderror">
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Teléfono
                                </label>
                                <input type="text" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}"
                                       placeholder="Ej: (81) 1234-5678"
                                       class="w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('phone') border-red-500 @enderror">
                                @error('phone')
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
                                    <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactivo</option>
                                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Activo</option>
                                </select>
                            </div>

                        </div>
                    </div>

                <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('doctors.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 transition">
                            <i class="fas fa-save mr-2"></i>
                            Crear Doctor
                        </button>
                    </div>

                </form>
            </div>
            
        </div>
    </div>
</x-app-layout>