<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('legal-entities.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ __('Editar Razón Social') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Modifica los datos de la entidad legal') }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                
                <!-- Form Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-briefcase text-indigo-600 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $legalEntity->name }}</h3>
                            <p class="text-sm text-gray-600">{{ __('RFC:') }} {{ $legalEntity->rfc }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('legal-entities.update', $legalEntity) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        
                        <!-- Nombre Corto -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Nombre Corto') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $legalEntity->name) }}"
                                   class="w-full px-4 py-2 border  rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('name') border-red-500 @enderror" 
                                   placeholder="Ej: Entidad A, Sucursal Norte"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">{{ __('Nombre corto para identificación rápida') }}</p>
                        </div>

                        <!-- Razón Social -->
                        <div>
                            <label for="razon_social" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Razón Social') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="razon_social" 
                                   id="razon_social" 
                                   value="{{ old('razon_social', $legalEntity->razon_social) }}"
                                   class="w-full px-4 py-2 border  rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('razon_social') border-red-500 @enderror" 
                                   placeholder="Ej: Empresa S.A. de C.V."
                                   required>
                            @error('razon_social')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">{{ __('Razón social completa como aparece en documentos oficiales') }}</p>
                        </div>

                        <!-- RFC -->
                        <div>
                            <label for="rfc" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('RFC') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="rfc" 
                                   id="rfc" 
                                   value="{{ old('rfc', $legalEntity->rfc) }}"
                                   maxlength="13"
                                   class="w-full px-4 py-2 border  rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 font-mono uppercase @error('rfc') border-red-500 @enderror" 
                                   placeholder="Ej: ABC123456XYZ"
                                   pattern="[A-Z]{3,4}[0-9]{6}[A-Z0-9]{3}"
                                   required>
                            @error('rfc')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">{{ __('13 caracteres (RFC con homoclave)') }}</p>
                        </div>

                        <!-- Dirección -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Dirección') }}
                            </label>
                            <textarea name="address" 
                                      id="address" 
                                      rows="3"
                                      class="w-full px-4 py-2 border  rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('address') border-red-500 @enderror" 
                                      placeholder="Calle, Número, Colonia, Ciudad, Estado, CP">{{ old('address', $legalEntity->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Teléfono y Email -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Teléfono -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Teléfono') }}
                                </label>
                                <input type="tel" 
                                       name="phone" 
                                       id="phone" 
                                       value="{{ old('phone', $legalEntity->phone) }}"
                                       class="w-full px-4 py-2 border  rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('phone') border-red-500 @enderror" 
                                       placeholder="(618) 123-4567">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Email') }}
                                </label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       value="{{ old('email', $legalEntity->email) }}"
                                       class="w-full px-4 py-2 border  rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('email') border-red-500 @enderror" 
                                       placeholder="contacto@empresa.com">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notas -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Notas') }}
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="3"
                                      class="w-full px-4 py-2 border  rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('notes') border-red-500 @enderror" 
                                      placeholder="Información adicional relevante...">{{ old('notes', $legalEntity->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estado Activo -->
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active" 
                                   value="1"
                                   {{ old('is_active', $legalEntity->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                {{ __('Entidad activa') }}
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('legal-entities.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i>
                            {{ __('Actualizar Razón Social') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Convertir RFC a mayúsculas automáticamente
        document.getElementById('rfc').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    </script>
    @endpush
</x-app-layout>