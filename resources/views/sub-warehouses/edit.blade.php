<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('sub-warehouses.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fas fa-warehouse mr-2"></i>
                    {{ __('Crear Sub-Almacén Virtual') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Mensajes de error -->
            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Formulario -->
            <div class="bg-white shadow-xl sm:rounded-lg overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <h3 class="text-xl font-bold text-white">
                        <i class="fas fa-info-circle mr-2"></i>
                        Información del Sub-Almacén
                    </h3>
                </div>

                <form action="{{ route('sub-warehouses.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <!-- Razón Social -->
                    <div>
                        <label for="legal_entity_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Razón Social <span class="text-red-500">*</span>
                        </label>
                        <select name="legal_entity_id" 
                                id="legal_entity_id"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('legal_entity_id') border-red-500 @enderror">
                            <option value="">Seleccionar razón social...</option>
                            @foreach($legalEntities as $entity)
                                <option value="{{ $entity->id }}" {{ old('legal_entity_id') == $entity->id ? 'selected' : '' }}>
                                    {{ $entity->name }} - {{ $entity->rfc }}
                                </option>
                            @endforeach
                        </select>
                        @error('legal_entity_id')
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Selecciona la razón social a la que pertenecerá este sub-almacén
                        </p>
                    </div>

                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nombre del Sub-Almacén <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name"
                               value="{{ old('name') }}"
                               required
                               maxlength="255"
                               placeholder="Ej: Instrumentos Rodilla, Consumibles, Stock Principal..."
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Usa un nombre descriptivo y único dentro de la razón social
                        </p>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            Descripción <span class="text-gray-500 text-xs font-normal">(Opcional)</span>
                        </label>
                        <textarea name="description" 
                                  id="description"
                                  rows="4"
                                  maxlength="1000"
                                  placeholder="Describe el propósito o contenido de este sub-almacén..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Máximo 1000 caracteres
                        </p>
                    </div>

                    <!-- Estado Activo -->
                    <div>
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">
                                Sub-almacén activo
                            </span>
                        </label>
                        <p class="mt-2 text-xs text-gray-500 ml-8">
                            <i class="fas fa-toggle-on mr-1"></i>
                            Solo los sub-almacenes activos estarán disponibles para asignar en órdenes de compra
                        </p>
                    </div>

                    <!-- Información adicional -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-2">¿Qué son los sub-almacenes virtuales?</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Son categorías para organizar tu inventario dentro de cada razón social</li>
                                    <li>No representan ubicaciones físicas, son solo para organización virtual</li>
                                    <li>Útiles para separar por especialidad médica, tipo de producto, o cliente</li>
                                    <li>Cada producto puede asignarse a un sub-almacén al recibirlo</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('sub-warehouses.index') }}" 
                           class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-all">
                            <i class="fas fa-save mr-2"></i>
                            Crear Sub-Almacén
                        </button>
                    </div>
                </form>
            </div>

            <!-- Ejemplos de uso -->
            <div class="mt-6 bg-white rounded-lg shadow-md p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    Ejemplos de Sub-Almacenes
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="font-semibold text-gray-700 mb-2">Por Especialidad Médica:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-1">
                            <li>Traumatología</li>
                            <li>Ortopedia</li>
                            <li>Cardiología</li>
                            <li>Cirugía General</li>
                        </ul>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700 mb-2">Por Tipo de Producto:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-1">
                            <li>Instrumentos Quirúrgicos</li>
                            <li>Implantes y Prótesis</li>
                            <li>Consumibles Médicos</li>
                            <li>Material de Curación</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>