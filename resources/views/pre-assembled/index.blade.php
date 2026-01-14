<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-box-open mr-2 text-green-600"></i>
                    {{ __('Paquetes Pre-Armados') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Gestiona los paquetes quirúrgicos pre-armados</p>
            </div>
            <a href="{{ route('pre-assembled.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Paquete
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Estadísticas Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Paquetes -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Paquetes</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $packages->total() }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-box-open text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Disponibles -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Disponibles</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $availableCount }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-check-circle text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <!-- En Cirugía -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">En Cirugía</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $inSurgeryCount }}</p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <i class="fas fa-procedures text-2xl text-yellow-600"></i>
                        </div>
                    </div>
                </div>

                <!-- En Mantenimiento -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Mantenimiento</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $maintenanceCount }}</p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <i class="fas fa-tools text-2xl text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form method="GET" action="{{ route('pre-assembled.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Búsqueda -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i>
                                Buscar
                            </label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Código o nombre del paquete..."
                                   class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                        </div>

                        <!-- Estado -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-filter mr-1"></i>
                                Estado
                            </label>
                            <select name="status" 
                                    class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                                <option value="">Todos</option>
                                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponibles</option>
                                <option value="in_preparation" {{ request('status') === 'in_preparation' ? 'selected' : '' }}>En Preparación</option>
                                <option value="in_surgery" {{ request('status') === 'in_surgery' ? 'selected' : '' }}>En Cirugía</option>
                                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
                            </select>
                        </div>

                        <!-- Check List -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-clipboard-list mr-1"></i>
                                Check List
                            </label>
                            <select name="checklist_id" 
                                    class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                                <option value="">Todos</option>
                                @foreach($checklists as $checklist)
                                    <option value="{{ $checklist->id }}" {{ request('checklist_id') == $checklist->id ? 'selected' : '' }}>
                                        {{ $checklist->surgery_type  }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Botón -->
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full px-4 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-search mr-1"></i>
                                Buscar
                            </button>
                        </div>
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
                                    Paquete
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Check List
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Contenido
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Usos
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($packages as $package)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box-open text-green-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $package->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $package->code }}</div>
                                            @if($package->package_epc)
                                                <div class="text-xs text-indigo-600 font-mono">EPC: {{ substr($package->package_epc, 0, 16) }}...</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-clipboard-list mr-1"></i>
                                        @foreach ($checklists as $checklists)
                                            @if($checklists->id === $package->checklist_id)
                                                @break
                                            @endif
                                        @endforeach
                                        {{ $checklists->surgery_type ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center space-y-1">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                            {{ $package->contents->count() }} items
                                        </span>
                                        @if($package->hasExpiredProducts())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Productos vencidos
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusConfig = [
                                            'available' => ['color' => 'green', 'label' => 'Disponible', 'icon' => 'check-circle'],
                                            'in_preparation' => ['color' => 'yellow', 'label' => 'En Preparación', 'icon' => 'spinner'],
                                            'in_surgery' => ['color' => 'orange', 'label' => 'En Cirugía', 'icon' => 'procedures'],
                                            'maintenance' => ['color' => 'red', 'label' => 'Mantenimiento', 'icon' => 'tools'],
                                        ];
                                        $config = $statusConfig[$package->status] ?? ['color' => 'gray', 'label' => $package->status, 'icon' => 'circle'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                                        <i class="fas fa-{{ $config['icon'] }} mr-1"></i>
                                        {{ $config['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm font-semibold text-gray-900">{{ $package->times_used }}</div>
                                    @if($package->last_used_at)
                                        <div class="text-xs text-gray-500">{{ $package->last_used_at->diffForHumans() }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('pre-assembled.show', $package) }}" 
                                       class="text-indigo-600 hover:text-indigo-900"
                                       title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('pre-assembled.edit', $package) }}" 
                                       class="text-blue-600 hover:text-blue-900"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('pre-assembled.destroy', $package) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('¿Eliminar este paquete? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-box-open text-5xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No hay paquetes pre-armados</p>
                                        <p class="text-sm text-gray-600 mb-4">Comienza creando tu primer paquete</p>
                                        <a href="{{ route('pre-assembled.create') }}" 
                                           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                            <i class="fas fa-plus mr-2"></i>
                                            Crear Paquete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($packages->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $packages->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>