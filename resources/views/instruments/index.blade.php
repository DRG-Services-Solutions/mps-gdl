{{-- resources/views/instruments/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-tools mr-2 text-indigo-600"></i>
                    {{ __('Instrumentales') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Gestión de instrumentos quirúrgicos, equipos y sus seriales</p>
            </div>
            <a href="{{ route('instruments.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-plus mr-2"></i> Nuevo Instrumento
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <p class="text-sm font-medium text-gray-600">Total</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalCount }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <p class="text-sm font-medium text-gray-600">Disponibles</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $availableCount }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <p class="text-sm font-medium text-gray-600">En Kit</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $inKitCount }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                    <p class="text-sm font-medium text-gray-600">Mantenimiento</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $maintenanceCount }}</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form method="GET" action="{{ route('instruments.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-search mr-1"></i> Buscar</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Serial, nombre, código..."
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-layer-group mr-1"></i> Categoría</label>
                            <select name="category_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todas</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-filter mr-1"></i> Estado</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponible</option>
                                <option value="in_kit" {{ request('status') === 'in_kit' ? 'selected' : '' }}>En Kit</option>
                                <option value="in_surgery" {{ request('status') === 'in_surgery' ? 'selected' : '' }}>En Cirugía</option>
                                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
                                <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Extraviado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-box mr-1"></i> Asignación</label>
                            <select name="assignment" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                <option value="in_kit" {{ request('assignment') === 'in_kit' ? 'selected' : '' }}>En un Kit</option>
                                <option value="loose" {{ request('assignment') === 'loose' ? 'selected' : '' }}>Sueltos</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('instruments.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instrumento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Kit</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Condición</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($instruments as $instrument)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-tools text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $instrument->name }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $instrument->serial_number }}</div>
                                            @if($instrument->depends_on_id)
                                                <div class="text-xs text-blue-500 mt-0.5">
                                                    <i class="fas fa-link mr-1"></i> Depende de: {{ $instrument->dependsOn->serial_number }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $instrument->category->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($instrument->kit)
                                        <a href="{{ route('instrument-kits.show', $instrument->kit) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                            <i class="fas fa-box mr-1"></i> {{ $instrument->kit->code }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Suelto</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $instrument->condition_color['classes'] }}">
                                        {{ $instrument->condition_color['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $instrument->status_color['classes'] }}">
                                        {{ $instrument->status_color['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end items-center space-x-2">
                                        <a href="{{ route('instruments.show', $instrument) }}" class="text-gray-400 hover:text-indigo-600" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('instruments.edit', $instrument) }}" class="text-gray-400 hover:text-blue-600" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-gray-400">
                                        <i class="fas fa-tools text-5xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No hay instrumentos registrados</p>
                                        <a href="{{ route('instruments.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg">
                                            <i class="fas fa-plus mr-2"></i> Nuevo Instrumento
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($instruments->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">{{ $instruments->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>