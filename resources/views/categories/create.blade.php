<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 leading-tight">
            {{ __('Crear Nueva Categoría') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Detalles de la Categoría') }}</h3>
                    
                    {{-- Formulario de Creación --}}
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        
                        {{-- Campo Nombre --}}
                        <div class="mb-5">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Nombre de la Categoría') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                   value="{{ old('name') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                                   placeholder="Ej: Consumibles Quirúrgicos">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Campo Descripción --}}
                        <div class="mb-5">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Descripción (Opcional)') }}
                            </label>
                            <textarea name="description" id="description" rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                                      placeholder="Detalles sobre qué tipo de productos abarca esta clasificación.">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex justify-end pt-4 border-t border-gray-100">
                            <a href="{{ route('categories.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors mr-3">
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-save w-4 h-4 mr-2"></i>
                                {{ __('Guardar Categoría') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>