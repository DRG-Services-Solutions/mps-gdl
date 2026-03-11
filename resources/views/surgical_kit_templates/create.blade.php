<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center">
                    <a href="{{ route('surgical_kit_templates.index') }}" class="text-gray-500 hover:text-indigo-600 mr-3 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <i class="fas fa-box-open mr-2 text-indigo-600"></i>
                    {{ __('Crear Nueva Receta de Kit') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1 ml-9">Define los detalles generales del nuevo kit quirúrgico</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                
                <form action="{{ route('surgical_kit_templates.store') }}" method="POST" class="p-6 md:p-8">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre del Kit <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-signature text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name') }}"
                                       class="pl-10 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 ring-red-500 @enderror" 
                                       placeholder="Ej. Kit de Traumatología Básica"
                                       required>
                            </div>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                Código de Referencia <span class="text-gray-400 font-normal">(Opcional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-barcode text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       name="code" 
                                       id="code" 
                                       value="{{ old('code') }}"
                                       class="pl-10 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('code') border-red-500 ring-red-500 @enderror" 
                                       placeholder="Ej. KIT-TRAU-001">
                            </div>
                            @error('code')
                                <p class="mt-1 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">
                                Estado Inicial
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-toggle-on text-gray-400"></i>
                                </div>
                                <select name="is_active" 
                                        id="is_active" 
                                        class="pl-10 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Activo (Disponible para usar)</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactivo (Borrador / No disponible)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Checklist al que se asociará el kit -->

                        <div class="md:col-span-2">

                            <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">
                                Checklist Asociado <span class="text-gray-400 font-normal">(Opcional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-clipboard-list text-gray-400"></i>
                                </div>
                                <select name="surgery_type" 
                                        id="surgery_type" 
                                        class="pl-10 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('checklist_id') border-red-500 ring-red-500 @enderror">
                                    <option value="">-- Sin Checklist --</option>
                                    @foreach($checklists as $checklist)
                                        <option value="{{ $checklist->surgery_type }}" {{ old('surgery_type') == $checklist->surgery_type ? 'selected' : '' }}>
                                            {{ $checklist->surgery_type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                        


                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción o Notas <span class="text-gray-400 font-normal">(Opcional)</span>
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="3" 
                                      class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 ring-red-500 @enderror" 
                                      placeholder="Agrega cualquier instrucción especial o detalle sobre este kit...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end space-x-3 border-t border-gray-100 pt-5">
                        <a href="{{ route('surgical_kit_templates.index') }}" 
                           class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
                            <i class="fas fa-save mr-1"></i>
                            Guardar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>