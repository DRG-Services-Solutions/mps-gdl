{{-- resources/views/hospitals/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                    {{ __('Nuevo Hospital / Cliente') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Registrar un nuevo hospital o cliente con su configuración fiscal</p>
            </div>
            <a href="{{ route('hospitals.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form method="POST" action="{{ route('hospitals.store') }}">
                    @csrf

                    <div class="p-6 space-y-6">

                        <!-- Información General -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-hospital mr-2 text-indigo-600"></i>
                                Información General
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nombre -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre del Hospital / Cliente <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}"
                                           required
                                           placeholder="Ej: Hospital General, Clínica San José..."
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- RFC -->
                                <div class="md:col-span-2">
                                    <label for="rfc" class="block text-sm font-medium text-gray-700 mb-2">
                                        RFC <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="rfc" 
                                           name="rfc" 
                                           value="{{ old('rfc') }}"
                                           required
                                           placeholder="ABCD123456A11"
                                           maxlength="13"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono uppercase @error('rfc') border-red-500 @enderror">
                                    @error('rfc')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        12 caracteres para personas morales, 13 para personas físicas
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de Facturación -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-file-invoice-dollar mr-2 text-indigo-600"></i>
                                Configuración de Facturación
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Selecciona las modalidades bajo las que operará este hospital y qué entidad emitirá la factura.
                            </p>

                            <div class="space-y-3">
                                @foreach($modalities as $modality)
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-indigo-200 transition-colors">
                                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                            <!-- Checkbox de modalidad -->
                                            <div class="flex items-center md:w-1/3">
                                                <input type="checkbox" 
                                                       name="configs[{{ $modality->id }}][selected]" 
                                                       id="mod_{{ $modality->id }}"
                                                       value="1"
                                                       class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                                       {{ old("configs.{$modality->id}.selected") ? 'checked' : '' }}>
                                                <label for="mod_{{ $modality->id }}" class="ml-3">
                                                    <span class="font-semibold text-gray-900">{{ $modality->name }}</span>
                                                </label>
                                            </div>

                                            <!-- Selector de entidad -->
                                            <div class="md:w-2/3">
                                                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider">
                                                    Entidad que Factura
                                                </label>
                                                <select name="configs[{{ $modality->id }}][legal_entity_id]" 
                                                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm @error("configs.{$modality->id}.legal_entity_id") border-red-500 @enderror">
                                                    <option value="">— Seleccionar Entidad —</option>
                                                    @foreach($legalEntities as $entity)
                                                        <option value="{{ $entity->id }}" 
                                                            {{ old("configs.{$modality->id}.legal_entity_id") == $entity->id ? 'selected' : '' }}>
                                                            {{ $entity->name }} ({{ $entity->rfc }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("configs.{$modality->id}.legal_entity_id")
                                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-blue-900 mb-1">Configuración Fiscal</h4>
                                    <p class="text-sm text-blue-800">
                                        La modalidad define cómo opera el hospital (Particular, Seguro, etc.) y la entidad que factura es la razón social que emitirá los comprobantes fiscales.
                                        Puedes configurar múltiples modalidades por hospital.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('hospitals.index') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-save mr-1"></i>
                            Crear Hospital / Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>