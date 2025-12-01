<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('legal-entities.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ $legalEntity->name }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Detalles de la razón social') }}
                    </p>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <a href="{{ route('legal-entities.edit', $legalEntity) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    {{ __('Editar') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Tarjetas de Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                
                <!-- Total Inventario -->
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-indigo-100 text-sm font-medium mb-1">{{ __('Valor Total Inventario') }}</p>
                            <h3 class="text-3xl font-bold">
                                ${{ number_format($totalInventoryValue, 2) }}
                            </h3>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-dollar-sign text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Unidades -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium mb-1">{{ __('Unidades en Inventario') }}</p>
                            <h3 class="text-3xl font-bold">
                                {{ number_format($totalUnits) }}
                            </h3>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-cubes text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Órdenes de Compra -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium mb-1">{{ __('Órdenes de Compra') }}</p>
                            <h3 class="text-3xl font-bold">
                                {{ $legalEntity->purchaseOrders->count() }}
                            </h3>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-file-invoice-dollar text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Columna Izquierda: Información General -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Información General -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                                {{ __('Información General') }}
                            </h3>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <!-- Estado -->
                            <div>
                                <label class="text-sm font-medium text-gray-500">{{ __('Estado') }}</label>
                                <div class="mt-1">
                                    @if ($legalEntity->is_active)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            {{ __('Activa') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-times-circle mr-2"></i>
                                            {{ __('Inactiva') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Razón Social -->
                            <div>
                                <label class="text-sm font-medium text-gray-500">{{ __('Razón Social') }}</label>
                                <p class="mt-1 text-sm text-gray-900 font-medium">{{ $legalEntity->razon_social }}</p>
                            </div>

                            <!-- RFC -->
                            <div>
                                <label class="text-sm font-medium text-gray-500">{{ __('RFC') }}</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono font-semibold">{{ $legalEntity->rfc }}</p>
                            </div>

                            <!-- Dirección -->
                            @if($legalEntity->address)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">{{ __('Dirección') }}</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $legalEntity->address }}</p>
                                </div>
                            @endif

                            <!-- Teléfono -->
                            @if($legalEntity->phone)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">{{ __('Teléfono') }}</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <a href="tel:{{ $legalEntity->phone }}" class="text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-phone mr-1"></i>
                                            {{ $legalEntity->phone }}
                                        </a>
                                    </p>
                                </div>
                            @endif

                            <!-- Email -->
                            @if($legalEntity->email)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">{{ __('Email') }}</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <a href="mailto:{{ $legalEntity->email }}" class="text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-envelope mr-1"></i>
                                            {{ $legalEntity->email }}
                                        </a>
                                    </p>
                                </div>
                            @endif

                            <!-- Notas -->
                            @if($legalEntity->notes)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">{{ __('Notas') }}</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $legalEntity->notes }}</p>
                                </div>
                            @endif

                            <!-- Fechas -->
                            <div class="pt-4 border-t border-gray-100">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ __('Creado') }}</span>
                                        <span>{{ $legalEntity->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ __('Actualizado') }}</span>
                                        <span>{{ $legalEntity->updated_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Productos y Órdenes -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Órdenes de Compra Recientes -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-file-invoice-dollar text-indigo-600 mr-2"></i>
                                    {{ __('Órdenes de Compra Recientes') }}
                                </h3>
                                @if($legalEntity->purchaseOrders->count() > 0)
                                    <a href="{{ route('purchase-orders.index') }}?legal_entity={{ $legalEntity->id }}" 
                                       class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                        {{ __('Ver todas') }} →
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            @if($legalEntity->purchaseOrders->count() > 0)
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Número') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Proveedor') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Fecha') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Total') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Estado') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($legalEntity->purchaseOrders->take(10) as $order)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <a href="{{ route('purchase-orders.show', $order) }}" 
                                                       class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                                        {{ $order->order_number }}
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $order->supplier->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        ${{ number_format($order->total ?? 0, 2) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'received' => 'bg-green-100 text-green-800',
                                                            'cancelled' => 'bg-red-100 text-red-800',
                                                        ];
                                                        $statusLabels = [
                                                            'pending' => 'Pendiente',
                                                            'received' => 'Recibida',
                                                            'cancelled' => 'Cancelada',
                                                        ];
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="px-6 py-12 text-center">
                                    <i class="fas fa-file-invoice text-gray-300 text-5xl mb-4"></i>
                                    <p class="text-gray-500 text-sm">{{ __('No hay órdenes de compra registradas para esta razón social') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Productos en Inventario -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-boxes text-indigo-600 mr-2"></i>
                                    {{ __('Productos en Inventario') }}
                                </h3>
                                @if($legalEntity->productUnits->count() > 0)
                                    <span class="text-sm text-gray-600">
                                        {{ $legalEntity->productUnits->count() }} {{ __('unidades') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            @if($legalEntity->productUnits->count() > 0)
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Producto') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('EPC') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Ubicación') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Costo') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Estado') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($legalEntity->productUnits->take(15) as $unit)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center">
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $unit->product->name ?? 'N/A' }}
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                {{ $unit->product->code ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900 font-mono">{{ $unit->epc_code }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $unit->storageLocation->name ?? 'N/A' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        ${{ number_format($unit->acquisition_cost ?? 0, 2) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $statusColors = [
                                                            'available' => 'bg-green-100 text-green-800',
                                                            'in_use' => 'bg-blue-100 text-blue-800',
                                                            'maintenance' => 'bg-yellow-100 text-yellow-800',
                                                            'retired' => 'bg-gray-100 text-gray-800',
                                                        ];
                                                        $statusLabels = [
                                                            'available' => 'Disponible',
                                                            'in_use' => 'En Uso',
                                                            'maintenance' => 'Mantenimiento',
                                                            'retired' => 'Retirado',
                                                        ];
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$unit->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                        {{ $statusLabels[$unit->status] ?? $unit->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                @if($legalEntity->productUnits->count() > 15)
                                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-center">
                                        <a href="{{ route('product-units.index') }}?legal_entity={{ $legalEntity->id }}" 
                                           class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                            {{ __('Ver todas las unidades') }} ({{ $legalEntity->productUnits->count() }} {{ __('total') }}) →
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="px-6 py-12 text-center">
                                    <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                                    <p class="text-gray-500 text-sm">{{ __('No hay productos en inventario para esta razón social') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Movimientos Recientes -->
                    @if($legalEntity->inventoryMovements->count() > 0)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-exchange-alt text-indigo-600 mr-2"></i>
                                    {{ __('Movimientos Recientes') }}
                                </h3>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Fecha') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Tipo') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Producto') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Cantidad') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($legalEntity->inventoryMovements->take(10) as $movement)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $movement->created_at->format('d/m/Y H:i') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $movement->type }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900">{{ $movement->product->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                                    {{ $movement->quantity }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>