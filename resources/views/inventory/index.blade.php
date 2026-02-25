<x-app-layout>
    <div x-data="{ currentModal: null }" class="py-6">
        
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                        <i class="fas fa-boxes mr-2 text-indigo-600"></i>
                        Existencias Físicas
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Gestión global de inventario</p>
                </div>
            </div>
        </x-slot>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    
                    <form method="GET" action="{{ route('inventory.index') }}" class="mb-6 pb-6 border-b border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Producto</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="SKU o Nombre..." 
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Almacén</label>
                                <select name="sub_warehouse_id" onchange="this.form.submit()" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                    <option value="">Todos</option>
                                    @foreach($subWarehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Físico</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Comprometido</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Disponible</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($products as $product)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-500">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $product->sku }}</div>
                                                    <div class="text-xs text-gray-500">{{ $product->sku }}</div>
                                                    @if($product->next_expiration)
                                                        @php
                                                            $days = \Carbon\Carbon::parse($product->next_expiration)->diffInDays(now(), false);
                                                        @endphp
                                                        @if($days > -30) <div class="text-xs text-red-600 font-bold mt-1">
                                                                <i class="fas fa-exclamation-circle"></i> Vence: {{ \Carbon\Carbon::parse($product->next_expiration)->format('d/m') }}
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-gray-700">{{ number_format($product->total_stock, 2) }}</td>
                                        <td class="px-6 py-4 text-center text-orange-600 font-semibold">{{ number_format($product->total_reserved, 2) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded-lg {{ ($product->total_stock - $product->total_reserved) > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ number_format($product->total_stock - $product->total_reserved, 2) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="currentModal = {{ $product->id }}" 
                                                    class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition">
                                                <i class="fas fa-search-plus mr-1"></i> Detalles
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-gray-500">Sin resultados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">{{ $products->links() }}</div>
                </div>
            </div>
        </div>

        @foreach($products as $product)
            <div x-show="currentModal === {{ $product->id }}" 
                 style="display: none;"
                 class="fixed inset-0 z-50 overflow-y-auto" 
                 aria-labelledby="modal-title-{{ $product->id }}" role="dialog" aria-modal="true">
                
                <div x-show="currentModal === {{ $product->id }}"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="currentModal = null"></div>

                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="currentModal === {{ $product->id }}"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                        
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-boxes text-indigo-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title-{{ $product->id }}">
                                        Detalle de Existencias: <span class="text-indigo-600">{{ $product->name }}</span>
                                    </h3>
                                    <p class="text-sm text-gray-500">SKU: {{ $product->sku }}</p>
                                </div>
                                <button @click="currentModal = null" class="text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-4 sm:p-6">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Desglose por Lote y Caducidad (FEFO)</h4>
                            <div class="bg-white rounded shadow-sm overflow-hidden border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-600">Lote</th>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-600">Almacén</th>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-600">Caducidad</th>
                                            <th class="px-4 py-2 text-right font-semibold text-gray-600">Cant.</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($product->inventorySummaries as $summary)
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-indigo-600">{{ $summary->lot_number ?: 'S/L' }}</td>
                                                <td class="px-4 py-2">{{ $summary->subWarehouse->name }}</td>
                                                <td class="px-4 py-2">
                                                    @if($summary->expiration_date)
                                                        @php
                                                            $days = now()->diffInDays($summary->expiration_date, false);
                                                            $color = $days < 30 ? 'text-red-600 font-bold' : ($days < 90 ? 'text-yellow-600' : 'text-green-600');
                                                            $icon = $days < 30 ? 'fa-exclamation-triangle' : 'fa-check-circle';
                                                        @endphp
                                                        <span class="{{ $color }}">
                                                            <i class="fas {{ $icon }} mr-1"></i>
                                                            {{ $summary->expiration_date->format('d/m/Y') }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2 text-right font-bold">{{ number_format($summary->quantity_on_hand, 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-gray-50 font-bold">
                                            <td colspan="3" class="px-4 py-2 text-right text-gray-600">Total en Sistema:</td>
                                            <td class="px-4 py-2 text-right text-gray-800">{{ number_format($product->total_stock, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bg-white px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                            <button type="button" @click="currentModal = null" 
                                    class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</x-app-layout>