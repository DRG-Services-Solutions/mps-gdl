<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Órdenes de Compra') }}
            </h2>
            <a href="{{ route('purchase-orders.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i>
                Nueva Orden
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <form method="GET" action="{{ route('purchase-orders.index') }}" class="flex gap-4 flex-wrap">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Buscar por número o proveedor..."
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <select name="status" 
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos los estados</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Parcial</option>
                            <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Recibida</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                    <button type="submit" 
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <i class="fas fa-search mr-2"></i>Filtrar
                    </button>
                    @if(request()->anyFilled(['search', 'status']))
                        <a href="{{ route('purchase-orders.index') }}" 
                           class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            <i class="fas fa-times mr-2"></i>Limpiar
                        </a>
                    @endif
                </form>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Lista de Órdenes con Acordeón -->
            <div class="space-y-4">
                @forelse($purchaseOrders as $order)
                    <div x-data="{ open: false }" 
                         class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                        
                        <!-- Header de la Orden (Clickeable) -->
                        <div @click="open = !open" 
                             class="px-6 py-4 cursor-pointer hover:bg-gray-50 transition-colors duration-150">
                            <div class="flex items-center justify-between">
                                <!-- Info Principal -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-4">
                                        <!-- Número de Orden -->
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $order->order_number }}
                                        </h3>
                                        
                                        <!-- Status Badge -->
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                            @if($order->status === 'pending')  text-yellow-800
                                            @elseif($order->status === 'partial')  
                                            @elseif($order->status === 'received') bg-green-100
                                            @elseif($order->status === 'cancelled')  
                                            @else  
                                            @endif">
                                            {{ $order->status_label }}
                                        </span>

                                        <!-- Indicador de Recepciones -->
                                        @if($order->receipts_count > 0)
                                            <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-semibold">
                                                <i class="fas fa-box-open mr-1"></i>
                                                {{ $order->receipts_count }} {{ $order->receipts_count === 1 ? 'Recepción' : 'Recepciones' }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Info Secundaria -->
                                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                        <span>
                                            <i class="fas fa-building text-gray-400 mr-1"></i>
                                            {{ $order->supplier->name }}
                                        </span>
                                        <span>
                                            <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                            {{ $order->order_date->format('d/m/Y') }}
                                        </span>
                                        <span>
                                            <i class="fas fa-warehouse text-gray-400 mr-1"></i>
                                            {{ $order->destinationWarehouse->name }}
                                        </span>
                                        <span class="font-semibold text-gray-900">
                                            <i class="fas fa-dollar-sign text-gray-400 mr-1"></i>
                                            ${{ number_format($order->total, 2) }}
                                        </span>
                                    </div>

                                    <!-- Progreso de Recepción -->
                                    @if($order->status !== 'cancelled')
                                        <div class="mt-3">
                                            <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                                <span>Progreso de recepción</span>
                                                <span class="font-semibold">{{ number_format($order->receipt_progress, 1) }}%</span>
                                            </div>

                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="h-2 rounded-full transition-all duration-300
                                                    @if($order->receipt_progress < 50) bg-red-500
                                                    @elseif($order->receipt_progress < 100) bg-yellow-500
                                                    @else bg-green-500
                                                    @endif"
                                                    style="width: {{ $order->receipt_progress }}%">
                                                </div>
                                            </div>

                                        </div>
                                    @endif
                                </div>

                                <!-- Botones de Acción -->
                                <div class="flex items-center gap-2 ml-4">
                                    <a href="{{ route('purchase-orders.show', $order) }}" 
                                       class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm"
                                       @click.stop>
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                    
                                    @if($order->canBeReceived())
                                        <a href="{{ route('purchase-orders.show', $order) }}#receive" 
                                           class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
                                           @click.stop>
                                            <i class="fas fa-box-open mr-1"></i>Recibir
                                        </a>
                                    @endif

                                    <!-- Indicador de Acordeón -->
                                    <div class="ml-2 text-gray-400 transition-transform duration-200"
                                         :class="{ 'rotate-180': open }">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido del Acordeón (Recepciones) -->
                        <div x-show="open" 
                             x-collapse
                             class="border-t border-gray-200 bg-gray-50">
                            <div class="px-6 py-4">
                                <!-- Items de la Orden -->
                                <div class="mb-6">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-list-ul mr-2 text-gray-400"></i>
                                        Productos Ordenados
                                    </h4>
                                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Ordenado</th>
                                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Recibido</th>
                                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Pendiente</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                @foreach($order->items as $item)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-sm">
                                                            <div class="font-medium text-gray-900">{{ $item->product_code }}</div>
                                                            <div class="text-gray-500 text-xs">{{ $item->product_name }}</div>
                                                        </td>
                                                        <td class="px-4 py-3 text-center text-sm text-gray-900">
                                                            {{ $item->quantity_ordered }}
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                @if($item->quantity_received === 0)  
                                                                @elseif($item->isFullyReceived())  
                                                                @else  text-blue-800
                                                                @endif">
                                                                {{ $item->quantity_received }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-center text-sm">
                                                            <span class="text-gray-600 font-medium">
                                                                {{ $item->pending_quantity }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                                            ${{ number_format($item->subtotal, 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Historial de Recepciones -->
                                @if($order->receipts->count() > 0)
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                            <i class="fas fa-history mr-2 text-gray-400"></i>
                                            Historial de Recepciones ({{ $order->receipts->count() }})
                                        </h4>
                                        <div class="space-y-3">
                                            @foreach($order->receipts->sortByDesc('received_at') as $receipt)
                                                <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow duration-200">
                                                    <div class="flex items-start justify-between">
                                                        <!-- Info de la Recepción -->
                                                        <div class="flex-1">
                                                            <div class="flex items-center gap-3 mb-2">
                                                                <span class="font-semibold text-gray-900">
                                                                    <i class="fas fa-receipt text-indigo-600 mr-1"></i>
                                                                    {{ $receipt->receipt_number }}
                                                                </span>
                                                                <span class="px-2 py-1 rounded text-xs font-semibold
                                                                    @if($receipt->status === 'pending')  
                                                                    @elseif($receipt->status === 'partial')  
                                                                    @elseif($receipt->status === 'completed')  
                                                                    @elseif($receipt->status === 'with_issues')  
                                                                    @else  text-gray-800
                                                                    @endif">
                                                                    {{ ucfirst($receipt->status) }}
                                                                </span>
                                                            </div>
                                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600">
                                                                <div>
                                                                    <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                                                    <span class="font-medium">{{ $receipt->received_at->format('d/m/Y H:i') }}</span>
                                                                </div>
                                                                <div>
                                                                    <i class="fas fa-user text-gray-400 mr-1"></i>
                                                                    {{ $receipt->receivedBy->name }}
                                                                </div>
                                                                <div>
                                                                    <i class="fas fa-warehouse text-gray-400 mr-1"></i>
                                                                    {{ $receipt->warehouse->full_location ?? $receipt->warehouse->area }}
                                                                </div>
                                                                <div class="font-semibold text-indigo-600">
                                                                    <i class="fas fa-box text-gray-400 mr-1"></i>
                                                                    {{ $receipt->items->sum('quantity_received') }} unidades
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Items Recibidos -->
                                                            <div class="mt-3 pl-4 border-l-2 border-indigo-200">
                                                                @foreach($receipt->items as $receiptItem)
                                                                    <div class="text-xs text-gray-600 py-1">
                                                                        <span class="font-medium text-gray-700">{{ $receiptItem->product->code }}</span>: 
                                                                        <span class="text-indigo-600 font-semibold">{{ $receiptItem->quantity_received }}</span> unidades
                                                                        
                                                                        @if($receiptItem->batch_number)
                                                                            <span class="ml-2 text-gray-500">
                                                                                <i class="fas fa-tag text-xs"></i> Lote: <span class="font-medium">{{ $receiptItem->batch_number }}</span>
                                                                            </span>
                                                                        @endif
                                                                        
                                                                        @if($receiptItem->expiry_date)
                                                                            <span class="ml-2 text-gray-500">
                                                                                <i class="fas fa-calendar text-xs"></i> Caduca: <span class="font-medium">{{ $receiptItem->expiry_date->format('d/m/Y') }}</span>
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>

                                                            @if($receipt->notes)
                                                                <div class="mt-2 text-xs text-gray-500 italic">
                                                                    <i class="fas fa-sticky-note text-gray-400 mr-1"></i>
                                                                    {{ $receipt->notes }}
                                                                </div>
                                                            @endif

                                                            <!-- Información de Factura -->
                                                            <div class="mt-3 flex flex-wrap gap-3">
                                                                @if($receipt->invoice_number)
                                                                    <span class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-md text-xs font-medium">
                                                                        <i class="fas fa-file-invoice mr-1.5"></i>
                                                                        Factura: {{ $receipt->invoice_number }}
                                                                    </span>
                                                                @endif
                                                                
                                                                @if($receipt->invoice_file)
                                                                    <a href="{{ asset('storage/' . $receipt->invoice_file) }}" 
                                                                    target="_blank"
                                                                    class="inline-flex items-center px-3 py-1.5 bg-green-50 text-green-700 rounded-md hover:bg-green-100 text-xs font-medium transition-colors">
                                                                        <i class="fas fa-download mr-1.5"></i>
                                                                        Descargar Factura
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Tiempo desde la recepción -->
                                                        <div class="text-right text-xs text-gray-500 ml-4">
                                                            {{ $receipt->received_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                                        <p class="text-sm">No hay recepciones registradas aún</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                        <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">No hay órdenes de compra</p>
                        <a href="{{ route('purchase-orders.create') }}" 
                           class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            <i class="fas fa-plus mr-2"></i>
                            Crear Primera Orden
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Paginación -->
            <div class="mt-6">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>