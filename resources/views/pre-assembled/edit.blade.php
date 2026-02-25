<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-edit mr-2 text-blue-600"></i>
                    {{ __('Editar Paquete Pre-Armado') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $package->code }}</p>
            </div>
            <a href="{{ route('pre-assembled.show', $package) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Cancelar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form action="{{ route('pre-assembled.update', $package) }}" method="POST">
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
                                           value="{{ old('code', $package->code) }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('code') border-red-500 @enderror"
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
                                           value="{{ old('name', $package->name) }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror"
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
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('surgery_checklist_id') border-red-500 @enderror"
                                            required>
                                        @foreach($checklists as $checklist)
                                            <option value="{{ $checklist->id }}" {{ old('surgery_checklist_id', $package->surgery_checklist_id) == $checklist->id ? 'selected' : '' }}>
                                                {{ $checklist->name }} ({{ $checklist->code }})
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
                                    </label>
                                    <input type="text" 
                                           name="package_epc" 
                                           id="package_epc" 
                                           value="{{ old('package_epc', $package->package_epc) }}"
                                           class="w-full font-mono rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('package_epc') border-red-500 @enderror">
                                    @error('package_epc')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Ubicación -->
                                <div>
                                    <label for="storage_location_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Ubicación <span class="text-red-500">*</span>
                                    </label>
                                    <select name="storage_location_id" 
                                            id="storage_location_id"
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('storage_location_id') border-red-500 @enderror"
                                            required>
                                        @foreach($storageLocations as $location)
                                            <option value="{{ $location->id }}" {{ old('storage_location_id', $package->storage_location_id) == $location->id ? 'selected' : '' }}>
                                                {{ $location->code }} - {{ $location->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('storage_location_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                        Estado <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" 
                                            id="status"
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-500 @enderror"
                                            required>
                                        <option value="available" {{ old('status', $package->status) === 'available' ? 'selected' : '' }}>Disponible</option>
                                        <option value="in_preparation" {{ old('status', $package->status) === 'in_preparation' ? 'selected' : '' }}>En Preparación</option>
                                        <option value="in_surgery" {{ old('status', $package->status) === 'in_surgery' ? 'selected' : '' }}>En Cirugía</option>
                                        <option value="maintenance" {{ old('status', $package->status) === 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Notas -->
                                <div class="md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Notas
                                    </label>
                                    <textarea name="notes" 
                                              id="notes" 
                                              rows="3"
                                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-500 @enderror">{{ old('notes', $package->notes) }}</textarea>
                                    @error('notes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas (Read-only) -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Estadísticas</h4>
                            <div class="grid grid-cols-4 gap-4 text-center">
                                <div>
                                    <div class="text-2xl font-bold text-blue-600">{{ $package->contents->count() }}</div>
                                    <div class="text-xs text-gray-600">Items</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-purple-600">{{ number_format($package->getCompletenessPercentage(), 1) }}%</div>
                                    <div class="text-xs text-gray-600">Completitud</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-indigo-600">{{ $package->times_used }}</div>
                                    <div class="text-xs text-gray-600">Usos</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-green-600">{{ $package->preparations->count() }}</div>
                                    <div class="text-xs text-gray-600">Preparaciones</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('pre-assembled.show', $package) }}" 
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