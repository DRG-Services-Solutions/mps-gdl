<div>
    <!-- Botón menú móvil -->
    <div class="fixed top-4 right-4 z-50 flex items-center lg:hidden">
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-gray-700 rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>
    
    <!-- Overlay móvil -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileMenuOpen = false" 
         class="fixed inset-0 z-30 bg-black bg-opacity-50 lg:hidden">
    </div>
</div>

<nav @mouseenter="desktopSidebarOpen = true" @mouseleave="desktopSidebarOpen = false"
     x-data="{ 
         inventoryMenuOpen: {{ request()->routeIs('products.*') || request()->routeIs('sets.*') || request()->routeIs('product-units.*') || request()->routeIs('product_layouts.*') || request()->routeIs('inventory-counts.*') || request()->routeIs('inventory.movements') ? 'true' : 'false' }},
         purchasesMenuOpen: {{ request()->routeIs('purchase-orders.*') || request()->routeIs('suppliers.*') ? 'true' : 'false' }},
         catalogsMenuOpen: {{ request()->routeIs('storage_locations.*') || request()->routeIs('legal-entities.*') || request()->routeIs('categories.*') || request()->routeIs('subcategories.*') || request()->routeIs('specialties.*') ? 'true' : 'false' }}
     }"
     class="fixed inset-y-0 left-0 z-40 bg-white transition-all duration-300 transform -translate-x-full lg:translate-x-0 shadow-lg"
     :class="{ 'translate-x-0': mobileMenuOpen, 'lg:w-64': desktopSidebarOpen, 'lg:w-20': !desktopSidebarOpen }">
    
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex-shrink-0 p-4 flex items-center justify-center h-24 mt-5 border-b border-gray-100">
            <a href="{{ route('dashboard') }}">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </div>

        <!-- Menú de navegación -->
        <div class="flex-1 flex flex-col p-3 space-y-1 overflow-y-auto overflow-x-hidden">
            
            <!-- SECCIÓN: GENERAL -->
            <div class="px-3 pt-2 pb-1" :class="{ 'opacity-100': desktopSidebarOpen, 'opacity-0 h-0 overflow-hidden': !desktopSidebarOpen }">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">General</p>
            </div>

            <!-- Inicio -->
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <x-slot name="icon">
                    <i class="fa-solid fa-house-chimney fa-fw text-lg"></i>
                </x-slot>
                {{ __('Dashboard') }}
            </x-nav-link>

            @role('admin')

            <!-- SECCIÓN: INVENTARIO -->
                <div class="px-3 pt-4 pb-1" :class="{ 'opacity-100': desktopSidebarOpen, 'opacity-0 h-0 overflow-hidden': !desktopSidebarOpen }">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Inventario</p>
                </div>

                <!-- Inventario Dropdown -->
                <div class="relative">
                    <button @click="inventoryMenuOpen = !inventoryMenuOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group {{ request()->routeIs('products.*') || request()->routeIs('product-units.*') || request()->routeIs('product_layouts.*') || request()->routeIs('inventory-counts.*') || request()->routeIs('inventory.movements') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">

                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-boxes-stacked fa-fw text-lg"></i>
                            </div>
                            <span class="truncate transition-opacity duration-300" 
                                  :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0': !desktopSidebarOpen }">
                                Inventario
                            </span>
                        </div>
                        <div class="flex-shrink-0 transition-all duration-300" 
                             :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0 lg:w-0': !desktopSidebarOpen }">
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
                               :class="{ 'rotate-180': inventoryMenuOpen }"></i>
                        </div>
                    </button>

                    <!-- Submenú Inventario (expandido) -->
                    <div x-show="inventoryMenuOpen && desktopSidebarOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="mt-1 ml-3 space-y-1 border-l-2 border-indigo-200">
                        
                        <a href="{{ route('products.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('products.index') || request()->routeIs('products.create') || request()->routeIs('products.edit') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-box fa-fw text-sm"></i>
                            <span class="truncate">Catalogo</span>
                        </a>
                        
                        <a href="{{ route('sets.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('sets.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-layer-group fa-fw text-sm"></i>
                            <span class="truncate">Sets/Kits</span>
                        </a>

                        <a href="{{ route('product-units.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('product-units.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-cubes fa-fw text-sm"></i>
                            <span class="truncate">Existencias</span>
                        </a>
                        
                       

                        <a href="{{ route('product_layouts.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('product_layouts.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-sitemap fa-fw text-sm"></i>
                            <span class="truncate">Lay Out</span>
                        </a>

                        <!-- Toma de Inventarios -->
                        <a href="{{ route('inventory-counts.index') }}" 
                        class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('inventory-counts.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-clipboard-check fa-fw text-sm"></i>
                            <span class="truncate">Toma de Inventarios</span>
                        </a>

                        <a href="{{ route('inventory.movements') }}" 
                        class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('inventory.movements') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-history fa-fw text-sm"></i>
                            <span class="truncate">Movimientos (Kardex)</span>
                        </a>
                    </div>

                    <!-- Tooltip Inventario (colapsado) -->
                    <div x-show="!desktopSidebarOpen && inventoryMenuOpen" 
                         x-transition
                         @click.outside="inventoryMenuOpen = false"
                         class="hidden lg:block absolute left-full top-0 ml-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            Inventario
                        </div>
                        <a href="{{ route('products.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('products.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-box fa-fw text-sm"></i>
                            <span>Productos</span>
                        </a>
                        <a href="{{ route('sets.index') }}" 
                        class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('sets.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-layer-group fa-fw text-sm"></i>
                            <span>Recetas (Sets)</span>
                        </a>
                        <a href="{{ route('product-units.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('product-units.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-cubes fa-fw text-sm"></i>
                            <span>Existencias</span>
                        </a>
                        <a href="{{ route('product_layouts.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('product_layouts.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-sitemap fa-fw text-sm"></i>
                            <span>Lay Out</span>
                        </a>

                        <a href="{{ route('inventory-counts.index') }}" 
                        class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('inventory-counts.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-clipboard-check fa-fw text-sm"></i>
                            <span>Toma de Inventarios</span>
                        </a>
                    </div>
                </div>


        
                <!-- SECCIÓN: COMPRAS -->
                <div class="px-3 pt-4 pb-1" :class="{ 'opacity-100': desktopSidebarOpen, 'opacity-0 h-0 overflow-hidden': !desktopSidebarOpen }">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Compras</p>
                </div>

                <!-- Compras Dropdown -->
                <div class="relative">
                    <button @click="purchasesMenuOpen = !purchasesMenuOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group {{ request()->routeIs('purchase-orders.*') || request()->routeIs('suppliers.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-shopping-cart fa-fw text-lg"></i>
                            </div>
                            <span class="truncate transition-opacity duration-300" 
                                  :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0': !desktopSidebarOpen }">
                                {{ __('Compras') }}
                            </span>
                        </div>
                        <div class="flex-shrink-0 transition-all duration-300" 
                             :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0 lg:w-0': !desktopSidebarOpen }">
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
                               :class="{ 'rotate-180': purchasesMenuOpen }"></i>
                        </div>
                    </button>

                    <!-- Submenú Compras (expandido) -->
                    <div x-show="purchasesMenuOpen && desktopSidebarOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="mt-1 ml-3 space-y-1 border-l-2 border-indigo-200">
                        
                        <a href="{{ route('purchase-orders.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('purchase-orders.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-file-invoice-dollar fa-fw text-sm"></i>
                            <span class="truncate">{{ __('Órdenes de Compra') }}</span>
                        </a>

                        <a href="{{ route('suppliers.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('suppliers.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-truck fa-fw text-sm"></i>
                            <span class="truncate">{{ __('Proveedores') }}</span>
                        </a>
                    </div>

                    <!-- Tooltip Compras (colapsado) -->
                    <div x-show="!desktopSidebarOpen && purchasesMenuOpen" 
                         x-transition
                         @click.outside="purchasesMenuOpen = false"
                         class="hidden lg:block absolute left-full top-0 ml-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            {{ __('Compras') }}
                        </div>
                        <a href="{{ route('purchase-orders.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('purchase-orders.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-file-invoice-dollar fa-fw text-sm"></i>
                            <span>{{ __('Órdenes de Compra') }}</span>
                        </a>
                        <a href="{{ route('suppliers.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('suppliers.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-truck fa-fw text-sm"></i>
                            <span>{{ __('Proveedores') }}</span>
                        </a>
                    </div>
                </div>

                <!-- SECCIÓN: CIRUGÍAS -->
                    <div class="px-3 pt-4 pb-1" :class="{ 'opacity-100': desktopSidebarOpen, 'opacity-0 h-0 overflow-hidden': !desktopSidebarOpen }">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cirugías</p>
                    </div>

                    <!-- Cirugías Dropdown -->
                    <div class="relative" x-data="{ 
                        quotationsMenuOpen: {{ request()->routeIs('quotations.*') || request()->routeIs('hospitals.*') || request()->routeIs('doctors.*') || request()->routeIs('sales.*') || request()->routeIs('surgical-kits.*') || request()->routeIs('checklists.*') || request()->routeIs('pre-assembled.*') || request()->routeIs('surgeries.*') ? 'true' : 'false' }}
                    }">
                        <button @click="quotationsMenuOpen = !quotationsMenuOpen"
                                class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group {{ request()->routeIs('quotations.*') || request()->routeIs('hospitals.*') || request()->routeIs('doctors.*') || request()->routeIs('sales.*') || request()->routeIs('surgical-kits.*') || request()->routeIs('checklists.*') || request()->routeIs('pre-assembled.*') || request()->routeIs('surgeries.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                            <div class="flex items-center space-x-3 flex-1 min-w-0">
                                <div class="flex-shrink-0">
                                    <i class="fa-solid fa-notes-medical fa-fw text-lg"></i>
                                </div>
                                <span class="truncate transition-opacity duration-300" 
                                    :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0': !desktopSidebarOpen }">
                                    Cirugías
                                </span>
                            </div>
                            <div class="flex-shrink-0 transition-all duration-300" 
                                :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0 lg:w-0': !desktopSidebarOpen }">
                                <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
                                :class="{ 'rotate-180': quotationsMenuOpen }"></i>
                            </div>
                        </button>

                        <!-- Submenú Cirugías (expandido) -->
                        <div x-show="quotationsMenuOpen && desktopSidebarOpen" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="mt-1 ml-3 space-y-1 border-l-2 border-indigo-200">

                            <!-- CIRUGÍAS PROGRAMADAS - NUEVO -->
                            <a href="{{ route('surgeries.index') }}" 
                            class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('surgeries.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-calendar-check fa-fw text-sm"></i>
                                <span class="truncate">Programacion</span>
                            </a>
                            
                            <!-- CHECK LISTS - NUEVO -->
                            <a href="{{ route('checklists.index') }}" 
                            class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('checklists.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-clipboard-list fa-fw text-sm"></i>
                                <span class="truncate">{{ __('Check Lists') }}</span>
                            </a>

                            <!-- PAQUETES PRE-ARMADOS - NUEVO -->
                            <a href="{{ route('pre-assembled.index') }}" 
                            class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('pre-assembled.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-box-open fa-fw text-sm"></i>
                                <span class="truncate">{{ __('Pre-Armados') }}</span>
                            </a>

                            

                            <!-- Remisiones -->
                            <a href="{{ route('shipping-notes.index') }}" 
                            class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('quotations.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-file-invoice fa-fw text-sm"></i>
                                <span class="truncate">Remisiones</span>
                            </a>

                            

                            <!-- Ventas (existente) -->
                            <a href="{{ route('sales.index') }}" 
                            class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('sales.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-dollar-sign fa-fw text-sm"></i>
                                <span class="truncate">Ventas</span>
                            </a>

                            

                            <!-- Hospitales -->
                            <a href="{{ route('hospitals.index') }}" 
                            class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('hospitals.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-hospital fa-fw text-sm"></i>
                                <span class="truncate">Clientes/Hospitales</span>
                            </a>

                            <!-- Listas de Precios -->
                            <a href="{{ route('price-lists.index') }}" 
                            class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('price-lists.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-tags fa-fw text-sm"></i>
                                <span class="truncate">Listas de Precios</span>
                            </a>

                            <!-- Doctores (existente) -->
                            <a href="{{ route('doctors.index') }}" 
                            class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('doctors.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-user-md fa-fw text-sm"></i>
                                <span class="truncate">{{ __('Doctores') }}</span>
                            </a>
                        </div>

                        <!-- Tooltip Cirugías (colapsado) -->
                        <div x-show="!desktopSidebarOpen && quotationsMenuOpen" 
                            x-transition
                            @click.outside="quotationsMenuOpen = false"
                            class="hidden lg:block absolute left-full top-0 ml-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                {{ __('Cirugías') }}
                            </div>
                            
                            <!-- CHECK LISTS EN TOOLTIP -->
                            <a href="{{ route('checklists.index') }}" 
                            class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('checklists.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                <i class="fas fa-clipboard-list fa-fw text-sm"></i>
                                <span>{{ __('Check Lists') }}</span>
                            </a>

                            <!-- PAQUETES PRE-ARMADOS EN TOOLTIP -->
                            <a href="{{ route('pre-assembled.index') }}" 
                            class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('pre-assembled.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                <i class="fas fa-box-open fa-fw text-sm"></i>
                                <span>{{ __('Pre-Armados') }}</span>
                            </a>

                            <!-- CIRUGÍAS PROGRAMADAS EN TOOLTIP -->
                            <a href="{{ route('surgeries.index') }}" 
                            class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('surgeries.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                <i class="fas fa-calendar-check fa-fw text-sm"></i>
                                <span>{{ __('Programacion') }}</span>
                            </a>

                            <a href="{{ route('quotations.index') }}" 
                            class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('quotations.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                <i class="fas fa-file-invoice fa-fw text-sm"></i>
                                <span>{{ __('Remisiones') }}</span>
                            </a>
                            
                            <a href="{{ route('sales.index') }}" 
                            class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('sales.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                <i class="fas fa-dollar-sign fa-fw text-sm"></i>
                                <span>{{ __('Ventas') }}</span>
                            </a>
                            
                            <a href="{{ route('sets.index') }}" 
                            class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('surgical-kits.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                <i class="fas fa-kit-medical fa-fw text-sm"></i>
                                <span>{{ __('Kits') }}</span>
                            </a>
                            
                            <a href="{{ route('hospitals.index') }}" 
                            class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('hospitals.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                <i class="fas fa-hospital fa-fw text-sm"></i>
                                <span>Hospitales</span>
                            </a>

                            <a href="{{ route('price-lists.index') }}" 
                            class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('price-lists.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                <i class="fas fa-tags fa-fw text-sm"></i>
                                <span>Listas de Precios</span>
                            </a>
                            
                            <a href="{{ route('doctors.index') }}" 
                            class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('doctors.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                <i class="fas fa-user-md fa-fw text-sm"></i>
                                <span>{{ __('Doctores') }}</span>
                            </a>
                        </div>
                    </div>

                

                <!-- SECCIÓN: CONFIGURACIÓN -->
                <div class="px-3 pt-4 pb-1" :class="{ 'opacity-100': desktopSidebarOpen, 'opacity-0 h-0 overflow-hidden': !desktopSidebarOpen }">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Configuración</p>
                </div>

                <!-- Catálogos Dropdown -->
                <div class="relative">
                    <button @click="catalogsMenuOpen = !catalogsMenuOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group {{ request()->routeIs('storage_locations.*') || request()->routeIs('legal-entities.*') || request()->routeIs('categories.*') || request()->routeIs('subcategories.*') || request()->routeIs('specialties.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="flex-shrink-0">
                                <i class="fas fa-cog fa-fw text-lg"></i>
                            </div>
                            <span class="truncate transition-opacity duration-300" 
                                  :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0': !desktopSidebarOpen }">
                                Catálogos
                            </span>
                        </div>
                        <div class="flex-shrink-0 transition-all duration-300" 
                             :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0 lg:w-0': !desktopSidebarOpen }">
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
                               :class="{ 'rotate-180': catalogsMenuOpen }"></i>
                        </div>
                    </button>

                    <!-- Submenú Catálogos (expandido) -->
                    <div x-show="catalogsMenuOpen && desktopSidebarOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="mt-1 ml-3 space-y-1 border-l-2 border-indigo-200">
                        
                        <a href="{{ route('storage_locations.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('storage_locations.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-warehouse fa-fw text-sm"></i>
                            <span class="truncate">Ubicaciones</span>
                        </a>

                        <!-- LEGAL ENTITIES -->
                        <a href="{{ route('legal-entities.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('legal-entities.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-briefcase fa-fw text-sm"></i>
                            <span class="truncate">Sucursales</span>
                        </a>

                        <!-- SUB WAREHOUSES -->
                        <a href="{{ route('sub-warehouses.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('sub-warehouses.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fa-solid fa-dolly text-sm"></i>
                            <span class="truncate">Sub - Almacenes</span>
                        </a>

                        <a href="{{ route('categories.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('categories.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-tags fa-fw text-sm"></i>
                            <span class="truncate">Categorías</span>
                        </a>

                        

                        <a href="{{ route('specialties.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('specialties.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-stethoscope fa-fw text-sm"></i>
                            <span class="truncate">Especialidades</span>
                        </a>
                    </div>

                    <!-- Tooltip Catálogos (colapsado) -->
                    <div x-show="!desktopSidebarOpen && catalogsMenuOpen" 
                         x-transition
                         @click.outside="catalogsMenuOpen = false"
                         class="hidden lg:block absolute left-full top-0 ml-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            Catálogos
                        </div>
                        <a href="{{ route('storage_locations.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('storage_locations.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-warehouse fa-fw text-sm"></i>
                            <span>Ubicaciones</span>
                        </a>
                        
                        <!-- ✅ RAZONES SOCIALES EN TOOLTIP -->
                        <a href="{{ route('legal-entities.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('legal-entities.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-briefcase fa-fw text-sm"></i>
                            <span>Sucursales</span>
                        </a>
                        
                        <a href="{{ route('categories.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('categories.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-tags fa-fw text-sm"></i>
                            <span>Categorías</span>
                        </a>
                        
                        <a href="{{ route('specialties.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('specialties.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-stethoscope fa-fw text-sm"></i>
                            <span>Especialidades</span>
                        </a>
                    </div>
                </div>

                <!-- Gestión de Usuarios -->
                <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    <x-slot name="icon">
                        <i class="fa-solid fa-users-cog fa-fw text-lg"></i>
                    </x-slot>
                    Usuarios
                </x-nav-link>

            @endrole
        </div>

        <!-- Tarjeta de perfil -->
        <div class="flex-shrink-0 p-3 border-t border-gray-100">
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg p-3 mb-2 shadow-md">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <img class="h-10 w-10 rounded-full object-cover ring-2 ring-white" 
                             src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=ffffff&color=4f46e5&bold=true" 
                             alt="{{ Auth::user()->name }}">
                    </div>
                    
                    <div class="flex-1 min-w-0 transition-opacity duration-300" 
                         :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0 lg:w-0': !desktopSidebarOpen }">
                        <p class="text-sm font-semibold text-white truncate">
                            {{ Auth::user()->name }}
                        </p>
                        <p class="text-xs text-indigo-100 truncate">
                            {{ Auth::user()->email }}
                        </p>
                        
                        @if (Auth::user()->hasRole('admin'))
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white text-indigo-600 mt-1">
                                <i class="fa-solid fa-crown fa-xs mr-1"></i>
                                Admin
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-400 text-white mt-1">
                                <i class="fa-solid fa-user fa-xs mr-1"></i>
                                {{ Auth::user()->getRoleNames()->first() }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="flex items-center gap-2">
                <a href="{{ route('profile.edit') }}" 
                   class="flex items-center justify-center flex-1 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all duration-200 group"
                   :class="{ 'px-3': desktopSidebarOpen, 'w-10 h-10 px-0': !desktopSidebarOpen }"
                   title="Editar perfil">
                    <i class="fa-solid fa-user-gear text-base group-hover:scale-110 transition-transform duration-200"></i>
                    <span class="ml-2 transition-all duration-300" 
                          :class="{ 'opacity-100': desktopSidebarOpen, 'opacity-0 w-0 ml-0 hidden': !desktopSidebarOpen }">
                        Perfil
                    </span>
                </a>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition-all duration-200 group"
                            :class="{ 'px-3': desktopSidebarOpen, 'w-10 h-10 px-0': !desktopSidebarOpen }"
                            title="Cerrar sesión"
                            onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?')">
                        <i class="fa-solid fa-right-from-bracket text-base group-hover:scale-110 transition-transform duration-200"></i>
                        <span class="ml-2 transition-all duration-300" 
                              :class="{ 'opacity-100': desktopSidebarOpen, 'opacity-0 w-0 ml-0 hidden': !desktopSidebarOpen }">
                            Salir
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>