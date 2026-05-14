<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-book-medical mr-2 text-indigo-600"></i>
                    Catálogo de Gestión Quirúrgica
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Definición maestra de jerarquías: Torres, Consolas, Charolas e Instrumentales.
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('items.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-all">
                    <i class="fas fa-plus mr-2"></i> Nuevo Modelo
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <i class="fas fa-layer-group text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Modelos Activos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activeCount }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex items-center">
                    <div class="p-3 rounded-full bg-amber-100 text-amber-600 mr-4">
                        <i class="fas fa-microchip text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Equipos Técnicos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $requiresMaintenanceCount }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex items-center">
                    <div class="p-3 rounded-full bg-emerald-100 text-emerald-600 mr-4">
                        <i class="fas fa-clipboard-list text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Estructuras/Recetas</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $kitCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <form action="{{ route('items.index') }}" method="GET" class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar en Catálogo</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                       class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" 
                                       placeholder="SKU, Modelo o Marca...">
                            </div>
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Clasificación</label>
                            <select name="type" id="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">Todas las categorías</option>
                                
                                <optgroup label="Estructuras Contenedoras">
                                    <option value="tower" {{ request('type') == 'tower' ? 'selected' : '' }}>Torre</option>
                                    <option value="kit" {{ request('type') == 'kit' ? 'selected' : '' }}>Kit</option>
                                    <option value="tray" {{ request('type') == 'tray' ? 'selected' : '' }}>Charola</option>
                                    <option value="instrumental_set" {{ request('type') == 'instrumental_set' ? 'selected' : '' }}>Set de Instrumental</option>
                                    <option value="implant_set" {{ request('type') == 'implant_set' ? 'selected' : '' }}>Set de Implantes</option>
                                </optgroup>
                                
                                <optgroup label="Hardware Médico">
                                    <option value="console" {{ request('type') == 'console' ? 'selected' : '' }}>Consola (Shavers, RF)</option>
                                    <option value="equipment" {{ request('type') == 'equipment' ? 'selected' : '' }}>Equipo Mayor / Monitor</option>
                                </optgroup>
                                
                                <optgroup label="Componentes Individuales">
                                    <option value="instrumental" {{ request('type') == 'instrumental' ? 'selected' : '' }}>Instrumento Suelto (Pinzas, Lentes)</option>
                                    <option value="implant" {{ request('type') == 'implant' ? 'selected' : '' }}>Implante (Tornillo, Placa)</option>
                                    <option value="accessory" {{ request('type') == 'accessory' ? 'selected' : '' }}>Accesorio (Cables, Pedales)</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="flex space-x-2">
                            <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Filtrar
                            </button>
                            @if(request()->anyFilled(['search', 'type']))
                                <a href="{{ route('items.index') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Limpiar
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo / Marca</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clasificación</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Físico</th>
                                <th class="relative px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($items as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-bold text-indigo-600">
                                        {{ $item->code }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->manufacturer ?? 'Genérico' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $badgeBg = match($item->type) {
                                                'tower'            => 'bg-slate-800 text-white',
                                                'kit'              => 'bg-purple-100 text-purple-800',
                                                'console'          => 'bg-indigo-100 text-indigo-800',
                                                'tray'             => 'bg-emerald-100 text-emerald-800',
                                                'instrumental_set' => 'bg-teal-100 text-teal-800',
                                                'implant_set'      => 'bg-rose-100 text-rose-800',
                                                'equipment'        => 'bg-blue-100 text-blue-800',
                                                'implant'          => 'bg-pink-100 text-pink-800',
                                                'accessory'        => 'bg-orange-100 text-orange-800',
                                                'instrumental'     => 'bg-gray-100 text-gray-800',
                                                default            => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeBg }}">
                                            {{ $item->type_label }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-sm text-gray-900 font-bold">{{ $item->stock_units_count }}</div>
                                        <div class="text-xs text-gray-500">unidades</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('items.show', $item) }}" class="text-indigo-600 hover:text-indigo-900 mx-2" title="Ver Detalle">
                                            <i class="fas fa-eye text-lg"></i>
                                        </a>
                                        <a href="{{ route('items.edit', $item) }}" class="text-blue-600 hover:text-blue-900 mx-2" title="Editar">
                                            <i class="fas fa-edit text-lg"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>