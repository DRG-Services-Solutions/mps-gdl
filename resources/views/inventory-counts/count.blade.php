<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('inventory-counts.show', $inventoryCount) }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                        Conteo: {{ $inventoryCount->count_number }}
                    </h2>
                    <p class="text-xs text-gray-500">{{ $inventoryCount->legal_entities_names }}</p>
                </div>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                En Progreso
            </span>
        </div>
    </x-slot>

    <div class="py-4 md:py-8" x-data="countingApp()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Barra de Progreso y Resumen --}}
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progreso del Conteo</span>
                    <span class="text-sm font-bold text-blue-600" x-text="progressText"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" 
                         :style="'width: ' + progressPercentage + '%'"></div>
                </div>
                <div class="flex justify-between mt-3 text-xs">
                    <span class="text-gray-500">
                        <span class="inline-block w-3 h-3 bg-gray-300 rounded mr-1"></span>
                        Pendientes: <strong x-text="stats.pending"></strong>
                    </span>
                    <span class="text-green-600">
                        <span class="inline-block w-3 h-3 bg-green-500 rounded mr-1"></span>
                        Encontrados: <strong x-text="stats.found"></strong>
                    </span>
                    <span class="text-red-600">
                        <span class="inline-block w-3 h-3 bg-red-500 rounded mr-1"></span>
                        Faltantes: <strong x-text="stats.missing"></strong>
                    </span>
                    <span class="text-blue-600">
                        <span class="inline-block w-3 h-3 bg-blue-500 rounded mr-1"></span>
                        Sobrantes: <strong x-text="stats.surplus"></strong>
                    </span>
                </div>
            </div>

            {{-- Área de Escaneo --}}
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg p-4 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-white font-semibold">Escanear Producto</h3>
                    <span class="text-blue-200 text-sm">{{ $inventoryCount->method_label }}</span>
                </div>
                
                {{-- Input de Escaneo --}}
                <div class="relative">
                    <input type="text" 
                           x-ref="scanInput"
                           x-model="scanCode"
                           @keydown.enter="processScan()"
                           @focus="isScanFocused = true"
                           @blur="isScanFocused = false"
                           class="w-full px-4 py-4 text-lg rounded-lg border-0 focus:ring-4 focus:ring-blue-300 placeholder-gray-400"
                           placeholder="Escanea EPC, Serial o Código de Barras..."
                           autofocus
                           autocomplete="off">
                    <button @click="processScan()" 
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition-colors"
                            :disabled="isProcessing || !scanCode">
                        <span x-show="!isProcessing">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <span x-show="isProcessing">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>

                {{-- Mensaje de último escaneo --}}
                <div x-show="lastScanMessage" x-transition class="mt-3">
                    <div :class="{
                        'bg-green-100 text-green-800 border-green-300': lastScanSuccess,
                        'bg-yellow-100 text-yellow-800 border-yellow-300': lastScanAction === 'surplus',
                        'bg-red-100 text-red-800 border-red-300': !lastScanSuccess && lastScanAction !== 'surplus'
                    }" class="px-4 py-2 rounded-lg border text-sm font-medium">
                        <span x-text="lastScanMessage"></span>
                    </div>
                </div>
            </div>

            {{-- Filtros Rápidos --}}
            <div class="bg-white rounded-xl shadow-sm p-3 mb-4">
                <div class="flex flex-wrap gap-2">
                    <button @click="filterStatus = 'all'" 
                            :class="filterStatus === 'all' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                        Todos (<span x-text="items.length"></span>)
                    </button>
                    <button @click="filterStatus = 'pending'" 
                            :class="filterStatus === 'pending' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                        Pendientes (<span x-text="stats.pending"></span>)
                    </button>
                    <button @click="filterStatus = 'found'" 
                            :class="filterStatus === 'found' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-700'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                        Encontrados (<span x-text="stats.found"></span>)
                    </button>
                    <button @click="filterStatus = 'missing'" 
                            :class="filterStatus === 'missing' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                        Faltantes (<span x-text="stats.missing"></span>)
                    </button>
                    <button @click="filterStatus = 'surplus'" 
                            :class="filterStatus === 'surplus' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                        Sobrantes (<span x-text="stats.surplus"></span>)
                    </button>
                </div>
            </div>

            {{-- Lista de Unidades (ProductUnits) --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="divide-y divide-gray-200">
                    <template x-for="item in filteredItems" :key="item.id">
                        <div class="p-4 hover:bg-gray-50 transition-colors"
                             :class="{
                                 'bg-green-50': item.status === 'found' || item.status === 'matched',
                                 'bg-red-50': item.status === 'missing',
                                 'bg-blue-50': item.status === 'surplus',
                                 'bg-orange-50': item.status === 'damaged' || item.status === 'expired'
                             }">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        {{-- Indicador de Estado --}}
                                        <span class="flex-shrink-0">
                                            <template x-if="item.status === 'pending'">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-200">
                                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </span>
                                            </template>
                                            <template x-if="item.status === 'found' || item.status === 'matched'">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-500">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </span>
                                            </template>
                                            <template x-if="item.status === 'surplus'">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-500">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    </svg>
                                                </span>
                                            </template>
                                            <template x-if="item.status === 'missing'">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-500">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </span>
                                            </template>
                                        </span>
                                        
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-gray-900 truncate" x-text="item.product_code"></p>
                                            <p class="text-xs text-gray-500 truncate" x-text="item.product_name"></p>
                                        </div>
                                    </div>

                                    {{-- Identificadores (EPC/Serial) --}}
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <template x-if="item.expected_epc">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-indigo-100 text-indigo-700">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0"/>
                                                </svg>
                                                <span x-text="item.expected_epc.substring(0, 20) + '...'"></span>
                                            </span>
                                        </template>
                                        <template x-if="item.expected_serial && !item.expected_epc">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-purple-100 text-purple-700">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                                </svg>
                                                <span x-text="item.expected_serial"></span>
                                            </span>
                                        </template>
                                        <template x-if="item.expected_batch">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                                                Lote: <span x-text="item.expected_batch" class="ml-1"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                {{-- Estado y Acciones --}}
                                <div class="ml-4 text-right">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                          :class="{
                                              'bg-gray-100 text-gray-600': item.status === 'pending',
                                              'bg-green-100 text-green-800': item.status === 'found' || item.status === 'matched',
                                              'bg-red-100 text-red-800': item.status === 'missing',
                                              'bg-blue-100 text-blue-800': item.status === 'surplus',
                                              'bg-orange-100 text-orange-800': item.status === 'damaged' || item.status === 'expired'
                                          }"
                                          x-text="item.status_label">
                                    </span>
                                </div>
                            </div>

                            {{-- Acciones del Item --}}
                            <div class="mt-3 flex items-center justify-end space-x-2">
                                <button @click="markNotFound(item)" 
                                        x-show="item.status === 'pending'"
                                        class="text-xs px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded transition-colors">
                                    Marcar Faltante
                                </button>
                                <button @click="recountItem(item)" 
                                        x-show="item.status !== 'pending'"
                                        class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors">
                                    Recontar
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Empty State --}}
                    <div x-show="filteredItems.length === 0" class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No hay unidades con este filtro</p>
                    </div>
                </div>
            </div>

            {{-- Botón Finalizar (Fijo en móvil) --}}
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg p-4 md:relative md:mt-4 md:border-0 md:shadow-none md:bg-transparent md:p-0">
                <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                    <div class="text-sm">
                        <span class="text-gray-500">Pendientes: </span>
                        <span class="font-bold text-gray-900" x-text="stats.pending"></span>
                    </div>
                    <form action="{{ route('inventory-counts.complete', $inventoryCount) }}" method="POST" class="flex-1 md:flex-none">
                        @csrf
                        <button type="submit" 
                                class="w-full md:w-auto px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors"
                                onclick="return confirm('¿Finalizar el conteo? Los items pendientes se marcarán como faltantes.')">
                            Finalizar Conteo
                        </button>
                    </form>
                </div>
            </div>

            {{-- Espaciador para el botón fijo en móvil --}}
            <div class="h-20 md:hidden"></div>
        </div>
    </div>

    @php
        $itemsJson = $items->map(function($item) {
            return [
                'id' => $item->id,
                'product_code' => $item->product_code,
                'product_name' => $item->product_name,
                'expected_epc' => $item->expected_epc,
                'expected_serial' => $item->expected_serial,
                'expected_batch' => $item->expected_batch,
                'expected_quantity' => $item->expected_quantity,
                'counted_quantity' => $item->counted_quantity,
                'difference' => $item->difference,
                'status' => $item->status,
                'status_label' => $item->status_label,
                'status_color' => $item->status_color,
            ];
        })->values();
    @endphp

    @push('scripts')
    <script>
    function countingApp() {
        return {
            items: @json($itemsJson),
            scanCode: '',
            isProcessing: false,
            isScanFocused: false,
            lastScanMessage: '',
            lastScanSuccess: false,
            lastScanAction: '',
            filterStatus: 'all',

            get stats() {
                return {
                    pending: this.items.filter(i => i.status === 'pending').length,
                    found: this.items.filter(i => ['found', 'matched'].includes(i.status)).length,
                    missing: this.items.filter(i => i.status === 'missing').length,
                    surplus: this.items.filter(i => i.status === 'surplus').length,
                };
            },

            get progressPercentage() {
                const total = this.items.length;
                const counted = this.items.filter(i => i.status !== 'pending').length;
                return total > 0 ? Math.round((counted / total) * 100) : 0;
            },

            get progressText() {
                const total = this.items.length;
                const counted = this.items.filter(i => i.status !== 'pending').length;
                return `${counted} / ${total} (${this.progressPercentage}%)`;
            },

            get filteredItems() {
                if (this.filterStatus === 'all') return this.items;
                if (this.filterStatus === 'pending') return this.items.filter(i => i.status === 'pending');
                if (this.filterStatus === 'found') return this.items.filter(i => ['found', 'matched'].includes(i.status));
                if (this.filterStatus === 'missing') return this.items.filter(i => i.status === 'missing');
                if (this.filterStatus === 'surplus') return this.items.filter(i => i.status === 'surplus');
                return this.items;
            },

            async processScan() {
                if (!this.scanCode.trim() || this.isProcessing) return;

                this.isProcessing = true;
                this.lastScanMessage = '';
                this.lastScanAction = '';

                try {
                    const response = await fetch('{{ route("inventory-counts.process-scan", $inventoryCount) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            scan_code: this.scanCode.trim(),
                            scan_type: '{{ in_array($inventoryCount->method, ["rfid_bulk", "rfid_handheld"]) ? "rfid" : "barcode" }}'
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.lastScanSuccess = data.action === 'found';
                        this.lastScanAction = data.action;
                        this.lastScanMessage = data.message;
                        
                        // Actualizar o agregar item en la lista
                        const index = this.items.findIndex(i => i.id === data.item.id);
                        if (index !== -1) {
                            this.items[index] = { ...this.items[index], ...data.item };
                        } else {
                            // Item nuevo (sobrante)
                            this.items.unshift(data.item);
                        }

                        this.playSound(data.action === 'found' ? 'success' : 'warning');
                    } else {
                        this.lastScanSuccess = false;
                        this.lastScanAction = 'error';
                        this.lastScanMessage = data.message;
                        this.playSound('error');
                    }
                } catch (error) {
                    this.lastScanSuccess = false;
                    this.lastScanAction = 'error';
                    this.lastScanMessage = 'Error de conexión';
                    this.playSound('error');
                } finally {
                    this.isProcessing = false;
                    this.scanCode = '';
                    this.$refs.scanInput.focus();

                    setTimeout(() => {
                        this.lastScanMessage = '';
                    }, 4000);
                }
            },

            async markNotFound(item) {
                if (!confirm(`¿Marcar "${item.product_code}" como FALTANTE?`)) return;

                try {
                    const response = await fetch(`{{ url('inventory-counts') }}/{{ $inventoryCount->id }}/items/${item.id}/not-found`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        const index = this.items.findIndex(i => i.id === item.id);
                        if (index !== -1) {
                            this.items[index] = { ...this.items[index], ...data.item };
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            },

            async recountItem(item) {
                if (!confirm(`¿Recontar "${item.product_code}"?`)) return;

                try {
                    const response = await fetch(`{{ url('inventory-counts') }}/{{ $inventoryCount->id }}/items/${item.id}/recount`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        const index = this.items.findIndex(i => i.id === item.id);
                        if (index !== -1) {
                            this.items[index].status = 'pending';
                            this.items[index].status_label = 'Pendiente';
                            this.items[index].counted_quantity = 0;
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            },

            playSound(type) {
                if (navigator.vibrate) {
                    if (type === 'success') {
                        navigator.vibrate(100);
                    } else if (type === 'warning') {
                        navigator.vibrate([100, 50, 100]);
                    } else {
                        navigator.vibrate([100, 50, 100, 50, 100]);
                    }
                }
            },

            init() {
                this.$refs.scanInput.focus();

                document.addEventListener('click', (e) => {
                    if (!e.target.closest('input') && !e.target.closest('button') && !e.target.closest('a')) {
                        setTimeout(() => this.$refs.scanInput.focus(), 100);
                    }
                });
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
