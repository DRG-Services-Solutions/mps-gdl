<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                    Nuevo Check List
                </h2>
                <p class="text-sm text-gray-600 mt-1">Crea un nuevo Check List</p>
            </div>
            <a href="{{ route('checklists.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form action="{{ route('checklists.store') }}" method="POST">
                    @csrf

                    <div class="p-6 space-y-6">
                        <!-- Información Básica -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-info-circle mr-2 text-indigo-600"></i>
                                Detalle de Check List
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Código (autogenerado) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Código
                                        <span class="ml-1 inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                            Autogenerado
                                        </span>
                                    </label>
                                    <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-400 italic">
                                        Se generará automáticamente al guardar
                                    </div>
                                </div>

                                <!-- Tipo de Cirugía -->
                                <div>
                                    <label for="surgery_type" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Cirugía <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="surgery_type" 
                                           id="surgery_type" 
                                           value="{{ old('surgery_type') }}"
                                           placeholder="Ej: Ortopedia, Cardiovascular"
                                           class="w-full rounded-lg focus:border-indigo-500 focus:ring-indigo-500 @error('surgery_type') border-red-500 @enderror"
                                           required>
                                    @error('surgery_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-blue-900 mb-2">Siguiente Paso</h4>
                                    <p class="text-sm text-blue-800">
                                        Una vez creado el check list, podrás agregar los productos necesarios y configurar las reglas condicionales por hospital, doctor o modalidad.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('checklists.index') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-save mr-1"></i>
                            Guardar Check List
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>