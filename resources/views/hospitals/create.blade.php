<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                    {{ __('Nuevo Hospital') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Registrar un nuevo hospital o cliente</p>
            </div>
            <a href="{{ route('hospitals.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('hospitals.store') }}" class="p-6 space-y-6">
                    @csrf

                    <!-- Información General -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            <i class="fas fa-info-circle mr-2 text-indigo-600"></i>Información General
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre del Hospital <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       required
                                       placeholder="Ej: Hospital General, Clínica San José..."
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
   

                            <div class="md:col-span-2">

                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    RFC <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="rfc" 
                                       name="rfc" 
                                       value="{{ old('rfc') }}"
                                       required
                                       placeholder="ABCD123456A11"
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('rfc') border-red-500 @enderror">
                                @error('rfc')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>


                        </div>
                    </div>

                    <!-- Fiscal -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            <i class="fas fa-address-book mr-2 text-indigo-600"></i>Informacion Fiscal
                        </h3>
                        <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('hospitals.store') }}" class="p-6 space-y-6">
                    @csrf

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            <i class="fas fa-file-invoice-dollar mr-2 text-indigo-600"></i>Configuración de Facturación
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">Selecciona las modalidades bajo las que operará este hospital y qué entidad emitirá la factura.</p>

                        <div class="space-y-4">
                            @foreach($modalities as $modality)
                                <div class="flex flex-col md:flex-row md:items-center justify-between p-3 bg-white rounded-lg shadow-sm border border-gray-100 gap-4">
                                    <div class="flex items-center w-full md:w-1/3">
                                        <input type="checkbox" 
                                               name="configs[{{ $modality->id }}][selected]" 
                                               id="mod_{{ $modality->id }}"
                                               class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                               {{ old("configs.{$modality->id}.selected") ? 'checked' : '' }}>
                                        <label for="mod_{{ $modality->id }}" class="ml-3 font-medium text-gray-700">
                                            {{ $modality->name }}
                                        </label>
                                    </div>

                                    <div class="w-full md:w-2/3">
                                        <label class="block text-xs font-medium text-gray-500 mb-1 uppercase">Entidad que Factura</label>
                                        <select name="configs[{{ $modality->id }}][legal_entity_id]" 
                                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                                            <option value="">-- Seleccionar Entidad --</option>
                                            @foreach($legalEntities as $entity)
                                                <option value="{{ $entity->id }}" 
                                                    {{ old("configs.{$modality->id}.legal_entity_id") == $entity->id ? 'selected' : '' }}>
                                                    {{ $entity->name }} ({{ $entity->rfc }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("configs.{$modality->id}.legal_entity_id")
                                            <p class="mt-1 text-xs text-red-600">La entidad es obligatoria si activas esta modalidad.</p>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('hospitals.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 transition">
                            <i class="fas fa-save mr-2"></i>
                            Crear Hospital
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>                   
</div>
                        


                    
                    

                    

                </form>
            </div>
            
        </div>
    </div>
</x-app-layout>