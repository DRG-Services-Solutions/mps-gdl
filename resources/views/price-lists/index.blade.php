{{-- resources/views/price-lists/index.blade.php --}}
<x-app-layout>
    @push('styles')
    <style>
        .ts-wrapper { width: 100% !important; }
        .ts-wrapper .ts-control {
            border: 1px solid #d1d5db !important; border-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important; background-image: none !important;
            min-height: 42px !important; display: flex !important; align-items: center !important;
        }
        .ts-wrapper .ts-control input[type="text"], .ts-wrapper .ts-control > input {
            border: none !important; padding: 0 !important; margin: 0 !important;
            background: transparent !important; box-shadow: none !important; outline: none !important;
            min-height: auto !important; width: auto !important; flex: 1 1 auto !important;
        }
        .ts-wrapper.focus .ts-control { border-color: #6366f1 !important; box-shadow: 0 0 0 1px #6366f1 !important; }
        .ts-wrapper .ts-dropdown { border: 1px solid #d1d5db !important; border-radius: 0.5rem !important; margin-top: 4px !important; z-index: 9999 !important; }
        .ts-wrapper .ts-dropdown .option { padding: 8px 12px !important; }
        .ts-wrapper .ts-dropdown .active { background-color: #eef2ff !important; color: #4f46e5 !important; }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-tags mr-2 text-indigo-600"></i>
                    {{ __('Listas de Precios') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Gestión de listas de precios por hospital / cliente</p>
            </div>
            <a href="{{ route('price-lists.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-plus mr-2"></i>
                Nueva Lista
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Listas</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalCount }}</p>
                        </div>
                        <div class="bg-indigo-100 rounded-full p-3">
                            <i class="fas fa-tags text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Activas</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $activeCount }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-check-circle text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-gray-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Inactivas</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalCount - $activeCount }}</p>
                        </div>
                        <div class="bg-gray-100 rounded-full p-3">
                            <i class="fas fa-pause-circle text-2xl text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form method="GET" action="{{ route('price-lists.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i> Buscar
                            </label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Nombre, código, hospital..."
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-hospital mr-1"></i> Hospital / Cliente
                            </label>
                            <select name="hospital_id" id="hospital_filter" placeholder="Buscar hospital...">
                                @if($selectedHospital)
                                    <option value="{{ $selectedHospital->id }}" selected>{{ $selectedHospital->name }}</option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-filter mr-1"></i> Estado
                            </label>
                            <select name="status" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activas</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivas</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('price-lists.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i> Limpiar
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lista</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hospital / Cliente</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($priceLists as $list)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-tags text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $list->name }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $list->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-hospital text-gray-400 mr-1"></i>
                                        {{ $list->hospital->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2 rounded-lg text-sm font-bold {{ $list->items_count > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-50 text-gray-400' }}">
                                        {{ $list->items_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($list->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Activa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-pause-circle mr-1"></i> Inactiva
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end items-center space-x-2">
                                        <a href="{{ route('price-lists.show', $list) }}" class="inline-flex items-center px-3 py-1 text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                            <i class="fas fa-eye mr-1"></i> Ver
                                        </a>
                                        <div class="flex items-center ml-2 border-l pl-2 space-x-2 border-gray-200">
                                            <a href="{{ route('price-lists.edit', $list) }}" class="text-gray-400 hover:text-blue-600" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(!$list->is_active)
                                                <form action="{{ route('price-lists.activate', $list) }}" method="POST" class="inline" onsubmit="return confirm('¿Activar esta lista? Las demás listas del hospital se desactivarán.')">
                                                    @csrf
                                                    <button type="submit" class="text-gray-400 hover:text-green-600" title="Activar">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-tags text-5xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No hay listas de precios</p>
                                        <p class="text-sm text-gray-600 mb-4">Crea una nueva lista para un hospital o cliente</p>
                                        <a href="{{ route('price-lists.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                            <i class="fas fa-plus mr-2"></i> Nueva Lista
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($priceLists->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">{{ $priceLists->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        new TomSelect('#hospital_filter', {
            valueField: 'id', labelField: 'text', searchField: 'text',
            placeholder: 'Buscar hospital...', openOnFocus: false,
            plugins: ['clear_button'],
            shouldLoad: function(query) { return query.length > 0; },
            load: function(query, callback) {
                fetch(`/api/hospitals/select2?search=${encodeURIComponent(query)}`)
                    .then(r => r.json()).then(data => callback(data.results)).catch(() => callback());
            },
            render: {
                option: (data, escape) => `<div class="py-2 px-3">${escape(data.text)}</div>`,
                item: (data, escape) => `<div>${escape(data.text)}</div>`,
                no_results: () => '<div style="padding:10px;text-align:center;color:#6b7280;">No se encontraron hospitales</div>',
            },
        });
    </script>
    @endpush
</x-app-layout>
