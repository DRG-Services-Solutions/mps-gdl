<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Administración de Categorías') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Define y gestiona las clasificaciones principales de los productos.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                {{-- Encabezado y Botón Crear --}}
                <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-cyan-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-tags w-8 h-8 text-teal-600 text-2xl"></i> 
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Lista de Categorías') }}</h3>
                                <p class="text-sm text-gray-600">{{ $categories->total() }} {{ __('categorías registradas') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('categories.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                            <i class="fas fa-plus w-5 h-5 mr-2"></i>
                            {{ __('Crear Categoría') }}
                        </a>
                    </div>
                </div>

                {{-- Mensaje de Éxito (Alpine.js) --}}
                @if(session('success'))
                    <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-200" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition 
                         x-init="setTimeout(() => show = false, 3000)">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle w-5 h-5 text-emerald-600 mr-2"></i>
                                <span class="text-emerald-800 font-medium">{{ session('success') }}</span>
                            </div>
                            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                               <i class="fas fa-times w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                @endif
                
                {{-- Tabla de Categorías --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-1/4">
                                    {{ __('Nombre') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-1/2">
                                    {{ __('Descripción') }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Productos Asoc.') }}
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($categories as $category)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $category->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 max-w-xl">
                                        {{ Str::limit($category->description ?? 'Sin descripción.', 70) }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-700">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                                            {{ $category->products_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            {{-- Botón Editar --}}
                                            <a href="{{ route('categories.edit', $category->id) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border rounded hover:bg-gray-50">
                                                <i class="fas fa-edit mr-1"></i> {{ __('Editar') }}
                                            </a>
                                            {{-- Formulario para Eliminar --}}
                                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
                                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta categoría? Los productos asociados quedarán sin categoría.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-2 text-sm text-red-700 bg-red-50 border rounded hover:bg-red-100">
                                                    <i class="fas fa-trash-alt mr-1"></i> {{ __('Eliminar') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Paginación --}}
                    @if($categories->hasPages())
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            {{ $categories->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>