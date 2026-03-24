<x-app-layout>
    @push('styles')
    <style>
        /* =============================================
         * Tom Select - Fix conflicto con @tailwindcss/forms
         * El plugin forms resetea TODOS los <select> e <input>,
         * lo que rompe el markup interno de Tom Select.
         * ============================================= */

        /* Wrapper principal: ancho completo */
        .ts-wrapper {
            width: 100% !important;
        }

        /* El control (caja visible donde se escribe) */
        .ts-wrapper .ts-control {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important;
            background-image: none !important;
            min-height: 42px !important;
            display: flex !important;
            align-items: center !important;
            cursor: text !important;
        }

        /* Neutralizar el reset de @tailwindcss/forms en el input interno */
        .ts-wrapper .ts-control input[type="text"],
        .ts-wrapper .ts-control > input {
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            background-image: none !important;
            box-shadow: none !important;
            outline: none !important;
            min-height: auto !important;
            width: auto !important;
            flex: 1 1 auto !important;
            appearance: none !important;
            -webkit-appearance: none !important;
        }

        /* Focus state */
        .ts-wrapper.focus .ts-control,
        .ts-wrapper .ts-control:focus-within {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 1px #6366f1 !important;
        }

        /* Dropdown */
        .ts-wrapper .ts-dropdown {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            margin-top: 4px !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1) !important;
            z-index: 9999 !important;
        }

        .ts-wrapper .ts-dropdown .option {
            padding: 8px 12px !important;
        }

        .ts-wrapper .ts-dropdown .active {
            background-color: #eef2ff !important;
            color: #4f46e5 !important;
        }

        /* Ocultar el select original que Tom Select esconde */
        .ts-wrapper + select,
        select.tomselected {
            display: none !important;
        }
    </style>
    @endpush

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
                                            placeholder="Escribe para buscar un check list..."
                                            required>
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
                            </div>

                            <!-- Hospital y Configuración -->
                            <div class="mt-6">
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
                                                placeholder="Escribe para buscar un hospital..."
                                                required>
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
                                            placeholder="Escribe para buscar un doctor..."
                                            required>
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
        // ==========================================
        // TOM SELECT - Check List (búsqueda remota)
        // ==========================================
        new TomSelect('#checklist_id', {
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            placeholder: 'Escribe para buscar un check list...',
            openOnFocus: false,

            shouldLoad: function(query) {
                return query.length > 0;
            },

            load: function(query, callback) {
                fetch(`/api/checklists/select2?search=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => callback(data.results))
                    .catch(() => callback());
            },

            render: {
                option: function(data, escape) {
                    return `<div class="py-2 px-3">${escape(data.text)}</div>`;
                },
                item: function(data, escape) {
                    return `<div>${escape(data.text)}</div>`;
                },
                no_results: function() {
                    return '<div class="no-results" style="padding:10px;text-align:center;color:#6b7280;">No se encontraron check lists</div>';
                },
            },
        });

        // ==========================================
        // TOM SELECT - Hospital (búsqueda remota)
        // ==========================================
        const hospitalSelect = new TomSelect('#hospital_id', {
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            placeholder: 'Escribe para buscar un hospital...',
            openOnFocus: false,

            shouldLoad: function(query) {
                return query.length > 0;
            },

            load: function(query, callback) {
                fetch(`/api/hospitals/select2?search=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => callback(data.results))
                    .catch(() => callback());
            },

            render: {
                option: function(data, escape) {
                    return `<div class="py-2 px-3">${escape(data.text)}</div>`;
                },
                item: function(data, escape) {
                    return `<div>${escape(data.text)}</div>`;
                },
                no_results: function() {
                    return '<div class="no-results" style="padding:10px;text-align:center;color:#6b7280;">No se encontraron hospitales</div>';
                },
            },

            onChange: function(hospitalId) {
                loadHospitalConfigs(hospitalId);
            },
        });

        // ==========================================
        // Cargar configuraciones del hospital
        // ==========================================
        function loadHospitalConfigs(hospitalId) {
            const configSelect = document.getElementById('hospital_modality_config_id');
            
            if (!hospitalId) {
                configSelect.innerHTML = '<option value="">Primero selecciona un hospital...</option>';
                configSelect.disabled = true;
                return;
            }
            
            configSelect.innerHTML = '<option value="">Cargando configuraciones...</option>';
            configSelect.disabled = true;
            
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
                        option.text = `${config.modality.name} - Factura: ${config.legal_entity.name}`;
                        
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
        }

        // ==========================================
        // TOM SELECT - Doctor (búsqueda remota)
        // ==========================================
        new TomSelect('#doctor_id', {
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            placeholder: 'Escribe para buscar un doctor...',
            openOnFocus: false,

            shouldLoad: function(query) {
                return query.length > 0;
            },

            load: function(query, callback) {
                fetch(`/api/doctors/select2?search=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => callback(data.results))
                    .catch(() => callback());
            },

            render: {
                option: function(data, escape) {
                    return `<div class="py-2 px-3">${escape(data.text)}</div>`;
                },
                item: function(data, escape) {
                    return `<div>${escape(data.text)}</div>`;
                },
                no_results: function() {
                    return '<div class="no-results" style="padding:10px;text-align:center;color:#6b7280;">No se encontraron doctores</div>';
                },
            },
        });

        // ==========================================
        // Combinar fecha y hora en datetime
        // ==========================================
        document.querySelector('form').addEventListener('submit', function(e) {
            const date = document.getElementById('surgery_date').value;
            const time = document.getElementById('surgery_time').value;
            
            if (date && time) {
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