<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-edit mr-2 text-indigo-600"></i>
                    Editar Hospital: {{ $hospital->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Actualiza la información general y configuración fiscal</p>
            </div>
            <a href="{{ route('hospitals.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <form method="POST" action="{{ route('hospitals.update', $hospital) }}" class="p-6 space-y-8">
                    @csrf
                    @method('PUT')

                    <section>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-hospital mr-2 text-indigo-600"></i> Información del Hospital
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre Comercial</label>
                                <input type="text" id="name" name="name" 
                                       value="{{ old('name', $hospital->name) }}" 
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="rfc" class="block text-sm font-medium text-gray-700 mb-1">RFC</label>
                                <input type="text" id="rfc" name="rfc" 
                                       value="{{ old('rfc', $hospital->rfc) }}" 
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
                                @error('rfc') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Estado de Operación</label>
                                <select id="is_active" name="is_active" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                    <option value="1" {{ old('is_active', $hospital->is_active) ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ !old('is_active', $hospital->is_active) ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="bg-indigo-50/50 p-6 rounded-xl border border-indigo-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-indigo-900 flex items-center">
                                <i class="fas fa-file-invoice-dollar mr-2"></i> Configuración Fiscal y Modalidades
                            </h3>
                        </div>

                        <div class="space-y-4">
                            @foreach($modalities as $modality)
                                @php
                                    // Sincronización de datos actuales
                                    $currentConfig = $hospital->configs->where('modality_id', $modality->id)->first();
                                    $isSelected = old("configs.{$modality->id}.selected", $currentConfig ? true : false);
                                @endphp

                                <div class="flex flex-col md:flex-row md:items-center justify-between p-4 bg-white rounded-lg shadow-sm border border-gray-200 gap-4 transition-all hover:border-indigo-300">
                                    <div class="flex items-center w-full md:w-1/3">
                                        <input type="checkbox" 
                                               name="configs[{{ $modality->id }}][selected]" 
                                               id="mod_{{ $modality->id }}"
                                               class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                               {{ $isSelected ? 'checked' : '' }}>
                                        <label for="mod_{{ $modality->id }}" class="ml-3 font-bold text-gray-800">
                                            {{ $modality->name }}
                                        </label>
                                    </div>

                                    <div class="w-full md:w-2/3">
                                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Entidad Fiscal Asignada</label>
                                        <select name="configs[{{ $modality->id }}][legal_entity_id]" 
                                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                                            <option value="">-- Seleccionar Empresa Facturadora --</option>
                                            @foreach($legalEntities as $entity)
                                                @php
                                                    $currentEntityId = $currentConfig ? $currentConfig->legal_entity_id : null;
                                                @endphp
                                                <option value="{{ $entity->id }}" 
                                                    {{ old("configs.{$modality->id}.legal_entity_id", $currentEntityId) == $entity->id ? 'selected' : '' }}>
                                                    {{ $entity->name }} ({{ $entity->rfc }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("configs.{$modality->id}.legal_entity_id")
                                            <p class="text-red-500 text-xs mt-1 italic">Debes asignar una entidad si activas esta modalidad.</p>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-100">
                        <a href="{{ route('hospitals.index') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition shadow-md">
                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>