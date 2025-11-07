<x-app-layout>
<x-slot name="header">
<div class="flex items-center justify-between">
<div>
<h2 class="text-2xl font-bold text-gray-900 leading-tight">
{{ __('Layouts de Productos (Ubicación Exacta)') }}
</h2>
<p class="mt-1 text-sm text-gray-600">
{{ __('Gestión de la ubicación precisa (Estante, Nivel, Posición) dentro de las bodegas.') }}
</p>
</div>
</div>
</x-slot>

<div class="py-8" x-data="{ showDeleteModal: false, layoutToDelete: null }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            {{-- Encabezado y Botón de Creación --}}
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Listado de Layouts') }}</h3>
                    {{-- La variable es $productLayouts, no $locations --}}
                    <p class="text-sm text-gray-600">{{ $productLayouts->total() }} {{ __('registros encontrados') }}</p>
                </div>
                <a href="{{ route('product_layouts.create') }}" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                    <i class="fas fa-plus mr-2"></i>
                    {{ __('Nuevo Layout') }}
                </a>
            </div>

            {{-- Mensajes de Éxito (Success Session) --}}
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

            {{-- Tabla de Registros --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Bodega Principal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Estante</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nivel</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Posición</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase hidden sm:table-cell">ID Producto</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- La variable que llega del controlador es $productLayouts --}}
                        @forelse($productLayouts as $layout)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                {{-- Columna de Bodega (Relación Eager Loaded) --}}
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    {{ $layout->storageLocation->name ?? 'N/A' }} 
                                    <span class="text-xs font-normal text-gray-500 block">{{ $layout->storageLocation->code ?? '' }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">{{ $layout->shelf }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $layout->level }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $layout->position }}</td>
                                {{-- Se asume que mostrarás el ID del producto por ahora --}}
                                <td class="px-6 py-4 text-gray-700 hidden sm:table-cell">#{{ $layout->product_id }}</td> 
                                
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-2">
                                        {{-- Botón Editar --}}
                                        <a href="{{ route('product_layouts.edit', $layout) }}" 
                                            class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 shadow-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        {{-- Botón Eliminar (Abre Modal) --}}
                                        <button 
                                            @click="layoutToDelete = { id: {{ $layout->id }}, name: 'Estante {{ addslashes($layout->shelf) }} - Nivel {{ addslashes($layout->level) }}' }; showDeleteModal = true"
                                            class="px-3 py-2 bg-red-50 border border-red-300 rounded-md text-red-700 hover:bg-red-100 shadow-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    <i class="fas fa-th-large text-gray-300 text-5xl mb-3"></i>
                                    <p>No hay layouts de productos registrados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($productLayouts->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                    <p class="text-sm text-gray-600">
                        Mostrando {{ $productLayouts->firstItem() }} al {{ $productLayouts->lastItem() }} de {{ $productLayouts->total() }}
                    </p>
                    {{ $productLayouts->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
    <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
        <div @click.away="showDeleteModal = false" class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6"
             x-transition.duration.300>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Confirmar Eliminación</h3>
            <p class="text-sm text-gray-600 mb-6">Está a punto de eliminar el Layout <strong x-text="layoutToDelete?.name"></strong>. Esta acción es permanente.</p>
            <div class="flex justify-end space-x-3">
                <form x-bind:action="`{{ url('product_layouts') }}/${layoutToDelete?.id}`" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 shadow-md transition-colors">
                        <i class="fas fa-trash-alt mr-1"></i> Eliminar
                    </button>
                </form>
                <button @click="showDeleteModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 shadow-sm transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>


</x-app-layout>