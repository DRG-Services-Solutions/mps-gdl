<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-layer-group text-purple-600 mr-2"></i> Gestor de Listado de Sets, Charolas y Kits
            </h2>
            <a href="{{ route('products.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm text-sm transition-colors">
                <i class="fas fa-plus mr-2"></i> Nuevo Set en Catálogo
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-xl border border-gray-200">
            <div class="p-6">
                <p class="text-gray-600 mb-6">Selecciona un Set, Charola o Kit".</p>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre del Set</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Componentes en Receta</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($sets as $set)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $set->code }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $set->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($set->components_count > 0)
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $set->components_count }} piezas
                                            </span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Charola Sin Productos
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($set->components_count > 0)
                                            <a href="{{ route('sets.build', $set) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold flex items-center justify-end">
                                            <i class="fas fa-tools mr-2"></i> Actualizar Charola/Set
                                        </a>
                                        @else
                                        <a href="{{ route('sets.build', $set) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold flex items-center justify-end">
                                            <i class="fas fa-tools mr-2"></i> Armar Charola
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        No hay productos marcados como "Set" en tu catálogo. <br> Ve a Productos y marca "Es un Producto Compuesto" en alguno.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $sets->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>