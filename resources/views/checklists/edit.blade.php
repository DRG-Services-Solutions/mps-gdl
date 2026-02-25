<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-edit mr-2 text-blue-600"></i>
                    {{ __('Editar Check List') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $checklist->code }}</p>
            </div>
            <a href="{{ route('checklists.show', $checklist) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Cancelar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form action="{{ route('checklists.update', $checklist) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="p-6 space-y-6">
                        <!-- Información Básica -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                Información Básica
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Código -->
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                        Código <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="code" 
                                           id="code" 
                                           value="{{ old('code', $checklist->code) }}"
                                           placeholder="Ej: CHK-ORTOPEDIA-001"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('code') border-red-500 @enderror"
                                           required>
                                    @error('code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Tipo de Cirugía -->
                                <div>
                                    <label for="surgery_type" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Cirugía <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="surgery_type" 
                                           id="surgery_type" 
                                           value="{{ old('surgery_type', $checklist->surgery_type) }}"
                                           placeholder="Ej: Ortopedia, Cardiovascular"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('surgery_type') border-red-500 @enderror"
                                           required>
                                    @error('surgery_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Nombre -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre del Check List <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name', $checklist->name) }}"
                                           placeholder="Ej: Check List Cirugía de Cadera"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                                           required>
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Descripción -->
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                        Descripción
                                    </label>
                                    <textarea name="description" 
                                              id="description" 
                                              rows="3"
                                              placeholder="Descripción opcional del check list..."
                                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $checklist->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Estado -->
                                <div class="md:col-span-2">
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                        Estado <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" 
                                            id="status"
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-500 @enderror"
                                            required>
                                        <option value="active" {{ old('status', $checklist->status) === 'active' ? 'selected' : '' }}>Activo</option>
                                        <option value="inactive" {{ old('status', $checklist->status) === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas (Read-only) -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Estadísticas</h4>
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="text-2xl font-bold text-indigo-600">{{ $checklist->items->count() }}</div>
                                    <div class="text-xs text-gray-600">Items</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-purple-600">{{ $checklist->scheduledSurgeries->count() }}</div>
                                    <div class="text-xs text-gray-600">Cirugías</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-green-600">{{ $checklist->preAssembledPackages->count() }}</div>
                                    <div class="text-xs text-gray-600">Paquetes</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('checklists.show', $checklist) }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-1"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>