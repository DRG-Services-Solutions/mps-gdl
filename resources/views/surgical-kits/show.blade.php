<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Detalle del Prearmado: {{ $surgicalKit->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('surgical-kits.edit', $surgicalKit) }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('surgical-kits.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            {{-- Mensajes --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Información Básica --}}
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Información Básica</h3>
                    
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Código</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $surgicalKit->code }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Nombre</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $surgicalKit->name }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Tipo de Cirugía</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $surgicalKit->surgery_type }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Estado</p>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $surgicalKit->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $surgicalKit->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Creado por</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $surgicalKit->creator->name ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Fecha de creación</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $surgicalKit->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        @if($surgicalKit->description)
                            <div class="md:col-span-2 lg:col-span-3">
                                <p class="text-sm font-medium text-gray-500">Descripción</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $surgicalKit->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Resumen de Disponibilidad --}}
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Disponibilidad de Stock</h3>
                        <a href="{{ route('surgical-kits.check-stock', $surgicalKit) }}" 
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md hover:bg-purple-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Verificar Stock Completo
                        </a>
                    </div>

                    @if($availability['all_available'])
                        <div class="p-4 bg-green-100 border border-green-400 rounded-md">
                            <div class="flex">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">
                                        ✓ Stock completo disponible
                                    </p>
                                    <p class="mt-1 text-sm text-green-700">
                                        Todos los productos tienen inventario suficiente para aplicar este prearmado.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('surgical-kits.select-quotation', $surgicalKit) }}" 
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                Aplicar a Cotización
                            </a>
                        </div>
                    @else
                        <div class="p-4 bg-red-100 border border-red-400 rounded-md">
                            <div class="flex">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">
                                        ⚠ Stock insuficiente
                                    </p>
                                    <p class="mt-1 text-sm text-red-700">
                                        Algunos productos no tienen inventario suficiente. Revisa el detalle completo.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('surgical-kits.select-quotation', $surgicalKit) }}" 
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Aplicar Parcialmente
                            </a>
                        </div>
                    @endif

                    {{-- Estadísticas rápidas --}}
                    <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-3">
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm font-medium text-blue-900">Total Requerido</p>
                            <p class="mt-1 text-2xl font-bold text-blue-900">{{ $availability['total_required'] }}</p>
                        </div>
                        <div class="p-4 bg-green-50 rounded-lg">
                            <p class="text-sm font-medium text-green-900">Total Disponible</p>
                            <p class="mt-1 text-2xl font-bold text-green-900">{{ $availability['total_available'] }}</p>
                        </div>
                        <div class="p-4 bg-purple-50 rounded-lg">
                            <p class="text-sm font-medium text-purple-900">Total Productos</p>
                            <p class="mt-1 text-2xl font-bold text-purple-900">{{ $surgicalKit->total_products }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Listado de Productos --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">
                        Productos del Prearmado ({{ $surgicalKit->items->count() }})
                    </h3>
                    
                    @if($surgicalKit->items->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Código</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Producto</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">Cantidad</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Notas</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($surgicalKit->items as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                                {{ $item->product->code }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $item->product->name }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-center whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $item->quantity }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $item->notes ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No hay productos en este prearmado</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>