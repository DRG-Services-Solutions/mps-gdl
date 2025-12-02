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

                <!-- Columna Derecha: Sub-Almacenes, Productos y Órdenes -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Sub-Almacenes Virtuales -->
                    <div x-data="{
                        showCreateModal: false,
                        showEditModal: false,
                        editingWarehouse: null,
                        openCreateModal() {
                            this.showCreateModal = true;
                        },
                        openEditModal(warehouse) {
                            this.editingWarehouse = warehouse;
                            this.showEditModal = true;
                        }
                    }">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-layer-group text-indigo-600 mr-2"></i>
                                        {{ __('Sub-Almacenes Virtuales') }}
                                    </h3>
                                    <button @click="openCreateModal()" 
                                            type="button"
                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        {{ __('Nuevo') }}
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('Organiza virtualmente tu inventario en categorías o departamentos') }}
                                </p>
                            </div>

                            <div class="p-6">
                                @if($legalEntity->subWarehouses->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($legalEntity->subWarehouses as $subWarehouse)
                                            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                                                <div class="flex items-start justify-between mb-3">
                                                    <div class="flex-1">
                                                        <h4 class="text-sm font-semibold text-gray-900 flex items-center">
                                                            <i class="fas fa-warehouse text-indigo-500 mr-2"></i>
                                                            {{ $subWarehouse->name }}
                                                        </h4>
                                                        @if($subWarehouse->description)
                                                            <p class="mt-1 text-xs text-gray-600">{{ $subWarehouse->description }}</p>
                                                        @endif
                                                    </div>
                                                    
                                                    @if($subWarehouse->is_active)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            Activo
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                            <i class="fas fa-times-circle mr-1"></i>
                                                            Inactivo
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Estadísticas -->
                                                <div class="grid grid-cols-2 gap-2 mb-3">
                                                    <div class="bg-white rounded p-2 text-center">
                                                        <div class="text-xs text-gray-500">Unidades</div>
                                                        <div class="text-lg font-bold text-indigo-600">
                                                            {{ number_format($subWarehouse->getTotalUnits()) }}
                                                        </div>
                                                    </div>
                                                    <div class="bg-white rounded p-2 text-center">
                                                        <div class="text-xs text-gray-500">Valor</div>
                                                        <div class="text-lg font-bold text-purple-600">
                                                            ${{ number_format($subWarehouse->getTotalValue(), 0) }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Acciones -->
                                                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-gray-200">
                                                    <button @click="openEditModal({{ $subWarehouse->toJson() }})"
                                                            type="button"
                                                            class="text-blue-600 hover:text-blue-800 transition-colors duration-200"
                                                            title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <form action="{{ route('sub-warehouses.toggle-status', $subWarehouse) }}" 
                                                          method="POST" 
                                                          class="inline"
                                                          onsubmit="return confirm('¿Cambiar el estado de este sub-almacén?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" 
                                                                class="text-yellow-600 hover:text-yellow-800 transition-colors duration-200"
                                                                title="Cambiar estado">
                                                            <i class="fas fa-power-off"></i>
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('sub-warehouses.destroy', $subWarehouse) }}" 
                                                          method="POST" 
                                                          class="inline"
                                                          onsubmit="return confirm('¿Estás seguro de eliminar este sub-almacén?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="text-red-600 hover:text-red-800 transition-colors duration-200"
                                                                title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-12">
                                        <i class="fas fa-layer-group text-gray-300 text-5xl mb-4"></i>
                                        <p class="text-gray-500 mb-4">No hay sub-almacenes creados</p>
                                        <button @click="openCreateModal()"
                                                type="button"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i>
                                            Crear primer sub-almacén
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Modal Crear -->
                        <div x-show="showCreateModal" 
                             x-cloak
                             @click.self="showCreateModal = false"
                             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-500 bg-opacity-75">
                            
                            <div x-show="showCreateModal"
                                 x-transition:enter="ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-4"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 @click.stop
                                 class="bg-white rounded-lg shadow-xl max-w-lg w-full">
                                
                                <form action="{{ route('sub-warehouses.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="legal_entity_id" value="{{ $legalEntity->id }}">

                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-900">Crear Sub-Almacén Virtual</h3>
                                        <p class="mt-1 text-sm text-gray-600">Crea una categoría virtual para organizar tu inventario</p>
                                    </div>

                                    <div class="px-6 py-4 space-y-4">
                                        <div>
                                            <label for="create_name" class="block text-sm font-medium text-gray-700 mb-2">
                                                Nombre <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" name="name" id="create_name" required
                                                   placeholder="Ej: Instrumentos Rodilla, Consumibles..."
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>

                                        <div>
                                            <label for="create_description" class="block text-sm font-medium text-gray-700 mb-2">
                                                Descripción (opcional)
                                            </label>
                                            <textarea name="description" id="create_description" rows="3"
                                                      placeholder="Descripción adicional..."
                                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                                        <button type="button" @click="showCreateModal = false"
                                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                            Cancelar
                                        </button>
                                        <button type="submit" 
                                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">
                                            <i class="fas fa-save mr-2"></i>Crear
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Modal Editar -->
                        <div x-show="showEditModal" 
                             x-cloak
                             @click.self="showEditModal = false"
                             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-500 bg-opacity-75">
                            
                            <div x-show="showEditModal"
                                 x-transition:enter="ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-4"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 @click.stop
                                 class="bg-white rounded-lg shadow-xl max-w-lg w-full"
                                 x-show="editingWarehouse">
                                
                                <form :action="`{{ url('sub-warehouses') }}/${editingWarehouse?.id}`" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-900">Editar Sub-Almacén Virtual</h3>
                                    </div>

                                    <div class="px-6 py-4 space-y-4">
                                        <div>
                                            <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">
                                                Nombre <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" name="name" id="edit_name" :value="editingWarehouse?.name" required
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>

                                        <div>
                                            <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-2">
                                                Descripción (opcional)
                                            </label>
                                            <textarea name="description" id="edit_description" rows="3" x-text="editingWarehouse?.description"
                                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                                        <button type="button" @click="showEditModal = false"
                                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                            Cancelar
                                        </button>
                                        <button type="submit" 
                                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                                            <i class="fas fa-save mr-2"></i>Guardar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <style>
                            [x-cloak] { display: none !important; }
                        </style>
                    </div>
                    
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
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Número') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Proveedor') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Fecha') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Estado') }}</th>
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
                                    <p class="text-gray-500 text-sm">{{ __('No hay órdenes de compra registradas') }}</p>
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
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Producto') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('EPC') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Ubicación') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Costo') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Estado') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($legalEntity->productUnits->take(15) as $unit)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $unit->product->name ?? 'N/A' }}</div>
                                                    <div class="text-sm text-gray-500">{{ $unit->product->code ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900 font-mono">{{ $unit->epc_code }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $unit->storageLocation->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-semibold text-gray-900">${{ number_format($unit->acquisition_cost ?? 0, 2) }}</div>
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
                                    <p class="text-gray-500 text-sm">{{ __('No hay productos en inventario') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>