{{-- resources/views/hospitals/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-hospital mr-2 text-indigo-600"></i>
                    {{ __('Hospitales / Clientes') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Gestión de hospitales, clientes y su configuración fiscal</p>
            </div>
            <a href="{{ route('hospitals.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Hospital / Cliente
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Estadísticas Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $hospitals->total() }}</p>
                        </div>
                        <div class="bg-indigo-100 rounded-full p-3">
                            <i class="fas fa-hospital text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Activos</p>
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
                            <p class="text-sm font-medium text-gray-600">Inactivos</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $inactiveCount }}</p>
                        </div>
                        <div class="bg-gray-100 rounded-full p-3">
                            <i class="fas fa-pause-circle text-2xl text-gray-500"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Sin Configuración</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $noConfigCount }}</p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form method="GET" action="{{ route('hospitals.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Búsqueda -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i>
                                Buscar
                            </label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Nombre, RFC..."
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Estado -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-filter mr-1"></i>
                                Estado
                            </label>
                            <select name="status" 
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                                <option value="no_config" {{ request('status') === 'no_config' ? 'selected' : '' }}>Sin configuración fiscal</option>
                            </select>
                        </div>

                        <!-- Modalidad -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-layer-group mr-1"></i>
                                Modalidad
                            </label>
                            <select name="modality_id" 
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todas las modalidades</option>
                                @foreach($modalities as $modality)
                                    <option value="{{ $modality->id }}" {{ request('modality_id') == $modality->id ? 'selected' : '' }}>
                                        {{ $modality->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('hospitals.index') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-search mr-1"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Listado -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hospital / Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Configuración Fiscal</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cirugías</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($hospitals as $hospital)
                            <tr class="hover:bg-gray-50 transition-colors">
                                {{-- Hospital / Cliente --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-hospital text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $hospital->name }}</div>
                                            @if($hospital->rfc)
                                                <div class="text-xs text-gray-500 font-mono">{{ $hospital->rfc }}</div>
                                            @else
                                                <div class="text-xs text-gray-400 italic">Sin RFC</div>
                                            @endif
                                           
                                        </div>
                                    </div>
                                </td>

                                {{-- Configuración Fiscal --}}
                                <td class="px-6 py-4">
                                    @forelse($hospital->configs as $config)
                                        <div class="flex items-center text-xs mb-1 last:mb-0">
                                            @php
                                                $modalityClasses = match($config->modality->name ?? '') {
                                                    'Seguro'     => 'bg-blue-100 text-blue-700',
                                                    'Particular' => 'bg-purple-100 text-purple-700',
                                                    default      => 'bg-gray-100 text-gray-700',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold mr-2 {{ $modalityClasses }}">
                                                {{ $config->modality->name ?? 'Sin modalidad' }}
                                            </span>
                                            <span class="text-gray-600">
                                                <i class="fas fa-file-invoice-dollar mr-1 text-gray-400"></i>
                                                {{ $config->legalEntity->name ?? 'Sin razón social' }}
                                            </span>
                                        </div>
                                    @empty
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-50 text-red-600 border border-red-200">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Sin configuración fiscal
                                        </span>
                                    @endforelse
                                </td>

                                {{-- Cirugías --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2 rounded-lg text-sm font-bold {{ $hospital->surgeries_count > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-50 text-gray-400' }}">
                                        {{ $hospital->surgeries_count ?? 0 }}
                                    </span>
                                </td>

                                {{-- Estado --}}
                                <td class="px-6 py-4 text-center">
                                    @if($hospital->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-pause-circle mr-1"></i>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end items-center space-x-2">
                                        <a href="{{ route('hospitals.edit', $hospital) }}" 
                                           class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                            <i class="fas fa-cog mr-1"></i> Configurar
                                        </a>

                                        <div class="flex items-center ml-2 border-l pl-2 space-x-2 border-gray-200">
                                            <a href="{{ route('hospitals.show', $hospital) }}" class="text-gray-400 hover:text-indigo-600" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-hospital text-5xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No hay hospitales registrados</p>
                                        <p class="text-sm text-gray-600 mb-4">Comienza registrando un nuevo hospital o cliente</p>
                                        <a href="{{ route('hospitals.create') }}" 
                                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                            <i class="fas fa-plus mr-2"></i>
                                            Nuevo Hospital / Cliente
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($hospitals->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $hospitals->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>