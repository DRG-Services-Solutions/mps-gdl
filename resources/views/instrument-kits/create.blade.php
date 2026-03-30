{{-- resources/views/instrument-kits/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i> Nuevo Kit de Instrumentales
                </h2>
                <p class="text-sm text-gray-600 mt-1">Crear una nueva caja o set de instrumentos quirúrgicos</p>
            </div>
            <a href="{{ route('instrument-kits.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form method="POST" action="{{ route('instrument-kits.store') }}">
                    @csrf
                    <div class="p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-box-open mr-2 text-indigo-600"></i> Información del Kit
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre del Kit <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                           placeholder="Ej: Caja Artroscopía #3"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        Número de Serie <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}" required
                                           placeholder="Identificador físico de la caja"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono @error('serial_number') border-red-500 @enderror">
                                    @error('serial_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="expected_count" class="block text-sm font-medium text-gray-700 mb-2">
                                        Piezas Esperadas <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="expected_count" id="expected_count" value="{{ old('expected_count', 1) }}" required min="1"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('expected_count') border-red-500 @enderror">
                                    @error('expected_count')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i> Cuántas piezas debería contener este kit cuando está completo
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="template_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Basado en Plantilla (Opcional)
                                    </label>
                                    <select name="template_id" id="template_id"
                                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">— Sin plantilla —</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                                {{ $template->name }} ({{ $template->surgery_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i> Selecciona una plantilla como referencia de lo que debería contener
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notas</label>
                                    <textarea name="notes" id="notes" rows="3" placeholder="Observaciones adicionales..."
                                              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-600 text-xl flex-shrink-0"></i>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-blue-900 mb-1">Siguiente paso</h4>
                                    <p class="text-sm text-blue-800">
                                        Después de crear el kit podrás asignar instrumentos individuales buscándolos por serial o nombre.
                                        El código del kit se genera automáticamente.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('instrument-kits.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-save mr-1"></i> Crear Kit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>