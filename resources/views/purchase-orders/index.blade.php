<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Órdenes de Compra') }}
            </h2>
            <a href="{{ route('purchase-orders.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Orden
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ showCancelModal: false, cancelId: null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alertas -->
            @if(session('success'))
                <div x-data="{ show: true }" 
                     x-show="show" 
                     x-transition
                     class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-green-700 font-medium">{{ session('success') }}</p>
                        </div>
                        <button @click="show = false" class="text-green-400 hover:text-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Tarjeta Principal -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Header -->
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Gestión de Órdenes</h3>
                            <p class="mt-1 text-sm text-gray-600">Administra todas las órdenes de compra</p>
                        </div>
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="font-medium">Total: {{ $purchaseOrders->total() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <form method="GET" action="{{ route('purchase-orders.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Búsqueda -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       placeholder="Número de orden...">
                            </div>

                            <!-- Proveedor -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor</label>
                                <select name="supplier_id" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Todos</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                                <select name="status" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Todos</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Recibida</option>
                                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Parcial</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                                    <option value="in_return" {{ request('status') == 'in_return' ? 'selected' : '' }}>En Devolución</option>
                                </select>
                            </div>

                            <!-- Pagado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pago</label>
                                <select name="is_paid" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Todos</option>
                                    <option value="1" {{ request('is_paid') === '1' ? 'selected' : '' }}>Pagado</option>
                                    <option value="0" {{ request('is_paid') === '0' ? 'selected' : '' }}>No Pagado</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white font-medium rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Filtrar
                            </button>
                            @if(request()->hasAny(['search', 'supplier_id', 'status', 'is_paid']))
                                <a href="{{ route('purchase-orders.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-all duration-200">
                                    Limpiar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    @if($purchaseOrders->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Orden
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Proveedor
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Pago
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($purchaseOrders as $order)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <!-- Orden -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-bold text-gray-900">{{ $order->order_number }}</div>
                                                    <div class="text-xs text-gray-500">{{ $order->total_items }} items</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Proveedor -->
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $order->supplier->name }}</div>
                                        </td>

                                        <!-- Fecha -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $order->order_date->format('d/m/Y') }}</div>
                                        </td>

                                        <!-- Estado -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                                {{ $order->status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $order->status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $order->status_color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $order->status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $order->status_color === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}">
                                                {{ $order->status_label }}
                                            </span>
                                        </td>

                                        <!-- Total -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">${{ number_format($order->total, 2) }}</div>
                                        </td>

                                        <!-- Pago -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($order->is_paid)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                    Pagado
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Acciones -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center space-x-3">
                                                <a href="{{ route('purchase-orders.show', $order) }}" 
                                                   title="Ver detalles"
                                                   class="text-blue-600 hover:text-blue-900 transition-colors duration-150">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                
                                                @if($order->canBeEdited())
                                                    <a href="{{ route('purchase-orders.edit', $order) }}" 
                                                       title="Editar"
                                                       class="text-orange-600 hover:text-orange-900 transition-colors duration-150">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay órdenes</h3>
                            <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva orden de compra.</p>
                            <div class="mt-6">
                                <a href="{{ route('purchase-orders.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Nueva Orden
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Paginación -->
                @if($purchaseOrders->count() > 0)
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $purchaseOrders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>