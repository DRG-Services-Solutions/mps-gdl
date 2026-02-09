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
                    <p class="text-xs text-gray-500">{{ $inventoryCount->legalEntity->name }}</p>
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
                        Coinciden: <strong x-text="stats.matched"></strong>
                    </span>
                    <span class="text-red-600">
                        <span class="inline-block w-3 h-3 bg-red-500 rounded mr-1"></span>
                        Discrepancias: <strong x-text="stats.discrepancies"></strong>
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
                           placeholder="Escanea código de barras o RFID..."
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
                        'bg-red-100 text-red-800 border-red-300': !lastScanSuccess
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
                    <button @click="filterStatus = 'matched'" 
                            :class="filterStatus === 'matched' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-700'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                        Coinciden (<span x-text="stats.matched"></span>)
                    </button>
                    <button @click="filterStatus = 'discrepancy'" 
                            :class="filterStatus === 'discrepancy' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                        Discrepancias (<span x-text="stats.discrepancies"></span>)
                    </button>
                </div>
            </div>

            {{-- Lista de Productos --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="divide-y divide-gray-200">
                    <template x-for="item in filteredItems" :key="item.id">
                        <div class="p-4 hover:bg-gray-50 transition-colors"
                             :class="{
                                 'bg-green-50': item.status === 'matched',
                                 'bg-red-50': ['shortage', 'not_found'].includes(item.status),
                                 'bg-blue-50': item.status === 'surplus',
                                 'bg-yellow-50': item.status === 'unexpected'
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
                                            <template x-if="item.status === 'matched'">
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
                                            <template x-if="['shortage', 'not_found'].includes(item.status)">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-500">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                    </svg>
                                                </span>
                                            </template>
                                        </span>
                                        
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-gray-900 truncate" x-text="item.product_code"></p>
                                            <p class="text-xs text-gray-500 truncate" x-text="item.product_name"></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Cantidades --}}
                                <div class="flex items-center space-x-4 ml-4">
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500">Sistema</p>
                                        <p class="text-lg font-bold text-gray-700" x-text="item.expected_quantity"></p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500">Contado</p>
                                        <input type="number" 
                                               x-model.number="item.counted_quantity"
                                               @change="updateQuantity(item)"
                                               min="0"
                                               class="w-16 text-center text-lg font-bold border rounded-lg py-1 focus:ring-2 focus:ring-blue-500"
                                               :class="{
                                                   'border-green-500 text-green-700': item.status === 'matched',
                                                   'border-red-500 text-red-700': ['shortage', 'not_found'].includes(item.status),
                                                   'border-blue-500 text-blue-700': item.status === 'surplus',
                                                   'border-gray-300': item.status === 'pending'
                                               }">
                                    </div>
                                    <div class="text-center" x-show="item.difference !== 0">
                                        <p class="text-xs text-gray-500">Dif.</p>
                                        <p class="text-lg font-bold" 
                                           :class="{
                                               'text-green-600': item.difference > 0,
                                               'text-red-600': item.difference < 0
                                           }"
                                           x-text="(item.difference > 0 ? '+' : '') + item.difference"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Acciones del Item --}}
                            <div class="mt-3 flex items-center justify-between">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                      :class="{
                                          'bg-gray-100 text-gray-600': item.status === 'pending',
                                          'bg-green-100 text-green-800': item.status === 'matched',
                                          'bg-red-100 text-red-800': ['shortage', 'not_found'].includes(item.status),
                                          'bg-blue-100 text-blue-800': item.status === 'surplus',
                                          'bg-yellow-100 text-yellow-800': item.status === 'unexpected'
                                      }"
                                      x-text="item.status_label">
                                </span>
                                
                                <div class="flex items-center space-x-2">
                                    <button @click="markNotFound(item)" 
                                            x-show="item.status === 'pending'"
                                            class="text-xs px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded transition-colors">
                                        No Encontrado
                                    </button>
                                    <button @click="recountItem(item)" 
                                            x-show="item.status !== 'pending'"
                                            class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors">
                                        Recontar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Empty State --}}
                    <div x-show="filteredItems.length === 0" class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No hay productos con este filtro</p>
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
                                class="w-full md:w-auto px-6 py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition-colors"
                                :disabled="stats.pending > 0">
                            <span x-show="stats.pending > 0">Faltan <span x-text="stats.pending"></span> productos</span>
                            <span x-show="stats.pending === 0">Finalizar Conteo</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Espaciador para el botón fijo en móvil --}}
            <div class="h-20 md:hidden"></div>
        </div>
    </div>

    @push('scripts')
    <script>
    function countingApp() {
        return {
            items: @json($items),
            scanCode: '',
            isProcessing: false,
            isScanFocused: false,
            lastScanMessage: '',
            lastScanSuccess: false,
            filterStatus: 'all',

            get stats() {
                return {
                    pending: this.items.filter(i => i.status === 'pending').length,
                    matched: this.items.filter(i => i.status === 'matched').length,
                    discrepancies: this.items.filter(i => ['shortage', 'surplus', 'not_found', 'unexpected'].includes(i.status)).length,
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
                if (this.filterStatus === 'matched') return this.items.filter(i => i.status === 'matched');
                if (this.filterStatus === 'discrepancy') return this.items.filter(i => ['shortage', 'surplus', 'not_found', 'unexpected'].includes(i.status));
                return this.items;
            },

            async processScan() {
                if (!this.scanCode.trim() || this.isProcessing) return;

                this.isProcessing = true;
                this.lastScanMessage = '';

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
                            scan_type: '{{ $inventoryCount->method === "rfid_bulk" || $inventoryCount->method === "rfid_handheld" ? "rfid" : "barcode" }}'
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.lastScanSuccess = true;
                        this.lastScanMessage = data.message;
                        
                        // Actualizar item en la lista
                        const index = this.items.findIndex(i => i.id === data.item.id);
                        if (index !== -1) {
                            this.items[index] = { ...this.items[index], ...data.item };
                        } else {
                            // Item nuevo (no esperado)
                            this.items.unshift(data.item);
                        }

                        // Sonido de éxito (opcional)
                        this.playSound('success');
                    } else {
                        this.lastScanSuccess = false;
                        this.lastScanMessage = data.message;
                        this.playSound('error');
                    }
                } catch (error) {
                    this.lastScanSuccess = false;
                    this.lastScanMessage = 'Error de conexión';
                    this.playSound('error');
                } finally {
                    this.isProcessing = false;
                    this.scanCode = '';
                    this.$refs.scanInput.focus();

                    // Limpiar mensaje después de 3 segundos
                    setTimeout(() => {
                        this.lastScanMessage = '';
                    }, 3000);
                }
            },

            async updateQuantity(item) {
                try {
                    const response = await fetch(`{{ url('inventory-counts') }}/${{{ $inventoryCount->id }}}/items/${item.id}/quantity`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            quantity: item.counted_quantity
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        const index = this.items.findIndex(i => i.id === item.id);
                        if (index !== -1) {
                            this.items[index] = { ...this.items[index], ...data.item };
                        }
                    }
                } catch (error) {
                    console.error('Error al actualizar cantidad:', error);
                }
            },

            async markNotFound(item) {
                if (!confirm(`¿Marcar "${item.product_code}" como NO ENCONTRADO?`)) return;

                try {
                    const response = await fetch(`{{ url('inventory-counts') }}/${{{ $inventoryCount->id }}}/items/${item.id}/not-found`, {
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
                            this.items[index].status = 'not_found';
                            this.items[index].status_label = 'No Encontrado';
                            this.items[index].counted_quantity = 0;
                            this.items[index].difference = -item.expected_quantity;
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            },

            async recountItem(item) {
                if (!confirm(`¿Recontar "${item.product_code}"?`)) return;

                try {
                    const response = await fetch(`{{ url('inventory-counts') }}/${{{ $inventoryCount->id }}}/items/${item.id}/recount`, {
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
                            this.items[index].difference = -item.expected_quantity;
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            },

            playSound(type) {
                // Para Enterprise Browser de Zebra, puedes usar la API nativa
                // O simplemente vibrar el dispositivo
                if (navigator.vibrate) {
                    navigator.vibrate(type === 'success' ? 100 : [100, 50, 100]);
                }
            },

            init() {
                // Focus automático en el input de escaneo
                this.$refs.scanInput.focus();

                // Re-focus cuando se pierde el foco (útil para escáneres)
                document.addEventListener('click', () => {
                    if (!this.isScanFocused) {
                        setTimeout(() => this.$refs.scanInput.focus(), 100);
                    }
                });
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
