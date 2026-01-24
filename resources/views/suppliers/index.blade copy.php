<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Administración de Proveedores') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Gestión de proveedores y empresas suministradoras de productos médicos.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                {{-- Encabezado y Botón Crear --}}
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-truck w-8 h-8 text-blue-600 text-2xl"></i> 
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Lista de Proveedores') }}</h3>
                                <p class="text-sm text-gray-600">{{ $suppliers->total() }} {{ __('proveedores registrados') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('suppliers.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                            <i class="fas fa-plus w-5 h-5 mr-2"></i>
                            {{ __('Crear Proveedor') }}
                        </a>
                    </div>
                </div>

                {{-- Mensaje de Éxito --}}
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

                {{-- Mensaje de Error --}}
                @if(session('error'))
                    <div class="px-6 py-4 bg-red-50 border-b border-red-200" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition 
                         x-init="setTimeout(() => show = false, 4000)">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle w-5 h-5 text-red-600 mr-2"></i>
                                <span class="text-red-800 font-medium">{{ session('error') }}</span>
                            </div>
                            <button @click="show = false" class="text-red-600 hover:text-red-800">
                               <i class="fas fa-times w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                @endif
                
                {{-- Tabla de Proveedores --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Código') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Proveedor') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Contacto') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Información') }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Estado') }}
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($suppliers as $supplier)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-semibold bg-gray-100 text-gray-800">
                                            {{ $supplier->code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-building text-indigo-600"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-semibold text-gray-900">{{ $supplier->name }}</div>
                                                <div class="text-xs text-gray-500">ID: {{ $supplier->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-user text-gray-400 mr-1"></i>
                                            {{ $supplier->contact_person ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            @if($supplier->email)
                                                <div class="text-sm text-gray-700">
                                                    <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                                    {{ $supplier->email }}
                                                </div>
                                            @endif
                                            @if($supplier->phone)
                                                <div class="text-sm text-gray-700">
                                                    <i class="fas fa-phone text-gray-400 mr-1"></i>
                                                    {{ $supplier->phone }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($supplier->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                {{ __('Activo') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                {{ __('Inactivo') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            {{-- Botón Ver --}}
                                            <a href="{{ route('suppliers.show', $supplier->id) }}" 
                                               class="px-3 py-2 text-sm text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
                                               title="Ver detalles">
                                                <i class="fas fa-eye mr-1"></i> {{ __('Ver') }}
                                            </a>
                                            
                                            {{-- Botón Editar --}}
                                            <a href="{{ route('suppliers.edit', $supplier->id) }}" 
                                               class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                               title="Editar proveedor">
                                                <i class="fas fa-edit mr-1"></i> {{ __('Editar') }}
                                            </a>
                                            
                                            {{-- Formulario para Eliminar --}}
                                            <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST"
                                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar este proveedor? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="px-3 py-2 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors"
                                                        title="Eliminar proveedor">
                                                    <i class="fas fa-trash-alt mr-1"></i> {{ __('Eliminar') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                                            <p class="text-gray-500 text-lg font-medium">{{ __('No hay proveedores registrados') }}</p>
                                            <p class="text-gray-400 text-sm mt-1">{{ __('Comienza creando tu primer proveedor') }}</p>
                                            <a href="{{ route('suppliers.create') }}" 
                                               class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg">
                                                <i class="fas fa-plus mr-2"></i>
                                                {{ __('Crear Proveedor') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Paginación --}}
                    @if($suppliers->hasPages())
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            {{ $suppliers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>