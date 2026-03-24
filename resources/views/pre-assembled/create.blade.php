{{-- resources/views/pre-assembled/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-green-600"></i>
                    {{ __('Nuevo Paquete Pre-Armado') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Crea un nuevo paquete quirúrgico pre-armado</p>
            </div>
            <a href="{{ route('pre-assembled.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form action="{{ route('pre-assembled.store') }}" method="POST">
                    @csrf

                    <div class="p-6 space-y-6">
                        <!-- Información Básica -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-info-circle mr-2 text-green-600"></i>
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
                                           value="{{ old('code') }}"
                                           placeholder="Ej: PRE-ORTOPEDIA-001"
                                           class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('code') border-red-500 @enderror"
                                           required>
                                    @error('code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Nombre -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name') }}"
                                           placeholder="Ej: Paquete Rodilla Estándar"
                                           class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('name') border-red-500 @enderror"
                                           required>
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Check List Base -->
                                <div class="md:col-span-2">
                                    <label for="surgery_checklist_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Check List Base <span class="text-red-500">*</span>
                                    </label>
                                    <select name="surgery_checklist_id" 
                                            id="surgery_checklist_id"
                                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('surgery_checklist_id') border-red-500 @enderror"
                                            required>
                                        <option value="">Selecciona un check list...</option>
                                        @foreach($checklists as $checklist)
                                            <option value="{{ $checklist->id }}" {{ old('surgery_checklist_id') == $checklist->id ? 'selected' : '' }}>
                                                {{ $checklist->surgery_type }} ({{ $checklist->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('surgery_checklist_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- EPC del Contenedor -->
                                <div class="md:col-span-2">
                                    <label for="package_epc" class="block text-sm font-medium text-gray-700 mb-2">
                                        EPC del Contenedor
                                        <span class="text-xs text-gray-500">(Opcional - se puede asignar después)</span>
                                    </label>
                                    <input type="text" 
                                           name="package_epc" 
                                           id="package_epc" 
                                           value="{{ old('package_epc') }}"
                                           placeholder="Ej: 3034257BF7194E4000001A85"
                                           class="w-full font-mono rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('package_epc') border-red-500 @enderror">
                                    @error('package_epc')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Ubicación -->
                                <div>
                                    <label for="storage_location_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Ubicación de Almacenamiento <span class="text-red-500">*</span>
                                    </label>
                                    <select name="storage_location_id" 
                                            id="storage_location_id"
                                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('storage_location_id') border-red-500 @enderror"
                                            required>
                                        <option value="">Selecciona ubicación...</option>
                                        @foreach($storageLocations as $location)
                                            <option value="{{ $location->id }}" {{ old('storage_location_id') == $location->id ? 'selected' : '' }}>
                                                {{ $location->code }} - {{ $location->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('storage_location_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                

                                <!-- Notas -->
                                <div class="md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Notas Adicionales
                                    </label>
                                    <textarea name="notes" 
                                              id="notes" 
                                              rows="3"
                                              placeholder="Notas adicionales sobre el paquete..."
                                              class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                                    @error('notes')
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
                                        Una vez creado el paquete, podrás agregar productos escaneando sus EPCs individuales o seleccionándolos manualmente.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('pre-assembled.index') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-save mr-1"></i>
                            Crear Paquete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>