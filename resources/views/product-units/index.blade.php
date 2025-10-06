<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Inventario - Unidades de Productos') }}
            </h2>
            <a href="{{ route('product-units.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Registrar Entrada
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ showDeleteModal: false, deleteId: null, deleteName: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alertas -->
            @if(session('success'))
                <div x-data="{ show: true }" 
                     x-show="show" 
                     x-transition
                     class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-green-700 font-medium">{{ session('success') }}</p>
                        </div>
                        <button @click="show = false" class="text-green-400 hover:text-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" 
                     x-show="show" 
                     x-transition
                     class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-red-700 font-medium">{{ session('error') }}</p>
                        </div>
                        <button @click="show = false" class="text-red-400 hover:text-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Tarjeta Principal -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Header de la Tabla -->
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Inventario de Unidades</h3>
                            <p class="mt-1 text-sm text-gray-600">Gestiona todas las unidades físicas de productos</p>
                        </div>
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span class="font-medium">Total: {{ $units->total() }} unidades</span>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <form method="GET" action="{{ route('product-units.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Búsqueda -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <input type="text" 
                                           name="search" 
                                           value="{{ request('search') }}"
                                           class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="EPC, Serial, Lote...">
                                </div>
                            </div>

                            <!-- Filtro por Producto -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
                                <select name="product_id" 
                                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Todos los productos</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filtro por Estado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                                <select name="status" 
                                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Todos los estados</option>
                                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponible</option>
                                    <option value="in_use" {{ request('status') == 'in_use' ? 'selected' : '' }}>En Uso</option>
                                    <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reservado</option>
                                    <option value="in_sterilization" {{ request('status') == 'in_sterilization' ? 'selected' : '' }}>En Esterilización</option>
                                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
                                    <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>Dañado</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Caducado</option>
                                </select>
                            </div>
                        </div>

                       

                            <!-- Botones de Filtro -->
                            <div class="flex items-end space-x-2 md:col-span-3">
                                <button type="submit" 
                                        class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-800 hover:bg-gray-900 text-white font-medium rounded-lg transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                    Filtrar
                                </button>
                                @if(request()->hasAny(['search', 'product_id', 'status', 'location_id']))
                                    <a href="{{ route('product-units.index') }}" 
                                       class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-all duration-200">
                                        Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    @if($units->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Identificador
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Producto
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Lote
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Ubicación
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Caducidad
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($units as $unit)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <!-- Identificador -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                    @if($unit->epc)
                                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-bold text-gray-900">{{ $unit->unique_identifier }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $unit->epc ? 'RFID' : 'Serial' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Producto -->
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $unit->product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $unit->product->code }}</div>
                                        </td>

                                        <!-- Lote -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $unit->batch_number ?? 'N/A' }}</span>
                                        </td>

                                        <!-- Estado -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                                {{ $unit->status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $unit->status_color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $unit->status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $unit->status_color === 'purple' ? 'bg-purple-100 text-purple-800' : '' }}
                                                {{ $unit->status_color === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}
                                                {{ $unit->status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $unit->status_color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ $unit->status_label }}
                                            </span>
                                        </td>

                                        <!-- Ubicación -->
                                        <td class="px-6 py-4">
                                            <div class="flex items-center text-sm">
                                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                </svg>
                                                <span class="text-gray-900">
                                                    {{ $unit->currentLocation ? $unit->currentLocation->code : 'Sin asignar' }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Caducidad -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($unit->expiration_date)
                                                <div class="text-sm">
                                                    <div class="text-gray-900">{{ $unit->expiration_date->format('d/m/Y') }}</div>
                                                    @if($unit->isExpired())
                                                        <span class="text-xs text-red-600 font-semibold">¡Caducado!</span>
                                                    @elseif($unit->isExpiringSoon())
                                                        <span class="text-xs text-orange-600 font-semibold">Próximo a caducar</span>
                                                    @else
                                                        <span class="text-xs text-green-600">{{ $unit->days_until_expiration }} días</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-400">N/A</span>
                                            @endif
                                        </td>

                                        <!-- Acciones -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center space-x-3">
                                                <a href="{{ route('product-units.show', $unit) }}" 
                                                   title="Ver detalles"
                                                   class="text-blue-600 hover:text-blue-900 transition-colors duration-150">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay unidades registradas</h3>
                            <p class="mt-1 text-sm text-gray-500">Comienza registrando la entrada de productos.</p>
                            <div class="mt-6">
                                <a href="{{ route('product-units.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Registrar Primera Entrada
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Paginación -->
                @if($units->count() > 0)
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $units->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>