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

                    <form method="POST" action="{{ route('hospitals.update', $hospital) }}" class="p-6 space-y-8">


                    {{-- Sistema de Alertas --}}
                    @if ($errors->any() || session('error') || session('success'))
                        <div class="alert-container space-y-3">
                            {{-- Errores de validación --}}
                            @if ($errors->any())
                                <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-md animate-shake">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-circle text-red-500 text-2xl"></i>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <h3 class="text-sm font-bold text-red-800 mb-2">
                                                <i class="fas fa-triangle-exclamation mr-1"></i>
                                                Se encontraron los siguientes errores:
                                            </h3>
                                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <button type="button" onclick="this.parentElement.parentElement.remove()" 
                                                class="flex-shrink-0 ml-4 text-red-400 hover:text-red-600 transition">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- Error general del sistema --}}
                            @if (session('error'))
                                <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-md">
                                    <div class="flex items-start">
                                        <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                                        </div>
                                        <button type="button" onclick="this.parentElement.parentElement.remove()" 
                                                class="text-red-400 hover:text-red-600 transition">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- Mensaje de éxito (aunque redirige, por si acaso) --}}
                            @if (session('success'))
                                <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow-md">
                                    <div class="flex items-start">
                                        <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                        </div>
                                        <button type="button" onclick="this.parentElement.parentElement.remove()" 
                                                class="text-green-400 hover:text-green-600 transition">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    

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
                                    $currentConfig = $hospital->configs->where('modality->id', $modality->id)->first();
                                    
                                    // Verificar si hay input antiguo
                                    $hasOldInput = old('configs') !== null;
                                    
                                    if ($hasOldInput) {
                                        // Si hay old input, usar exactamente lo que el usuario envió
                                        $isSelected = isset(old('configs')[$modality->id]['selected']);
                                        $currentEntityId = old("configs.{$modality->id}.legal_entity_id");
                                    } else {
                                        // Primera carga: usar valores de BD
                                        $isSelected = $currentConfig ? true : false;
                                        $currentEntityId = $currentConfig ? $currentConfig->legal_entity_id : null;
                                    }
                                @endphp

                                <div class="flex flex-col md:flex-row md:items-center justify-between p-4 bg-white rounded-lg shadow-sm border border-gray-200 gap-4 transition-all hover:border-indigo-300">
                                    <div class="flex items-center w-full md:w-1/3">
                                        <input type="checkbox" 
                                            name="configs[{{ $modality->id }}][selected]" 
                                            id="mod_{{ $modality->id }}"
                                            class="modality-checkbox h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                            {{ $isSelected ? 'checked' : '' }}
                                            value="1">
                                        <label for="mod_{{ $modality->id }}" class="ml-3 font-bold text-gray-800">
                                            {{ $modality->name }}
                                        </label>
                                    </div>

                                    <div class="w-full md:w-2/3">
                                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Entidad Fiscal Asignada</label>
                                        <select id="select_{{ $modality->id }}" 
                                                name="configs[{{ $modality->id }}][legal_entity_id]" 
                                                class="entity-select w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                                            <option value="">-- Seleccionar Empresa Facturadora --</option>
                                            @foreach($legalEntities as $entity)
                                                <option value="{{ $entity->id }}" 
                                                    {{ $currentEntityId == $entity->id ? 'selected' : '' }}>
                                                    {{ $entity->name }} ({{ $entity->rfc }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("configs.{$modality->id}.legal_entity_id")
                                            <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
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
    @push ('scripts')
        <script>
    console.log("Script cargado correctamente");
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const checkboxes = document.querySelectorAll('.modality-checkbox');

        /**
         * Actualiza el estado (habilitado/deshabilitado) del select
         */
        function updateSelectState(checkbox) {
            const modalityId = checkbox.id.split('_')[1];
            const selectElement = document.getElementById('select_' + modalityId);

            if (selectElement) {
                if (checkbox.checked) {
                    selectElement.disabled = false;
                    selectElement.classList.remove('bg-gray-100', 'cursor-not-allowed', 'opacity-50');
                    selectElement.classList.add('bg-white');
                } else {
                    selectElement.disabled = true;
                    selectElement.value = ""; // Limpiar el valor
                    selectElement.classList.add('bg-gray-100', 'cursor-not-allowed', 'opacity-50');
                    selectElement.classList.remove('bg-white');
                }
            }
        }

        /**
         * INICIALIZACIÓN: Deshabilitar todos los selects que NO tengan checkbox marcado
         */
        checkboxes.forEach(cb => {
            updateSelectState(cb);
        });

        /**
         * Listener para cambios en los checkboxes
         */
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () { 
                updateSelectState(this);
            });
        });

        /**
         * Validación antes del envío del formulario
         */
        form.addEventListener('submit', function (e) {
            let hasError = false;
            let errorMessages = [];

            // Verificar si al menos uno está marcado
            const isAnyChecked = Array.from(checkboxes).some(cb => cb.checked);

            if (!isAnyChecked) {
                hasError = true;
                errorMessages.push('Debe seleccionar al menos una modalidad (Seguro o Particular).');
            } else {
                // Validar que los checkboxes marcados tengan un valor en el select
                checkboxes.forEach(cb => {
                    if (cb.checked) {
                        const modalityId = cb.id.split('_')[1];
                        const selectElement = document.getElementById('select_' + modalityId);
                        
                        if (selectElement && !selectElement.value) {
                            hasError = true;
                            const modalityName = cb.nextElementSibling.textContent.trim();
                            errorMessages.push(`La modalidad "${modalityName}" requiere asignar una Entidad Fiscal.`);
                        }
                    }
                });
            }

            // Si hay errores, prevenir el envío
            if (hasError) {
                e.preventDefault();
                e.stopPropagation();

                // Feedback visual
                const configSection = document.querySelector('.bg-indigo-50');
                configSection.classList.add('ring-2', 'ring-red-500', 'ring-offset-2');
                
                // Mostrar mensajes de error
                alert('¡Atención!\n\n' + errorMessages.join('\n'));

                // Scroll hacia la sección
                configSection.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Quitar el borde rojo después de 3 segundos
                setTimeout(() => {
                    configSection.classList.remove('ring-2', 'ring-red-500', 'ring-offset-2');
                }, 3000);

                return false; // Seguridad extra
            }
        });
    });
</script>
    @endpush 
</x-app-layout>