<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ __('Razones Sociales') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Gestiona las entidades legales de tu empresa') }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showDeleteModal: false, entityToDelete: null, showToggleModal: false, entityToToggle: null }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Mensajes de éxito/error -->
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                
                <!-- Header Section -->
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-briefcase text-indigo-600 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Entidades Legales') }}</h3>
                                <p class="text-sm text-gray-600">{{ $legalEntities->count() }} {{ __('razones sociales registradas') }}</p>
                            </div>
                        </div>
                        
                        <a href="{{ route('legal-entities.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                            <i class="fas fa-plus mr-2"></i>
                            {{ __('Nueva Razón Social') }}
                        </a>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Nombre') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('RFC') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Inventario') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Órdenes de Compra') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Estado') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($legalEntities as $entity)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-building text-indigo-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $entity->name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $entity->razon_social }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-mono">{{ $entity->rfc }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <span class="font-semibold">{{ $entity->total_units }}</span> unidades
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ${{ number_format($entity->total_inventory_value, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $entity->purchase_orders_count }} órdenes
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($entity->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                {{ __('Activa') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                {{ __('Inactiva') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <!-- Ver -->
                                            <a href="{{ route('legal-entities.show', $entity) }}" 
                                               class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200"
                                               title="{{ __('Ver detalles') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Editar -->
                                            <a href="{{ route('legal-entities.edit', $entity) }}" 
                                               class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                               title="{{ __('Editar') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Toggle Status -->
                                            <button @click="showToggleModal = true; entityToToggle = {{ $entity }}"
                                                    class="text-yellow-600 hover:text-yellow-900 transition-colors duration-200"
                                                    title="{{ $entity->is_active ? __('Desactivar') : __('Activar') }}">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                            
                                            <!-- Eliminar -->
                                            <button @click="showDeleteModal = true; entityToDelete = {{ $entity }}"
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                    title="{{ __('Eliminar') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-briefcase text-gray-300 text-5xl mb-4"></i>
                                            <p class="text-gray-500 text-lg font-medium">{{ __('No hay razones sociales registradas') }}</p>
                                            <p class="text-gray-400 text-sm mt-1">{{ __('Comienza agregando tu primera entidad legal') }}</p>
                                            <a href="{{ route('legal-entities.create') }}" 
                                               class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                                                <i class="fas fa-plus mr-2"></i>
                                                {{ __('Crear Razón Social') }}
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

        <!-- Modal de Confirmación de Eliminación -->
        <div x-show="showDeleteModal" 
             x-cloak
             class="fixed z-50 inset-0 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showDeleteModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showDeleteModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ __('Eliminar Razón Social') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('¿Estás seguro de que deseas eliminar') }} 
                                    <span class="font-semibold" x-text="entityToDelete?.name"></span>?
                                    {{ __('Esta acción no se puede deshacer.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <form x-bind:action="`{{ url('legal-entities') }}/${entityToDelete?.id}`" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <i class="fas fa-trash mr-2"></i>
                                {{ __('Eliminar') }}
                            </button>
                        </form>
                        <button @click="showDeleteModal = false" 
                                type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                            {{ __('Cancelar') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación de Cambio de Estado -->
        <div x-show="showToggleModal" 
             x-cloak
             class="fixed z-50 inset-0 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showToggleModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showToggleModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-power-off text-yellow-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                {{ __('Cambiar Estado') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('¿Estás seguro de que deseas') }} 
                                    <span class="font-semibold" x-text="entityToToggle?.is_active ? 'desactivar' : 'activar'"></span> 
                                    <span class="font-semibold" x-text="entityToToggle?.name"></span>?
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <form x-bind:action="`{{ url('legal-entities') }}/${entityToToggle?.id}/toggle-status`" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <i class="fas fa-check mr-2"></i>
                                {{ __('Confirmar') }}
                            </button>
                        </form>
                        <button @click="showToggleModal = false" 
                                type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                            {{ __('Cancelar') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>