{{-- resources/views/price-lists/show.blade.php --}}
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
                    {{ $priceList->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-indigo-600">{{ $priceList->code }}</span>
                    <span class="mx-2">|</span>
                    <i class="fas fa-hospital mr-1"></i> {{ $priceList->hospital->name }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('price-lists.import', $priceList) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-file-csv mr-2"></i> Importar CSV
                </a>
                <a href="{{ route('price-lists.edit', $priceList) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
                <a href="{{ route('price-lists.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Info + Estado -->
            <div class="bg-gradient-to-r {{ $priceList->is_active ? 'from-green-500 to-emerald-600' : 'from-gray-400 to-gray-500' }} rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white bg-opacity-20 rounded-lg p-4">
                            <i class="fas fa-tags text-4xl"></i>
                        </div>
                        <div>
                            <p class="text-sm uppercase tracking-wider opacity-80">Estado</p>
                            <h3 class="text-2xl font-bold">{{ $priceList->is_active ? 'Lista Activa' : 'Lista Inactiva' }}</h3>
                            <p class="text-sm opacity-80">{{ $priceList->hospital->name }}</p>
                        </div>
                    </div>
                    <div>
                        @if($priceList->is_active)
                            <form action="{{ route('price-lists.deactivate', $priceList) }}" method="POST" onsubmit="return confirm('¿Desactivar esta lista?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg text-sm font-semibold transition-colors">
                                    <i class="fas fa-pause mr-1"></i> Desactivar
                                </button>
                            </form>
                        @else
                            <form action="{{ route('price-lists.activate', $priceList) }}" method="POST" onsubmit="return confirm('¿Activar esta lista? Las demás del hospital se desactivarán.')">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg text-sm font-semibold transition-colors">
                                    <i class="fas fa-play mr-1"></i> Activar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Productos</p>
                    <p class="text-3xl font-black text-gray-900">{{ $stats['total_products'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Precio Promedio</p>
                    <p class="text-3xl font-black text-gray-900">${{ number_format($stats['avg_price'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Precio Mínimo</p>
                    <p class="text-3xl font-black text-gray-900">${{ number_format($stats['min_price'] ?? 0, 2) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Precio Máximo</p>
                    <p class="text-3xl font-black text-gray-900">${{ number_format($stats['max_price'] ?? 0, 2) }}</p>
                </div>
            </div>

            <!-- Agregar producto manual -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i> Agregar Producto
                </h3>
                <form action="{{ route('price-lists.items.add', $priceList) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
                            <select name="product_id" id="product_search" placeholder="Buscar producto por código o nombre..." required></select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Precio Unitario</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">$</span>
                                <input type="number" name="unit_price" step="0.01" min="0" required placeholder="0.00"
                                       class="w-full pl-7 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="md:col-span-3">
                            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-plus mr-1"></i> Agregar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabla de productos -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list mr-2 text-indigo-600"></i>
                        Productos en la Lista ({{ $stats['total_products'] }})
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unitario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notas</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" x-data="{ editingId: null }">
                            @forelse($items as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-9 w-9 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-sm"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-bold text-gray-900">{{ $item->product->code }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->product->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    {{-- Vista normal --}}
                                    <span x-show="editingId !== {{ $item->id }}" class="text-sm font-bold text-gray-900">
                                        ${{ number_format($item->unit_price, 2) }}
                                    </span>

                                    {{-- Modo edición inline --}}
                                    <form x-show="editingId === {{ $item->id }}"
                                          action="{{ route('price-lists.items.update', [$priceList, $item]) }}" method="POST"
                                          class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 text-xs">$</span>
                                            <input type="number" name="unit_price" step="0.01" min="0"
                                                   value="{{ $item->unit_price }}"
                                                   class="w-28 pl-5 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <button type="submit" class="text-green-600 hover:text-green-800" title="Guardar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" @click="editingId = null" class="text-gray-400 hover:text-gray-600" title="Cancelar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-gray-500 italic">{{ $item->notes ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end items-center space-x-2">
                                        <button @click="editingId = {{ $item->id }}" x-show="editingId !== {{ $item->id }}"
                                                class="text-gray-400 hover:text-blue-600" title="Editar precio">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('price-lists.items.remove', [$priceList, $item]) }}" method="POST" class="inline"
                                              onsubmit="return confirm('¿Eliminar este producto de la lista?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-600" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-gray-400">
                                        <i class="fas fa-inbox text-5xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-900 mb-2">Lista vacía</p>
                                        <p class="text-sm text-gray-600 mb-4">Importa productos desde un CSV o agrégalos manualmente arriba</p>
                                        <a href="{{ route('price-lists.import', $priceList) }}"
                                           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                            <i class="fas fa-file-csv mr-2"></i> Importar CSV
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        new TomSelect('#product_search', {
            valueField: 'id', labelField: 'text', searchField: 'text',
            placeholder: 'Buscar producto por código o nombre...', openOnFocus: false,
            shouldLoad: function(query) { return query.length > 0; },
            load: function(query, callback) {
                fetch(`{{ route('price-lists.search-products', $priceList) }}?search=${encodeURIComponent(query)}`)
                    .then(r => r.json()).then(data => callback(data.results)).catch(() => callback());
            },
            render: {
                option: function(data, escape) {
                    return `<div class="py-2 px-3">
                        <div class="font-medium">${escape(data.text)}</div>
                        <div class="text-xs text-gray-400">Precio base: $${parseFloat(data.price).toFixed(2)}</div>
                    </div>`;
                },
                item: (data, escape) => `<div>${escape(data.text)}</div>`,
                no_results: () => '<div style="padding:10px;text-align:center;color:#6b7280;">No se encontraron productos</div>',
            },
            onChange: function(productId) {
                // Obtener el item seleccionado y poner su precio en el input
                const item = this.options[productId];
                if (item && item.price) {
                    document.querySelector('input[name="unit_price"]').value = parseFloat(item.price).toFixed(2);
                }
            },
        });
    </script>
    @endpush
</x-app-layout>
