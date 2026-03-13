<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                    {{ __('Agendar Cirugía') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Programa una nueva cirugía</p>
            </div>
            <a href="{{ route('surgeries.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form action="{{ route('surgeries.store') }}" method="POST">
                    @csrf

                    <div class="p-6 space-y-6">
                        <!-- Check List -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-clipboard-list mr-2 text-indigo-600"></i>
                                Tipo de Cirugía
                            </h3>

                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="checklist_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Check List <span class="text-red-500">*</span>
                                    </label>
                                    <select name="checklist_id" 
                                            id="checklist_id"
                                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('checklist_id') border-red-500 @enderror"
                                            required>
                                        <option value="">Selecciona el tipo de cirugía...</option>
                                        @foreach($checklists as $checklist)
                                            <option value="{{ $checklist->id }}" {{ old('checklist_id') == $checklist->id ? 'selected' : '' }}>
                                                {{ $checklist->name }} ({{ $checklist->surgery_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('checklist_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Información -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-user mr-2 text-indigo-600"></i>
                                Información del Paciente
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nombre del Paciente -->
                                <div class="md:col-span-2">
                                    <label for="patient_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre del Paciente <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="patient_name" 
                                           id="patient_name" 
                                           value="{{ old('patient_name') }}"
                                           placeholder="Nombre completo del paciente"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('patient_name') border-red-500 @enderror"
                                           required>
                                    @error('patient_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <br>
                            </div>

                                <!-- Hospital y Configuración -->
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                        <i class="fas fa-hospital mr-2 text-indigo-600"></i>
                                        Hospital y Configuración
                                    </h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Hospital -->
                                        <div>
                                            <label for="hospital_id" class="block text-sm font-medium text-gray-700 mb-2">
                                                Hospital <span class="text-red-500">*</span>
                                            </label>
                                            <select name="hospital_id_temp" 
                                                    id="hospital_id"
                                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                    required>
                                                <option value="">Selecciona el hospital...</option>
                                                @foreach($hospitals as $hospital)
                                                    <option value="{{ $hospital->id }}" {{ old('hospital_id_temp') == $hospital->id ? 'selected' : '' }}>
                                                        {{ $hospital->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Configuración (Modalidad + Legal Entity) -->
                                        <div>
                                            <label for="hospital_modality_config_id" class="block text-sm font-medium text-gray-700 mb-2">
                                                Modalidad y Facturación <span class="text-red-500">*</span>
                                            </label>
                                            <select name="hospital_modality_config_id" 
                                                    id="hospital_modality_config_id"
                                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('hospital_modality_config_id') border-red-500 @enderror"
                                                    disabled
                                                    required>
                                                <option value="">Primero selecciona un hospital...</option>
                                            </select>
                                            @error('hospital_modality_config_id')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                            <p class="mt-1 text-xs text-gray-500">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Define quién factura y bajo qué modalidad
                                            </p>
                                        </div>
                                    </div>
                                

                              
                                
                            </div>
                        </div>

                        <!-- Doctor -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-user-md mr-2 text-indigo-600"></i>
                                Doctor Responsable
                            </h3>

                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="doctor_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Doctor que Realizará la Cirugía <span class="text-red-500">*</span>
                                    </label>
                                    <select name="doctor_id" 
                                            id="doctor_id"
                                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('doctor_id') border-red-500 @enderror"
                                            required>
                                        <option value="">Selecciona el doctor...</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                @if($doctor->middle_name)
                                                    Dr. {{ $doctor->first_name }} {{ $doctor->middle_name }} {{ $doctor->last_name }}
                                                @else
                                                    Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                                                @endif
                                                @if($doctor->specialty)
                                                    - {{ $doctor->specialty }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Fecha y Hora -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-calendar-alt mr-2 text-indigo-600"></i>
                                Fecha y Hora
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Fecha -->
                                <div>
                                    <label for="surgery_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Fecha de la Cirugía <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" 
                                           name="surgery_date" 
                                           id="surgery_date" 
                                           value="{{ old('surgery_date') }}"
                                           min="{{ now()->format('Y-m-d') }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('surgery_date') border-red-500 @enderror"
                                           required>
                                    @error('surgery_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Hora -->
                                <div>
                                    <label for="surgery_time" class="block text-sm font-medium text-gray-700 mb-2">
                                        Hora <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" 
                                           name="surgery_time" 
                                           id="surgery_time" 
                                           value="{{ old('surgery_time') }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('surgery_time') border-red-500 @enderror"
                                           required>
                                    @error('surgery_time')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Notas -->
                                <div class="md:col-span-2">
                                    <label for="surgery_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Notas de la Cirugía
                                    </label>
                                    <textarea name="surgery_notes" 
                                              id="surgery_notes" 
                                              rows="3"
                                              placeholder="Notas adicionales sobre la cirugía..."
                                              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('surgery_notes') border-red-500 @enderror">{{ old('surgery_notes') }}</textarea>
                                    @error('surgery_notes')
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
                                        Una vez agendada la cirugía, podrás iniciar el proceso de preparación seleccionando un paquete pre-armado y completando los productos faltantes.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('surgeries.index') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-save mr-1"></i>
                            Agendar Cirugía
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
<script>
    // Cargar configuraciones cuando se selecciona hospital
    document.getElementById('hospital_id').addEventListener('change', function() {
        const hospitalId = this.value;
        const configSelect = document.getElementById('hospital_modality_config_id');
        
        if (!hospitalId) {
            configSelect.innerHTML = '<option value="">Primero selecciona un hospital...</option>';
            configSelect.disabled = true;
            return;
        }
        
        // Mostrar loading
        configSelect.innerHTML = '<option value="">Cargando configuraciones...</option>';
        configSelect.disabled = true;
        
        // Cargar configuraciones del hospital
        fetch(`/api/hospitals/${hospitalId}/configs`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al cargar configuraciones');
                }
                return response.json();
            })
            .then(configs => {
                configSelect.innerHTML = '<option value="">Selecciona modalidad y facturación...</option>';
                
                if (configs.length === 0) {
                    configSelect.innerHTML = '<option value="">Este hospital no tiene configuraciones disponibles</option>';
                    return;
                }
                
                configs.forEach(config => {
                    const option = document.createElement('option');
                    option.value = config.id;
                    
                    // Formato: "Particular - Factura: Legal Entity 1"
                    option.text = `${config.modality.name} - Factura: ${config.legal_entity.name}`;
                    
                    // Si solo hay una config, auto-seleccionarla
                    if (configs.length === 1) {
                        option.selected = true;
                    }
                    
                    configSelect.add(option);
                });
                
                configSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                configSelect.innerHTML = '<option value="">Error al cargar configuraciones</option>';
                alert('Error al cargar las configuraciones del hospital. Por favor, recarga la página.');
            });
    });
    
    // Combinar fecha y hora en un solo campo datetime
    document.querySelector('form').addEventListener('submit', function(e) {
        const date = document.getElementById('surgery_date').value;
        const time = document.getElementById('surgery_time').value;
        
        if (date && time) {
            // Crear input hidden con datetime combinado
            const datetimeInput = document.createElement('input');
            datetimeInput.type = 'hidden';
            datetimeInput.name = 'surgery_datetime';
            datetimeInput.value = date + ' ' + time;
            this.appendChild(datetimeInput);
        }
    });
</script>
@endpush
</x-app-layout>