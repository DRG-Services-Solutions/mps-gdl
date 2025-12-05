<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-hospital mr-2 text-indigo-600"></i>
                    {{ __('Hospitales') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Gestión de hospitales y clientes</p>
            </div>
            <a href="{{ route('hospitals.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Hospital
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('hospitals.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-search mr-1"></i>Buscar
                            </label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Nombre, código, ciudad..."
                                   class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-filter mr-1"></i>Estado
                            </label>
                            <select name="status" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                <option value="">Todos</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                                <i class="fas fa-filter mr-2"></i>Filtrar
                            </button>
                            <a href="{{ route('hospitals.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                                <i class="fas fa-times mr-1"></i>Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                            <i class="fas fa-hospital text-indigo-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Total Hospitales</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $hospitals->total() }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Activos</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $hospitals->where('is_active', true)->count() }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gray-100 rounded-lg p-3">
                            <i class="fas fa-ban text-gray-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Inactivos</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $hospitals->where('is_active', false)->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hospital
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Código
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Contacto
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ubicación
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($hospitals as $hospital)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    <i class="fas fa-hospital text-indigo-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $hospital->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $hospital->code ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($hospital->contact_person || $hospital->phone)
                                            <div class="text-sm text-gray-900">{{ $hospital->contact_person ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">
                                                <i class="fas fa-phone mr-1"></i>{{ $hospital->phone ?? 'N/A' }}
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">Sin contacto</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($hospital->city || $hospital->state)
                                            <div class="text-sm text-gray-900">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $hospital->city ?? '' }}{{ $hospital->city && $hospital->state ? ', ' : '' }}{{ $hospital->state ?? '' }}
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">Sin ubicación</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form action="{{ route('hospitals.toggle-status', $hospital) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $hospital->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }} transition-colors">
                                                <i class="fas {{ $hospital->is_active ? 'fa-check-circle' : 'fa-ban' }} mr-1"></i>
                                                {{ $hospital->is_active ? 'Activo' : 'Inactivo' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('hospitals.show', $hospital) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('hospitals.edit', $hospital) }}" 
                                           class="text-gray-600 hover:text-gray-900">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-hospital text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-lg font-medium mb-2">No hay hospitales registrados</p>
                                        <a href="{{ route('hospitals.create') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-900 font-medium">
                                            <i class="fas fa-plus mr-1"></i>Crear primer hospital
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($hospitals->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $hospitals->links() }}
                    </div>
                @endif
            </div>
            
        </div>
    </div>
</x-app-layout>