<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                    Alta de Nuevo Modelo al Catálogo
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Registra un nuevo tipo de instrumental, consola o charola para uso hospitalario.
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('items.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition-all">
                    <i class="fas fa-arrow-left mr-2"></i> Cancelar y Volver
                </a>
            </div>
        </div>
    </x-slot>


    
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <!-- x-data de Alpine para controlar la reactividad del formulario -->
                <form action="{{ route('items.store') }}" method="POST" x-data="{ requiresMaintenance: {{ old('requires_maintenance') ? 'true' : 'false' }} }">
                    @csrf
                    
                    <div class="p-6 sm:p-8 space-y-6">
                        
                        <!-- Sección 1: Datos de Identificación -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2 mb-4">
                                <i class="fas fa-barcode mr-2 text-indigo-600"></i> Identificación del Modelo
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Código SKU -->
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700">Código Interno / SKU <span class="text-red-500">*</span></label>
                                    <input type="text" name="code" id="code" value="{{ old('code') }}" required autofocus
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md font-mono"
                                           placeholder="Ej. STRYK-SHAVER-01">
                                    @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <!-- Fabricante -->
                                <div>
                                    <label for="manufacturer" class="block text-sm font-medium text-gray-700">Fabricante / Marca</label>
                                    <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer') }}"
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                           placeholder="Ej. Stryker, Arthrex, Karl Storz">
                                    @error('manufacturer') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <!-- Nombre -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Modelo <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                           placeholder="Ej. Consola Shaver CORE 2.0">
                                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Clasificación y Clínica -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2 mb-4 mt-8">
                                <i class="fas fa-microscope mr-2 text-indigo-600"></i> Clasificación y Uso Clínico
                            </h3>
                            
                            <div class="grid grid-cols-1 gap-6">
                                <!-- Tipo -->
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">Clasificación del Catálogo <span class="text-red-500">*</span></label>
                                    <select name="type" id="type" required
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="" disabled {{ old('type', $item->type ?? '') ? '' : 'selected' }}>Seleccione una clasificación...</option>
                                        
                                        <optgroup label="Estructuras Contenedoras">
                                            <option value="tower" {{ old('type', $item->type ?? '') == 'tower' ? 'selected' : '' }}>Torre</option>
                                            <option value="kit" {{ old('type', $item->type ?? '') == 'kit' ? 'selected' : '' }}>Kit</option>
                                            <option value="tray" {{ old('type', $item->type ?? '') == 'tray' ? 'selected' : '' }}>Charola</option>
                                            <option value="instrumental_set" {{ old('type', $item->type ?? '') == 'instrumental_set' ? 'selected' : '' }}>Set de Instrumental</option>
                                            <option value="implant_set" {{ old('type', $item->type ?? '') == 'implant_set' ? 'selected' : '' }}>Set de Implantes</option>
                                        </optgroup>
                                        
                                        <optgroup label="Hardware Médico">
                                            <option value="console" {{ old('type', $item->type ?? '') == 'console' ? 'selected' : '' }}>Consola (Shavers, RF)</option>
                                            <option value="equipment" {{ old('type', $item->type ?? '') == 'equipment' ? 'selected' : '' }}>Equipo Mayor / Monitor</option>
                                        </optgroup>
                                        
                                        <optgroup label="Componentes Individuales">
                                            <option value="instrumental" {{ old('type', $item->type ?? '') == 'instrumental' ? 'selected' : '' }}>Instrumento Suelto (Pinzas, Lentes)</option>
                                            <option value="implant" {{ old('type', $item->type ?? '') == 'implant' ? 'selected' : '' }}>Implante (Tornillo, Placa)</option>
                                            <option value="accessory" {{ old('type', $item->type ?? '') == 'accessory' ? 'selected' : '' }}>Accesorio (Cables, Pedales)</option>
                                        </optgroup>
                                    </select>
                                    @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    <p class="mt-1 text-xs text-gray-500">Determina si este modelo podrá contener instrumental (Charolas) o no.</p>
                                </div>

                                <!-- Descripción -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Descripción Clínica</label>
                                    <textarea name="description" id="description" rows="3"
                                              class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                              placeholder="Especificaciones técnicas o detalles clínicos relevantes...">{{ old('description') }}</textarea>
                                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Control Biomédico (Con Alpine.js) -->
                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 mt-8">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="requires_maintenance" id="requires_maintenance" value="1"
                                           x-model="requiresMaintenance"
                                           class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-gray-300 rounded cursor-pointer">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="requires_maintenance" class="font-bold text-gray-900 cursor-pointer">Sujeto a Control Biomédico / Mantenimiento</label>
                                    <p class="text-gray-500">Habilita esta opción si las unidades de este modelo requieren servicio técnico después de cierta cantidad de cirugías.</p>
                                </div>
                            </div>

                            <!-- Campo Condicional -->
                            <div x-show="requiresMaintenance" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 class="mt-4 ml-8 pl-4 border-l-2 border-indigo-200"
                                 style="display: none;">
                                <label for="maintenance_interval_uses" class="block text-sm font-medium text-gray-700">Ciclos máximos antes de bloqueo <span class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm w-full sm:w-1/2">
                                    <input type="number" name="maintenance_interval_uses" id="maintenance_interval_uses" 
                                           value="{{ old('maintenance_interval_uses') }}" min="1"
                                           :required="requiresMaintenance"
                                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-16 sm:text-sm border-gray-300 rounded-md"
                                           placeholder="Ej. 100">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">usos</span>
                                    </div>
                                </div>
                                @error('maintenance_interval_uses') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                <p class="mt-1 text-xs text-gray-500">Al alcanzar este número de cirugías, el equipo físico pasará a estado de Mantenimiento automáticamente.</p>
                            </div>
                        </div>

                    </div>

                    <!-- Footer del Formulario -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3 rounded-b-lg">
                        <a href="{{ route('items.index') }}" class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors flex items-center">
                            <i class="fas fa-save mr-2"></i> Guardar en Catálogo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>