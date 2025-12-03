<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('sub-warehouses.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        <i class="fas fa-warehouse mr-2"></i>
                        {{ $subWarehouse->name }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $subWarehouse->legalEntity->name }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('sub-warehouses.edit', $subWarehouse) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-all">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Mensajes -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Columna Izquierda: Información General -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Información Básica -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-info-circle mr-2"></i>
                                Información General
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Nombre -->
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Nombre</label>
                                <p class="text-lg font-semibold text-gray-900 mt-1">{{ $subWarehouse->name }}</p>
                            </div>

                            <!-- Razón Social -->
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Razón Social</label>
                                <p class="text-sm font-medium text-gray-900 mt-1">{{ $subWarehouse->legalEntity->name }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ $subWarehouse->legalEntity->rfc }}</p>
                            </div>

                            <!-- Descripción -->
                            @if($subWarehouse->description)
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Descripción</label>
                                    <p class="text-sm text-gray-700 mt-1">{{ $subWarehouse->description }}</p>
                                </div>
                            @endif

                            <!-- Estado -->
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Estado</label>
                                <div class="mt-2">
                                    @if($subWarehouse->is_active)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Inactivo
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Fechas -->
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Creado</label>
                                <p class="text-sm text-gray-700 mt-1">{{ $subWarehouse->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Estadísticas
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Total Unidades -->
                            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                <div>
                                    <p class="text-xs font-medium text-blue-600 uppercase">Unidades</p>
                                    <p class="text-3xl font-bold text-blue-900 mt-1">{{ $stats['total_units'] }}</p>
                                </div>
                                <i class="fas fa-boxes text-4xl text-blue-300"></i>
                            </div>

                            <!-- Valor Total -->
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                <div>
                                    <p class="text-xs font-medium text-green-600 uppercase">Valor Total</p>
                                    <p class="text-2xl font-bold text-green-900 mt-1">${{ number_format($stats['total_value'], 2) }}</p>
                                </div>
                                <i class="fas fa-dollar-sign text-4xl text-green-300"></i>
                            </div>

                            <!-- Productos Únicos -->
                            <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                                <div>
                                    <p class="text-xs font-medium text-purple-600 uppercase">Productos Únicos</p>
                                    <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['unique_products'] }}</p>
                                </div>
                                <i class="fas fa-cubes text-4xl text-purple-300"></i>
                            </div>

                            <!-- Órdenes de Compra -->
                            <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-lg">
                                <div>
                                    <p class="text-xs font-medium text-indigo-600 uppercase">Órdenes de Compra</p>
                                    <p class="text-3xl font-bold text-indigo-900 mt-1">{{ $stats['purchase_orders_count'] }}</p>
                                </div>
                                <i class="fas fa-file-invoice text-4xl text-indigo-300"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-gray-100 px-6 py-4">
                            <h3 class="text-lg font-bold text-gray-900">
                                <i class="fas fa-cog mr-2"></i>
                                Acciones
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <!-- Toggle Estado -->
                            <form action="{{ route('sub-warehouses.toggle-status', $subWarehouse) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="w-full px-4 py-3 {{ $subWarehouse->is_active ? 'bg-yellow-100 hover:bg-yellow-200 text-yellow-800' : 'bg-green-100 hover:bg-green-200 text-green-800' }} rounded-lg font-medium transition-colors"
                                        onclick="return confirm('¿Está seguro de cambiar el estado?')">
                                    <i class="fas fa-power-off mr-2"></i>
                                    {{ $subWarehouse->is_active ? 'Desactivar' : 'Activar' }} Sub-Almacén
                                </button>
                            </form>

                            <!-- Eliminar -->
                            <form action="{{ route('sub-warehouses.destroy', $subWarehouse) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full px-4 py-3 bg-red-100 hover:bg-red-200 text-red-800 rounded-lg font-medium transition-colors"
                                        onclick="return confirm('¿Está seguro de eliminar este sub-almacén? Esta acción no se puede deshacer.')">
                                    <i class="fas fa-trash mr-2"></i>
                                    Eliminar Sub-Almacén
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Detalles -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Productos por Estado -->
                    @if(count($productsByStatus) > 0)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-4">
                                <h3 class="text-lg font-bold text-white">
                                    <i class="fas fa-boxes mr-2"></i>
                                    Productos por Estado
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @foreach($productsByStatus as $status => $count)
                                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-2">
                                                {{ ucfirst($status) }}
                                            </p>
                                            <p class="text-3xl font-bold text-gray-900">{{ $count }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Últimas Órdenes de Compra -->
                    @if($subWarehouse->purchaseOrders->count() > 0)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
                                <h3 class="text-lg font-bold text-white">
                                    <i class="fas fa-file-invoice mr-2"></i>
                                    Últimas Órdenes de Compra ({{ $subWarehouse->purchaseOrders->count() }})
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Número</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Proveedor</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($subWarehouse->purchaseOrders->take(10) as $order)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                    {{ $order->order_number }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-700">
                                                    {{ $order->supplier->name }}
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $order->status === 'received' ? 'bg-green-100 text-green-800' : '' }}
                                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
                                                    ${{ number_format($order->total, 2) }}
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <a href="{{ route('purchase-orders.show', $order) }}" 
                                                       class="text-indigo-600 hover:text-indigo-800">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Productos Recientes -->
                    @if($subWarehouse->productUnits->count() > 0)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
                                <h3 class="text-lg font-bold text-white">
                                    <i class="fas fa-cubes mr-2"></i>
                                    Productos Recientes ({{ $subWarehouse->productUnits->count() }})
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Producto</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">EPC/Serial</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Costo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($subWarehouse->productUnits->take(10) as $unit)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $unit->product->code }}</div>
                                                    <div class="text-xs text-gray-500">{{ $unit->product->name }}</div>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="text-xs font-mono text-gray-600">
                                                        {{ $unit->epc_code ?? $unit->serial_number ?? '-' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $unit->status === 'available' ? 'bg-green-100 text-green-800' : '' }}
                                                        {{ $unit->status === 'in_use' ? 'bg-blue-100 text-blue-800' : '' }}
                                                        {{ $unit->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                                        {{ ucfirst($unit->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
                                                    ${{ number_format($unit->acquisition_cost, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            @if($subWarehouse->productUnits->count() > 10)
                                <div class="bg-gray-50 px-6 py-3 text-center">
                                    <p class="text-sm text-gray-600">
                                        Mostrando 10 de {{ $subWarehouse->productUnits->count() }} productos
                                    </p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow-md p-12 text-center">
                            <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                Sin productos asignados
                            </h3>
                            <p class="text-gray-500">
                                Este sub-almacén aún no tiene productos. Los productos se asignarán al recibir órdenes de compra.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>