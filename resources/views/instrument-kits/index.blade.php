{{-- resources/views/instrument-kits/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-box-open mr-2 text-indigo-600"></i>
                    {{ __('Kits de Instrumentales') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Cajas y sets de instrumentos quirúrgicos</p>
            </div>
            <a href="{{ route('instrument-kits.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-plus mr-2"></i> Nuevo Kit
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <p class="text-sm font-medium text-gray-600">Total Kits</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalCount }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <p class="text-sm font-medium text-gray-600">Disponibles</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $availableCount }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                    <p class="text-sm font-medium text-gray-600">Incompletos</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $incompleteCount }}</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form method="GET" action="{{ route('instrument-kits.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-search mr-1"></i> Buscar</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre, código, serial..."
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-filter mr-1"></i> Estado</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponible</option>
                                <option value="incomplete" {{ request('status') === 'incomplete' ? 'selected' : '' }}>Incompleto</option>
                                <option value="in_surgery" {{ request('status') === 'in_surgery' ? 'selected' : '' }}>En Cirugía</option>
                                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('instrument-kits.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-times mr-1"></i> Limpiar
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-search mr-1"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plantilla</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Piezas</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($kits as $kit)
                            @php
                                $statusConfig = match($kit->status) {
                                    'available'   => ['classes' => 'bg-green-100 text-green-800', 'label' => 'Disponible', 'icon' => 'fa-check-circle'],
                                    'incomplete'  => ['classes' => 'bg-yellow-100 text-yellow-800', 'label' => 'Incompleto', 'icon' => 'fa-exclamation-triangle'],
                                    'in_surgery'  => ['classes' => 'bg-purple-100 text-purple-800', 'label' => 'En Cirugía', 'icon' => 'fa-procedures'],
                                    'maintenance' => ['classes' => 'bg-yellow-100 text-yellow-800', 'label' => 'Mantenimiento', 'icon' => 'fa-wrench'],
                                    'retired'     => ['classes' => 'bg-gray-100 text-gray-800', 'label' => 'Retirado', 'icon' => 'fa-ban'],
                                    default       => ['classes' => 'bg-gray-100 text-gray-800', 'label' => $kit->status, 'icon' => 'fa-circle'],
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box-open text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $kit->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                <span class="font-mono">{{ $kit->code }}</span>
                                                <span class="mx-1">·</span>
                                                S/N: {{ $kit->serial_number }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($kit->template)
                                        <span class="text-sm text-gray-700">{{ $kit->template->name }}</span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Sin plantilla</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-bold {{ $kit->instruments_count >= $kit->expected_count ? 'text-green-700' : 'text-yellow-700' }}">
                                            {{ $kit->instruments_count }} / {{ $kit->expected_count }}
                                        </span>
                                        <div class="w-16 bg-gray-200 rounded-full h-1.5 mt-1">
                                            @php
                                                $pct = $kit->expected_count > 0 ? min(100, ($kit->instruments_count / $kit->expected_count) * 100) : 0;
                                                $barColor = $pct >= 100 ? 'bg-green-500' : 'bg-yellow-500';
                                            @endphp
                                            <div class="{{ $barColor }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusConfig['classes'] }}">
                                        <i class="fas {{ $statusConfig['icon'] }} mr-1"></i>
                                        {{ $statusConfig['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end items-center space-x-2">
                                        <a href="{{ route('instrument-kits.show', $kit) }}" class="inline-flex items-center px-3 py-1 text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                            <i class="fas fa-eye mr-1"></i> Ver
                                        </a>
                                        <a href="{{ route('instrument-kits.edit', $kit) }}" class="text-gray-400 hover:text-blue-600" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-gray-400">
                                        <i class="fas fa-box-open text-5xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No hay kits registrados</p>
                                        <a href="{{ route('instrument-kits.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg">
                                            <i class="fas fa-plus mr-2"></i> Nuevo Kit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($kits->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">{{ $kits->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>