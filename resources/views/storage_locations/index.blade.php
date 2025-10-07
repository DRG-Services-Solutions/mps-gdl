<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Ubicaciones de Almacenamiento') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Gestión de áreas de almacén, recepción, cuarentena y envío.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showDeleteModal: false, locationToDelete: null }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

                <!-- Header Section -->
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Listado de Ubicaciones') }}</h3>
                        <p class="text-sm text-gray-600">{{ $locations->total() }} {{ __('registros encontrados') }}</p>
                    </div>
                    <a href="{{ route('storage_locations.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('Nueva ubicación') }}
                    </a>
                </div>

                <!-- Success message -->
                @if(session('success'))
                    <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-200" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center text-emerald-800 font-medium">
                                <i class="fas fa-check-circle mr-2 text-emerald-600"></i>
                                {{ session('success') }}
                            </div>
                            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase hidden md:table-cell">Tipo</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Activo</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($locations as $location)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $location->code }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $location->name }}</td>
                                    <td class="px-6 py-4 hidden md:table-cell">
                                        @php
                                            $colors = [
                                                'warehouse' => 'bg-blue-100 text-blue-800',
                                                'reception' => 'bg-green-100 text-green-800',
                                                'quarantine' => 'bg-yellow-100 text-yellow-800',
                                                'shipping' => 'bg-purple-100 text-purple-800',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$location->type] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($location->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($location->is_active)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                                <i class="fas fa-check-circle mr-1"></i> Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">
                                                <i class="fas fa-ban mr-1"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('storage_locations.edit', $location) }}" 
                                               class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 shadow-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button 
                                                @click="locationToDelete = { id: {{ $location->id }}, name: '{{ addslashes($location->name) }}' }; showDeleteModal = true"
                                                class="px-3 py-2 bg-red-50 border border-red-300 rounded-md text-red-700 hover:bg-red-100 shadow-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        <i class="fas fa-warehouse text-gray-300 text-5xl mb-3"></i>
                                        <p>No hay ubicaciones registradas.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($locations->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                        <p class="text-sm text-gray-600">
                            Mostrando {{ $locations->firstItem() }} al {{ $locations->lastItem() }} de {{ $locations->total() }}
                        </p>
                        {{ $locations->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Delete Modal -->
        <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">¿Eliminar ubicación?</h3>
                <p class="text-sm text-gray-600 mb-6">Esta acción no se puede deshacer.</p>
                <div class="flex justify-end space-x-3">
                    <form x-bind:action="`{{ url('storage_locations') }}/${locationToDelete?.id}`" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Eliminar
                        </button>
                    </form>
                    <button @click="showDeleteModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
