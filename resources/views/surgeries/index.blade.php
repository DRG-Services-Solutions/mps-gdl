{{-- resources/views/surgeries/index.blade.php --}}
<x-app-layout>
    @push('styles')
    <style>
        /* Tom Select - Fix conflicto con @tailwindcss/forms */
        .ts-wrapper { width: 100% !important; }
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
        .ts-wrapper .ts-control input[type="text"],
        .ts-wrapper .ts-control > input {
            border: none !important; padding: 0 !important; margin: 0 !important;
            background: transparent !important; background-image: none !important;
            box-shadow: none !important; outline: none !important;
            min-height: auto !important; width: auto !important;
            flex: 1 1 auto !important; appearance: none !important;
        }
        .ts-wrapper.focus .ts-control,
        .ts-wrapper .ts-control:focus-within {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 1px #6366f1 !important;
        }
        .ts-wrapper .ts-dropdown {
            border: 1px solid #d1d5db !important; border-radius: 0.5rem !important;
            margin-top: 4px !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1) !important;
            z-index: 9999 !important;
        }
        .ts-wrapper .ts-dropdown .option { padding: 8px 12px !important; }
        .ts-wrapper .ts-dropdown .active { background-color: #eef2ff !important; color: #4f46e5 !important; }
        .ts-wrapper + select, select.tomselected { display: none !important; }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-calendar-check mr-2 text-indigo-600"></i>
                    {{ __('Cirugías Programadas') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Gestiona las cirugías agendadas y su preparación</p>
            </div>
            <a href="{{ route('surgeries.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-plus mr-2"></i>
                Agendar Cirugía
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Estadísticas Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $surgeries->total() }}</p>
                        </div>
                        <div class="bg-indigo-100 rounded-full p-3">
                            <i class="fas fa-calendar-alt text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Agendadas</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $scheduledCount }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-clock text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">En Preparación</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $inPreparationCount }}</p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <i class="fas fa-spinner text-2xl text-yellow-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Listas</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $readyCount }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-check-circle text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">En Cirugía</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $inSurgeryCount }}</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <i class="fas fa-procedures text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form method="GET" action="{{ route('surgeries.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Búsqueda -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i>
                                Buscar
                            </label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Código, paciente, tipo de cirugía..."
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Doctor (Tom Select con búsqueda remota) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user-md mr-1"></i>
                                Doctor
                            </label>
                            <select name="doctor_id" 
                                    id="doctor_filter"
                                    placeholder="Buscar doctor...">
                                {{-- Precargar opción si hay doctor seleccionado (persiste al recargar) --}}
                                @if($selectedDoctor)
                                    <option value="{{ $selectedDoctor->id }}" selected>
                                        Dr. {{ $selectedDoctor->first_name }} {{ $selectedDoctor->middle_name ? $selectedDoctor->middle_name . ' ' : '' }}{{ $selectedDoctor->last_name }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        <!-- Fecha Desde -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1"></i>
                                Desde
                            </label>
                            <input type="date" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Fecha Hasta -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1"></i>
                                Hasta
                            </label>
                            <input type="date" 
                                   name="date_to" 
                                   value="{{ request('date_to') }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('surgeries.index') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-search mr-1"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Listado -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cirugía</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hospital / Doctor</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha De Cirugía</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($surgeries as $surgery)
                            @php
                                $statusConfig = match($surgery->status) {
                                    'scheduled'      => ['classes' => 'bg-blue-100 text-blue-800', 'label' => 'Agendada', 'icon' => 'fa-calendar'],
                                    'in_preparation' => ['classes' => 'bg-yellow-100 text-yellow-800', 'label' => 'En Preparación', 'icon' => 'fa-spinner fa-spin'],
                                    'prepared'       => ['classes' => 'bg-green-100 text-green-800', 'label' => 'Preparada', 'icon' => 'fa-check-circle'],
                                    'ready'          => ['classes' => 'bg-green-100 text-green-800', 'label' => 'Lista', 'icon' => 'fa-check-circle'],
                                    'in_surgery'     => ['classes' => 'bg-purple-100 text-purple-800', 'label' => 'En Cirugía', 'icon' => 'fa-procedures'],
                                    'completed'      => ['classes' => 'bg-gray-100 text-gray-800', 'label' => 'Completada', 'icon' => 'fa-check'],
                                    'cancelled'      => ['classes' => 'bg-red-100 text-red-800', 'label' => 'Cancelada', 'icon' => 'fa-times-circle'],
                                    default          => ['classes' => 'bg-gray-100 text-gray-800', 'label' => $surgery->status, 'icon' => 'fa-circle'],
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-procedures text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $surgery->checklist->surgery_type ?? 'Sin checklist' }}</div>
                                            <div class="text-xs text-gray-500">{{ $surgery->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $surgery->patient_name }}</div>
                                    <div class="text-xs text-gray-500">
                                        @if($surgery->hospitalModalityConfig && $surgery->hospitalModalityConfig->modality)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-{{ $surgery->hospitalModalityConfig->modality->name === 'Particular' ? 'user' : 'shield-alt' }} mr-1"></i>
                                                {{ $surgery->hospitalModalityConfig->modality->name }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                Sin modalidad
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($surgery->hospitalModalityConfig && $surgery->hospitalModalityConfig->hospital)
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-hospital text-gray-400 mr-1"></i>
                                            {{ $surgery->hospitalModalityConfig->hospital->name }}
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-500 italic">
                                            <i class="fas fa-hospital text-gray-400 mr-1"></i>
                                            Sin hospital asignado
                                        </div>
                                    @endif
                                    
                                    @if($surgery->doctor)
                                        <div class="text-xs text-gray-500">
                                            <i class="fas fa-user-md text-gray-400 mr-1"></i>
                                            Dr. {{ $surgery->doctor->first_name }}
                                            {{ $surgery->doctor->middle_name ? $surgery->doctor->middle_name . ' ' : '' }}{{ $surgery->doctor->last_name }}
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500 italic">
                                            <i class="fas fa-user-md text-gray-400 mr-1"></i>
                                            Sin doctor asignado
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm font-medium text-gray-900">{{ $surgery->surgery_datetime->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $surgery->surgery_datetime->format('H:i') }} Hrs</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusConfig['classes'] }}">
                                            <i class="fas {{ $statusConfig['icon'] }} mr-1"></i>
                                            {{ $statusConfig['label'] }}
                                        </span>

                                        @if($surgery->status === 'in_preparation' && $surgery->preparation)
                                            @php
                                                $progress = $surgery->preparation->cached_progress ?? 0;
                                                $progressColor = $progress == 100 ? 'bg-green-500' : 'bg-yellow-500';
                                            @endphp
                                            <div class="w-full mt-2 max-w-[100px]">
                                                <div class="flex justify-between mb-1">
                                                    <span class="text-[10px] font-medium text-gray-500">Progreso</span>
                                                    <span class="text-[10px] font-medium text-gray-500">{{ $progress }}%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-1">
                                                    <div class="{{ $progressColor }} h-1 rounded-full" style="width: {{ $progress }}%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end items-center space-x-2">
                                        @if($surgery->status === 'scheduled')
                                            <form action="{{ route('surgeries.preparations.start', $surgery) }}" method="POST" class="inline" onsubmit="return confirm('¿Iniciar preparación?')">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700">
                                                    <i class="fas fa-play mr-1"></i> Preparar
                                                </button>
                                            </form>
                                        @elseif($surgery->status === 'in_preparation')
                                            @if($surgery->preparation && !$surgery->preparation->pre_assembled_package_id)
                                                <a href="{{ route('surgeries.preparations.selectPackage', $surgery) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                                    <i class="fas fa-box-open mr-1"></i> Paquete
                                                </a>
                                            @else
                                                <a href="{{ route('surgeries.preparations.picking', $surgery) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-purple-600 hover:bg-purple-700">
                                                    <i class="fas fa-clipboard-check mr-1"></i> Surtir
                                                </a>
                                            @endif
                                        @endif

                                        <div class="flex items-center ml-2 border-l pl-2 space-x-2 border-gray-200">
                                            <a href="{{ route('surgeries.show', $surgery) }}" class="text-gray-400 hover:text-indigo-600" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($surgery->canBeEdited())
                                                <a href="{{ route('surgeries.edit', $surgery) }}" class="text-gray-400 hover:text-blue-600" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($surgery->canBeCancelled())
                                                <form action="{{ route('surgeries.cancel', $surgery) }}" method="POST" class="inline" onsubmit="return confirm('¿Cancelar cirugía?')">
                                                    @csrf
                                                    <button type="submit" class="text-gray-400 hover:text-red-600" title="Cancelar">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-calendar-times text-5xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No hay cirugías programadas</p>
                                        <p class="text-sm text-gray-600 mb-4">Comienza agendando una nueva cirugía</p>
                                        <a href="{{ route('surgeries.create') }}" 
                                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                            <i class="fas fa-plus mr-2"></i>
                                            Agendar Cirugía
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($surgeries->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $surgeries->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Tom Select para filtro de Doctor (búsqueda remota)
        new TomSelect('#doctor_filter', {
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            placeholder: 'Buscar doctor...',
            openOnFocus: false,
            plugins: ['clear_button'],

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
    </script>
    @endpush
</x-app-layout>