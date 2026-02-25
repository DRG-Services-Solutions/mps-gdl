<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Toma de Inventarios') }}
            </h2>
            <a href="{{ route('inventory-counts.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Toma de Inventario
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Alertas --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                        <button @click="show = false" class="text-green-400 hover:text-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Estadísticas Rápidas --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-gray-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Borradores</p>
                            <p class="text-2xl font-bold text-gray-700">{{ $stats['draft'] }}</p>
                        </div>
                        <div class="bg-gray-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">En Progreso</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $stats['in_progress'] }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Pendientes Revisión</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_review'] }}</p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Aprobados (Mes)</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['approved_this_month'] }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tarjeta Principal --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                {{-- Filtros --}}
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <form method="GET" action="{{ route('inventory-counts.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="block w-full rounded-lg border-gray-300 text-sm" 
                                       placeholder="Número de inventario...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="status" class="block w-full rounded-lg border-gray-300 text-sm">
                                    <option value="">Todos</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                                    <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>Pendiente Revisión</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprobado</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select name="type" class="block w-full rounded-lg border-gray-300 text-sm">
                                    <option value="">Todos</option>
                                    <option value="full" {{ request('type') == 'full' ? 'selected' : '' }}>Completo</option>
                                    <option value="partial" {{ request('type') == 'partial' ? 'selected' : '' }}>Parcial</option>
                                    <option value="cyclic" {{ request('type') == 'cyclic' ? 'selected' : '' }}>Cíclico</option>
                                    <option value="spot_check" {{ request('type') == 'spot_check' ? 'selected' : '' }}>Verificación</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Razón Social</label>
                                <select name="legal_entity_id" class="block w-full rounded-lg border-gray-300 text-sm">
                                    <option value="">Todas</option>
                                    @foreach($legalEntities as $entity)
                                        <option value="{{ $entity->id }}" {{ request('legal_entity_id') == $entity->id ? 'selected' : '' }}>
                                            {{ $entity->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit" 
                                        class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium rounded-lg">
                                    Filtrar
                                </button>
                                @if(request()->hasAny(['search', 'status', 'type', 'legal_entity_id']))
                                    <a href="{{ route('inventory-counts.index') }}" 
                                       class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm rounded-lg">
                                        Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Tabla --}}
                <div class="overflow-x-auto">
                    @if($inventoryCounts->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Número</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Razones Sociales</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ubicación</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Precisión</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Creado</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($inventoryCounts as $count)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('inventory-counts.show', $count) }}" 
                                               class="text-blue-600 hover:text-blue-800 font-semibold">
                                                {{ $count->count_number }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $count->type_label }}</span>
                                            <div class="text-xs text-gray-500">{{ $count->method_label }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ $count->legal_entities_names }}
                                            </div>
                                            @if($count->legalEntities->count() > 2)
                                                <div class="text-xs text-gray-500">
                                                    {{ $count->legalEntities->count() }} razones sociales
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $count->location_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                                {{ $count->status_color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}
                                                {{ $count->status_color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $count->status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $count->status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $count->status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ $count->status_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($count->accuracy_percentage !== null)
                                                <span class="text-sm font-semibold 
                                                    {{ $count->accuracy_percentage >= 95 ? 'text-green-600' : '' }}
                                                    {{ $count->accuracy_percentage >= 80 && $count->accuracy_percentage < 95 ? 'text-yellow-600' : '' }}
                                                    {{ $count->accuracy_percentage < 80 ? 'text-red-600' : '' }}">
                                                    {{ number_format($count->accuracy_percentage, 1) }}%
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $count->created_at->format('d/m/Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $count->createdBy->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="{{ route('inventory-counts.show', $count) }}" 
                                                   class="text-blue-600 hover:text-blue-800" title="Ver">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                
                                                @if(in_array($count->status, ['draft', 'in_progress']))
                                                    <a href="{{ route('inventory-counts.count', $count) }}" 
                                                       class="text-green-600 hover:text-green-800" title="Continuar conteo">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                        </svg>
                                                    </a>
                                                @endif

                                                @if($count->status === 'pending_review')
                                                    <a href="{{ route('inventory-counts.review', $count) }}" 
                                                       class="text-yellow-600 hover:text-yellow-800" title="Revisar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay tomas de inventario</h3>
                            <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva toma de inventario.</p>
                            <div class="mt-6">
                                <a href="{{ route('inventory-counts.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Nueva Toma de Inventario
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Paginación --}}
                @if($inventoryCounts->count() > 0)
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $inventoryCounts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
