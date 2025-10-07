<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('purchase-orders.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors duration-150">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Orden de Compra') }} #{{ $purchaseOrder->order_number }}
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                @if($purchaseOrder->canBeEdited())
                    <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" 
                       class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                    </a>
                @endif
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimir
                </button>
            </div>
        </div>
    </x-slot>

    {{-- ⬇️ IMPORTANTE: El x-data debe envolver TODO el contenido incluyendo los modales --}}
    <div x-data="{ showCancelModal: false, showPaymentModal: false, showReceiveModal: false }">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Alertas -->
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <p class="text-green-700 font-medium">{{ session('success') }}</p>
                            <button @click="show = false" class="text-green-400 hover:text-green-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Columna Principal -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Información General -->
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-8">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-2xl font-bold text-white">{{ $purchaseOrder->order_number }}</h3>
                                        <p class="mt-1 text-blue-100">Orden de Compra</p>
                                    </div>
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold shadow-lg
                                        {{ $purchaseOrder->status_color === 'yellow' ? 'bg-yellow-400 text-yellow-900' : '' }}
                                        {{ $purchaseOrder->status_color === 'green' ? 'bg-green-400 text-green-900' : '' }}
                                        {{ $purchaseOrder->status_color === 'blue' ? 'bg-blue-400 text-blue-900' : '' }}
                                        {{ $purchaseOrder->status_color === 'red' ? 'bg-red-400 text-red-900' : '' }}
                                        {{ $purchaseOrder->status_color === 'orange' ? 'bg-orange-400 text-orange-900' : '' }}">
                                        {{ $purchaseOrder->status_label }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-6 border-b border-gray-200">
                                <h4 class="text-lg font-bold text-gray-900 mb-4">Información General</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Proveedor</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $purchaseOrder->supplier->name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Almacén Destino</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $purchaseOrder->destinationWarehouse->name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Fecha de Orden</p>
                                        <p class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->order_date->format('d/m/Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Fecha Esperada</p>
                                        <p class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->expected_date ? $purchaseOrder->expected_date->format('d/m/Y') : 'No definida' }}</p>
                                    </div>
                                    @if($purchaseOrder->received_date)
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Fecha de Recepción</p>
                                            <p class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->received_date->format('d/m/Y') }}</p>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Creado por</p>
                                        <p class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->createdBy->name }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Productos -->
                            {{-- Sección de la tabla de productos --}}
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-bold text-gray-900">Productos Ordenados</h4>
                                    
                                    {{-- Botón Registrar Recepción --}}
                                    @if($hasPendingItems && $purchaseOrder->canBeEdited())
                                        <button 
                                            @click="showReceiveModal = true" 
                                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                            </svg>
                                            Registrar Recepción
                                        </button>
                                    @endif
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Producto</th>
                                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Cant. Solicitada</th>
                                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Cant. Recibida</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Precio Unit.</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($purchaseOrder->items as $item)
                                                <tr>
                                                    <td class="px-4 py-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $item->product_code }}</div>
                                                        <div class="text-xs text-gray-500">{{ $item->product_name }}</div>
                                                    </td>
                                                    <td class="px-4 py-3 text-center text-sm text-gray-900">
                                                        {{ $item->quantity_ordered }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold
                                                            {{ $item->isFullyReceived() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                            {{ $item->quantity_received }} / {{ $item->quantity_ordered }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-right text-sm text-gray-900">
                                                        ${{ number_format($item->unit_price, 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                                                        ${{ number_format($item->subtotal, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Totales -->
                            <div class="p-6 bg-gray-50">
                                <div class="max-w-md ml-auto space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Subtotal:</span>
                                        <span class="text-sm font-semibold text-gray-900">${{ number_format($purchaseOrder->subtotal, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">IVA (16%):</span>
                                        <span class="text-sm font-semibold text-gray-900">${{ number_format($purchaseOrder->tax, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t border-gray-300">
                                        <span class="text-lg font-bold text-gray-900">Total:</span>
                                        <span class="text-lg font-bold text-blue-600">${{ number_format($purchaseOrder->total, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($purchaseOrder->notes)
                                <div class="p-6 border-t border-gray-200">
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Notas</h4>
                                    <p class="text-sm text-gray-700">{{ $purchaseOrder->notes }}</p>
                                </div>
                            @endif

                            @if($purchaseOrder->cancellation_reason)
                                <div class="p-6 border-t border-gray-200 bg-red-50">
                                    <h4 class="text-lg font-bold text-red-900 mb-2">Motivo de Cancelación</h4>
                                    <p class="text-sm text-red-700">{{ $purchaseOrder->cancellation_reason }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Columna Lateral -->
                    <div class="space-y-6">
                        <!-- Resumen -->
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-b">
                                <h4 class="text-lg font-bold text-gray-900">Resumen</h4>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Total Productos:</span>
                                    <span class="font-bold text-gray-900">{{ $purchaseOrder->items->count() }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Total Piezas:</span>
                                    <span class="font-bold text-gray-900">{{ $purchaseOrder->total_items }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Piezas Recibidas:</span>
                                    <span class="font-bold text-gray-900">{{ $purchaseOrder->total_received }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-3 border-t">
                                    <span class="text-sm text-gray-600">Estado de Pago:</span>
                                    @if($purchaseOrder->is_paid)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            Pagado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                            Pendiente
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-b">
                                <h4 class="text-lg font-bold text-gray-900">Acciones</h4>
                            </div>
                            <div class="p-6 space-y-3">
                                @if($purchaseOrder->canBeEdited())
                                    <!-- Marcar como Pagado/No Pagado -->
                                    @if($purchaseOrder->is_paid)
                                        <form action="{{ route('purchase-orders.mark-unpaid', $purchaseOrder) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                Marcar como No Pagado
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('purchase-orders.mark-paid', $purchaseOrder) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Marcar como Pagado
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Cancelar Orden -->
                                    <button @click="showCancelModal = true" 
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Cancelar Orden
                                    </button>
                                @endif

                                <a href="{{ route('purchase-orders.index') }}" 
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Volver al Listado
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ⬇️ MODALES: Ahora están DENTRO del scope de x-data --}}
        
        <!-- Modal Cancelar Orden -->
        <div x-show="showCancelModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="showCancelModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     @click="showCancelModal = false" 
                     class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

                <div x-show="showCancelModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     @click.away="showCancelModal = false"
                     class="relative bg-white rounded-lg max-w-lg w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Cancelar Orden de Compra</h3>
                    <form action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo de Cancelación <span class="text-red-500">*</span>
                            </label>
                            <textarea name="cancellation_reason" 
                                      rows="4"
                                      class="block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                      required></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" 
                                    @click="showCancelModal = false"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                Cerrar
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                                Confirmar Cancelación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal de Recepción -->
        <div x-show="showReceiveModal" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="showReceiveModal" 
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    @click="showReceiveModal = false" 
                    class="fixed inset-0 bg-gray-900 bg-opacity-50"></div>

                <div x-show="showReceiveModal" 
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    @click.away="showReceiveModal = false"
                    class="relative bg-white rounded-2xl shadow-xl max-w-4xl w-full p-6">
                    
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Recepción de Orden: {{ $purchaseOrder->order_number }}
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Almacén de destino: <strong>{{ $purchaseOrder->destinationWarehouse->name }}</strong>
                            </p>
                        </div>
                        <button @click="showReceiveModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('purchase-orders.receive', $purchaseOrder) }}" method="POST">
                        @csrf
                        <div class="overflow-y-auto max-h-96 border rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Producto</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Solicitada</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Recibido</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">A Recibir</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Última Recepción</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($purchaseOrder->items as $item)
                                        @php
                                            $fullyReceived = $item->isFullyReceived();
                                            $pendingQty = $item->pending_quantity;
                                        @endphp
                                        <tr class="{{ $fullyReceived ? 'bg-gray-50' : '' }}">
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                <div class="font-medium">{{ $item->product_code }}</div>
                                                <div class="text-xs text-gray-500">{{ $item->product_name }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-center text-sm text-gray-700">
                                                {{ $item->quantity_ordered }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold
                                                    {{ $fullyReceived ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $item->quantity_received }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="number" 
                                                    name="items[{{ $item->id }}][quantity_received]" 
                                                    min="0" 
                                                    max="{{ $pendingQty }}" 
                                                    value="{{ $pendingQty }}" 
                                                    class="w-20 text-center border border-gray-300 rounded-lg px-2 py-1 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                    {{ $fullyReceived ? 'disabled' : '' }}>
                                            </td>
                                            <td class="px-4 py-3 text-center text-xs text-gray-500">
                                                @if($item->received_at)
                                                    <div>{{ $item->receivedBy ? $item->receivedBy->name : 'Sistema' }}</div>
                                                    <div class="text-gray-400">{{ $item->received_at->format('d/m/Y H:i') }}</div>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notas de Recepción</label>
                            <textarea name="notes" 
                                    rows="2"
                                    class="block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    placeholder="Observaciones sobre esta recepción..."></textarea>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" 
                                    @click="showReceiveModal = false" 
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                                    {{ !$hasPendingItems ? 'disabled' : '' }}>
                                Registrar Recepción
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div> {{-- ⬆️ Cierre del x-data --}}
</x-app-layout>