<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-warehouse mr-2"></i>
                {{ __('Sub-Almacenes Virtuales') }}
            </h2>
            <a href="{{ route('sub-warehouses.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-all duration-150">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Sub-Almacén
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Mensajes de éxito/error -->
            @if(session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Filtros y Búsqueda -->
            <div class="bg-white rounded-lg shadow-md mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('sub-warehouses.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Búsqueda -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-search mr-1"></i>
                                    Buscar
                                </label>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       placeholder="Nombre del sub-almacén..."
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <!-- Filtro por Razón Social -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-building mr-1"></i>
                                    Razón Social
                                </label>
                                <select name="legal_entity_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Todas</option>
                                    @foreach($legalEntities as $entity)
                                        <option value="{{ $entity->id }}" 
                                                {{ request('legal_entity_id') == $entity->id ? 'selected' : '' }}>
                                            {{ $entity->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filtro por Estado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-toggle-on mr-1"></i>
                                    Estado
                                </label>
                                <select name="status" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Todos</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('sub-warehouses.index') }}" 
                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                <i class="fas fa-undo mr-1"></i>
                                Limpiar
                            </a>
                            <button type="submit" 
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                                <i class="fas fa-filter mr-1"></i>
                                Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Listado -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                @if($subWarehouses->count() > 0)
                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-indigo-600 to-purple-600">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        Nombre
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        Razón Social
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                        Unidades
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                        Valor
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($subWarehouses as $subWarehouse)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <!-- Nombre -->
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $subWarehouse->name }}
                                                </div>
                                                @if($subWarehouse->description)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        {{ Str::limit($subWarehouse->description, 60) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Razón Social -->
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ $subWarehouse->legalEntity->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono">
                                                {{ $subWarehouse->legalEntity->rfc }}
                                            </div>
                                        </td>

                                        <!-- Unidades -->
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                                <i class="fas fa-boxes mr-1"></i>
                                                {{ $subWarehouse->getTotalUnits() }}
                                            </span>
                                        </td>

                                        <!-- Valor -->
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm font-semibold text-green-600">
                                                ${{ number_format($subWarehouse->getTotalValue(), 2) }}
                                            </span>
                                        </td>

                                        <!-- Estado -->
                                        <td class="px-6 py-4 text-center">
                                            @if($subWarehouse->is_active)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Activo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                    <i class="fas fa-times-circle mr-1"></i>
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Acciones -->
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <!-- Ver -->
                                                <a href="{{ route('sub-warehouses.show', $subWarehouse) }}" 
                                                   class="text-blue-600 hover:text-blue-800 transition-colors"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye text-lg"></i>
                                                </a>

                                                <!-- Editar -->
                                                <a href="{{ route('sub-warehouses.edit', $subWarehouse) }}" 
                                                   class="text-indigo-600 hover:text-indigo-800 transition-colors"
                                                   title="Editar">
                                                    <i class="fas fa-edit text-lg"></i>
                                                </a>

                                                <!-- Toggle Estado -->
                                                <form action="{{ route('sub-warehouses.toggle-status', $subWarehouse) }}" 
                                                      method="POST" 
                                                      class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="text-yellow-600 hover:text-yellow-800 transition-colors"
                                                            title="{{ $subWarehouse->is_active ? 'Desactivar' : 'Activar' }}"
                                                            onclick="return confirm('¿Está seguro de cambiar el estado?')">
                                                        <i class="fas fa-power-off text-lg"></i>
                                                    </button>
                                                </form>

                                                <!-- Eliminar -->
                                                <form action="{{ route('sub-warehouses.destroy', $subWarehouse) }}" 
                                                      method="POST" 
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-800 transition-colors"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Está seguro de eliminar este sub-almacén? Esta acción no se puede deshacer.')">
                                                        <i class="fas fa-trash text-lg"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        {{ $subWarehouses->links() }}
                    </div>
                @else
                    <!-- Estado vacío -->
                    <div class="text-center py-12">
                        <i class="fas fa-warehouse text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            No hay sub-almacenes registrados
                        </h3>
                        <p class="text-gray-500 mb-6">
                            Comienza creando tu primer sub-almacén virtual
                        </p>
                        <a href="{{ route('sub-warehouses.create') }}" 
                           class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-all">
                            <i class="fas fa-plus mr-2"></i>
                            Crear Primer Sub-Almacén
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>