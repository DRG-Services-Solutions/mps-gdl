<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-medkit mr-2 text-indigo-600"></i>
                    {{ __('Kits Instrumentales') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Gestiona las plantillas de los kits y su contenido predeterminado</p>
            </div>
            <a href="{{ route('surgical_kit_templates.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Kit
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm mb-4" role="alert">
                    <p class="font-medium"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm p-6">
                <form method="GET" action="{{ route('surgical_kit_templates.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i>
                                Buscar
                            </label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Código o nombre del kit..."
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-filter mr-1"></i>
                                Estado
                            </label>
                            <select name="status" 
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Activos</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('surgical_kit_templates.index') }}" 
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

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kit Quirúrgico
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Artículos
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($templates as $template)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-medkit text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $template->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $template->code ?? 'Sin código asignado' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ Str::limit($template->description, 50) ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800" title="Total de artículos en la receta">
                                        {{ $template->items->count() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($template->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('surgical_kit_templates.show', $template) }}" 
                                       class="text-indigo-600 hover:text-indigo-900"
                                       title="Ver y gestionar artículos">
                                        <i class="fas fa-eye text-lg"></i>
                                    </a>
                                    <a href="{{ route('surgical_kit_templates.edit', $template) }}" 
                                       class="text-blue-600 hover:text-blue-900"
                                       title="Editar detalles del kit">
                                        <i class="fas fa-edit text-lg"></i>
                                    </a>
                                    <form action="{{ route('surgical_kit_templates.destroy', $template) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('¿Eliminar esta receta? Se perderán los artículos asociados. Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                title="Eliminar">
                                            <i class="fas fa-trash text-lg"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-box-open text-5xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No hay kits registrados</p>
                                        <p class="text-sm text-gray-600 mb-4">Comienza creando tu primera receta de kit quirúrgico</p>
                                        <a href="{{ route('surgical_kit_templates.create') }}" 
                                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                            <i class="fas fa-plus mr-2"></i>
                                            Crear Kit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($templates, 'hasPages') && $templates->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $templates->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>