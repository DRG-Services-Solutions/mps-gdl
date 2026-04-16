<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Existencias') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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

            @if(session('error'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-transition
                     class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-red-700 font-medium">{{ session('error') }}</p>
                        </div>
                        <button @click="show = false" class="text-red-400 hover:text-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Existencias Agrupadas</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Explora los productos. Haz clic en ellos para ver el detalle de sus unidades físicas, lotes y números de serie.
                            </p>
                        </div>
                        <div class="flex items-center space-x-2 text-sm text-gray-600 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span class="font-bold text-gray-900">{{ $groupedProducts->total() }}</span> 
                            <span>productos listados</span>
                        </div>
                    </div>
                </div>

                {{-- ============================================================
                     FILTROS REACTIVOS (Alpine.js)
                     ============================================================ --}}
                <div
                    x-data="{
                        filters: {
                            search:     '{{ request('search') }}',
                            product_id: '{{ request('product_id') }}',
                            status:     '{{ request('status') }}',
                        },
                        productLabel: '{{ addslashes(optional($products->firstWhere('id', request('product_id')))->name ?? '') }}',
                        loading: false,
                        debounceTimer: null,

                        get activeFilters() {
                            const self = this;
                            const labels = {
                                search:     v => v ? `Búsqueda: ${v}` : null,
                                product_id: v => v ? `Producto: ${self.productLabel}` : null,
                                status:     v => v ? `Estado: ${self.statusLabel(v)}` : null,
                            };
                            return Object.entries(this.filters)
                                .map(([k, v]) => ({ key: k, label: labels[k](v) }))
                                .filter(f => f.label !== null);
                        },

                        statusLabel(v) {
                            const map = {
                                available:        'Disponible',
                                in_use:           'En Uso',
                                reserved:         'Reservado',
                                in_sterilization: 'En Esterilización',
                                maintenance:      'Mantenimiento',
                                damaged:          'Dañado',
                                expired:          'Caducado',
                            };
                            return map[v] ?? v;
                        },

                        removeFilter(key) {
                            this.filters[key] = '';
                            if (key === 'product_id') {
                                this.productLabel = '';
                                // Limpia el input visual del componente hijo
                                window.dispatchEvent(new CustomEvent('clear-product'));
                            }
                            this.doFetch();
                        },

                        doFetch() {
                            clearTimeout(this.debounceTimer);
                            this.debounceTimer = setTimeout(async () => {
                                this.loading = true;

                                const params = new URLSearchParams(
                                    Object.fromEntries(
                                        Object.entries(this.filters).filter(([, v]) => v !== '')
                                    )
                                );

                                const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                                window.history.pushState({}, '', newUrl);

                                const res = await fetch(window.location.pathname + '?' + params.toString(), {
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                });
                                document.getElementById('table-container').innerHTML = await res.text();
                                this.loading = false;
                            }, 350);
                        }
                    }"
                    x-init="
                        $watch('filters.status', () => doFetch());
                        $watch('filters.search', () => doFetch());
                    "
                    {{-- Escucha el evento del hijo cuando se selecciona/limpia un producto --}}
                    x-on:product-selected.window="
                        filters.product_id = $event.detail.id;
                        productLabel       = $event.detail.name;
                        doFetch();
                    "
                >
                    <div class="p-6 bg-gray-50 border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <input
                                        type="text"
                                        x-model="filters.search"
                                        placeholder="EPC, Serial, Lote..."
                                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                </div>
                            </div>

                            <div
                                x-data="{
                                    open:          false,
                                    search:        '',
                                    results:       [],
                                    loadingSearch: false,
                                    debounce:      null,

                                    init() {
                                        window.addEventListener('clear-product', () => {
                                            this.search  = '';
                                            this.open    = false;
                                            this.results = [];
                                        });
                                    },

                                    fetchProducts() {
                                        clearTimeout(this.debounce);
                                        this.debounce = setTimeout(async () => {
                                            if (!this.search.trim()) {
                                                this.results = [];
                                                this.open    = false;
                                                return;
                                            }
                                            this.loadingSearch = true;
                                            const res = await fetch(
                                                `{{ route('product-units.search-products') }}?q=${encodeURIComponent(this.search)}`,
                                                { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                                            );
                                            this.results       = await res.json();
                                            this.open          = this.results.length > 0;
                                            this.loadingSearch = false;
                                        }, 300);
                                    },

                                    selectProduct(product) {
                                        this.search  = product.name;
                                        this.open    = false;
                                        this.results = [];
                                        // Comunica al padre vía evento global
                                        window.dispatchEvent(new CustomEvent('product-selected', {
                                            detail: { id: product.id, name: product.name }
                                        }));
                                    },

                                    clear() {
                                        this.search  = '';
                                        this.open    = false;
                                        this.results = [];
                                        window.dispatchEvent(new CustomEvent('product-selected', {
                                            detail: { id: '', name: '' }
                                        }));
                                    }
                                }"
                                x-init="
                                    @if(request('product_id') && $products->firstWhere('id', request('product_id')))
                                        search = '{{ addslashes($products->firstWhere('id', request('product_id'))->name) }}';
                                    @endif
                                "
                                x-on:click.outside="open = false"
                                class="relative"
                            >
                                <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>

                                <div class="relative">
                                    <input
                                        type="text"
                                        x-model="search"
                                        x-on:input="fetchProducts()"
                                        placeholder="Buscar producto..."
                                        autocomplete="off"
                                        class="block w-full px-3 py-2.5 pr-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                    <div class="absolute inset-y-0 right-2 flex items-center">
                                        <template x-if="loadingSearch">
                                            <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                            </svg>
                                        </template>
                                        <template x-if="!loadingSearch && search">
                                            <button type="button" x-on:click="clear()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                    style="display: none;"
                                >
                                    <template x-for="product in results" :key="product.id">
                                        <button
                                            type="button"
                                            x-on:click="selectProduct(product)"
                                            class="w-full text-left px-4 py-2.5 hover:bg-blue-50 transition-colors border-b border-gray-100 last:border-0 focus:bg-blue-50 focus:outline-none"
                                        >
                                            <span class="block text-sm font-medium text-gray-900" x-text="product.name"></span>
                                            <span class="block text-xs text-gray-500" x-text="product.code"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estado de la unidad</label>
                                <select
                                    x-model="filters.status"
                                    class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">Todos los estados</option>
                                    <option value="available">Disponible</option>
                                    <option value="in_use">En Uso</option>
                                    <option value="reserved">Reservado</option>
                                    <option value="in_sterilization">En Esterilización</option>
                                    <option value="maintenance">Mantenimiento</option>
                                    <option value="damaged">Dañado</option>
                                    <option value="expired">Caducado</option>
                                </select>
                            </div>

                        </div>{{-- /grid --}}

                        <div class="mt-3 flex flex-wrap gap-2" x-show="activeFilters.length > 0" x-cloak>
                            <span class="text-xs text-gray-500 self-center">Filtros activos:</span>
                            <template x-for="filter in activeFilters" :key="filter.key">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <span x-text="filter.label"></span>
                                    <button
                                        type="button"
                                        x-on:click="removeFilter(filter.key)"
                                        class="ml-1 hover:text-blue-600 focus:outline-none"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>

                    </div>{{-- /filtros --}}

                    <div
                        id="table-container"
                        x-bind:class="loading ? 'opacity-50 pointer-events-none transition-opacity duration-200' : ''"
                    >
                        @include('product-units._table')
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>