{{-- resources/views/surgeries/index.blade.php --}}
<x-app-layout>
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
                <!-- Total Cirugías -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $surgeries->count() }}</p>
                        </div>
                        <div class="bg-indigo-100 rounded-full p-3">
                            <i class="fas fa-calendar-alt text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Agendadas -->
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

                <!-- En Preparación -->
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

                <!-- Listas -->
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

                <!-- En Cirugía -->
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
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <!-- Búsqueda -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i>
                                Buscar
                            </label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Código, paciente..."
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Estado -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-filter mr-1"></i>
                                Estado
                            </label>
                            <select name="status" 
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Agendadas</option>
                                <option value="in_preparation" {{ request('status') === 'in_preparation' ? 'selected' : '' }}>En Preparación</option>
                                <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Listas</option>
                                <option value="in_surgery" {{ request('status') === 'in_surgery' ? 'selected' : '' }}>En Cirugía</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completadas</option>
                            </select>
                        </div>

                        <!-- Hospital -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-hospital mr-1"></i>
                                Hospital
                            </label>
                            <select name="hospital_id" 
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                @foreach($hospitals as $hospital)
                                    <option value="{{ $hospital->id }}" {{ request('hospital_id') == $hospital->id ? 'selected' : '' }}>
                                        {{ $hospital->name }}
                                    </option>
                                @endforeach
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cirugía
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Paciente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hospital / Doctor
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($surgeries as $surgery)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-procedures text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $surgery->checklist->name }}</div>
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
                                            @if($surgery->doctor->middle_name)
                                                Dr. {{ $surgery->doctor->first_name }} {{ $surgery->doctor->middle_name }} {{ $surgery->doctor->last_name }}
                                            @else
                                                Dr. {{ $surgery->doctor->first_name }} {{ $surgery->doctor->last_name }}
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500 italic">
                                            <i class="fas fa-user-md text-gray-400 mr-1"></i>
                                            Sin doctor asignado
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    
                                    <div class="text-xs text-gray-500">
                                        {{ $surgery->surgery_datetime->format('H:i') }} Hrs
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-center">
                                        @php
                                            $statusConfig = [
                                                'scheduled' => ['color' => 'blue', 'label' => 'Agendada', 'icon' => 'calendar'],
                                                'in_preparation' => ['color' => 'yellow', 'label' => 'En Preparación', 'icon' => 'spinner'],
                                                'ready' => ['color' => 'green', 'label' => 'Lista', 'icon' => 'check-circle'],
                                                'in_surgery' => ['color' => 'purple', 'label' => 'En Cirugía', 'icon' => 'procedures'],
                                                'completed' => ['color' => 'gray', 'label' => 'Completada', 'icon' => 'check'],
                                                'cancelled' => ['color' => 'red', 'label' => 'Cancelada', 'icon' => 'times-circle'],
                                            ];
                                            $config = $statusConfig[$surgery->status] ?? ['color' => 'gray', 'label' => $surgery->status, 'icon' => 'circle'];
                                        @endphp
                                        
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                                            <i class="fas fa-{{ $config['icon'] }} mr-1 {{ $surgery->status === 'in_preparation' ? 'fa-spin' : '' }}"></i>
                                            {{ $config['label'] }}
                                        </span>

                                        {{-- Barra de completitud visual si está en preparación --}}
                                        @if($surgery->status === 'in_preparation' && $surgery->preparation)
                                            <div class="w-full mt-2 max-w-[100px]">
                                                <div class="flex justify-between mb-1">
                                                    <span class="text-[10px] font-medium text-gray-500">Progreso</span>
                                                    <span class="text-[10px] font-medium text-gray-500">45%</span> {{-- Aquí va tu lógica de % --}}
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-1">
                                                    <div class="bg-yellow-500 h-1 rounded-full" style="width: 45%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end items-center space-x-2">
                                        {{-- ACCIÓN PRINCIPAL (Botón con texto) --}}
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
                                                <a href="{{ route('surgeries.preparations.compare', $surgery) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-purple-600 hover:bg-purple-700">
                                                    <i class="fas fa-clipboard-check mr-1"></i> Surtir
                                                </a>
                                            @endif
                                        @endif

                                        {{-- ACCIONES SECUNDARIAS (Iconos discretos) --}}
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
</x-app-layout>