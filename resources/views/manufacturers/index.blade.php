<!-- vista index de manufacturadores -->
 <x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Administración de Fabricantes') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Gestiona las empresas que proveen los productos quirúrgicos.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-cyan-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            {{-- Icono para Fabricantes (usaremos un icono de edificio/fábrica) --}}
                            <i class="fas fa-industry w-8 h-8 text-teal-600 text-2xl"></i> 
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Lista de Fabricantes') }}</h3>
                                {{-- Asegúrate de que $manufacturers esté paginado para usar total() --}}
                                <p class="text-sm text-gray-600">{{ $manufacturers->total() }} {{ __('fabricantes registrados') }}</p>
                            </div>
                        </div>
                        {{-- Botón de Crear Fabricante --}}
                        <a href="{{ route('manufacturers.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                            <i class="fas fa-plus w-5 h-5 mr-2"></i>
                            {{ __('Crear Fabricante') }}
                        </a>
                    </div>
                </div>

                {{-- Mensaje de Éxito (Success Message) --}}
                @if(session('success'))
                    <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-200" x-data="{ show: true }" x-show="show" x-transition>
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
                
                {{-- Contenedor de la Tabla --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-1/3">
                                    {{ __('Nombre del Fabricante') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-1/2">
                                    {{ __('Descripción') }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Productos') }}
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Recorrido de Fabricantes --}}
                            @foreach ($manufacturers as $manufacturer)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $manufacturer->name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $manufacturer->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 max-w-lg truncate">
                                        {{ $manufacturer->description ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-700">
                                        {{-- Muestra el número de productos relacionados --}}
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                                            {{ $manufacturer->products_count ?? $manufacturer->products()->count() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            {{-- Botón Editar --}}
                                            <a href="{{ route('manufacturers.edit', $manufacturer->id) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border rounded hover:bg-gray-50">
                                                <i class="fas fa-edit mr-1"></i> {{ __('Editar') }}
                                            </a>
                                            {{-- Formulario para Eliminar --}}
                                            <form action="{{ route('manufacturers.destroy', $manufacturer->id) }}" method="POST"
                                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar a este fabricante? Los productos asociados quedarán sin fabricante.');">
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
                    @if($manufacturers->hasPages())
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            {{ $manufacturers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Script para Font Awesome (ya lo tenías, lo dejo aquí para completar la vista) --}}
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</x-app-layout>