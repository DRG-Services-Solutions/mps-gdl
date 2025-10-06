<div>
    <div class="fixed top-4 right-4 z-50 flex items-center lg:hidden">
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-gray-700 rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>
    
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
         productsMenuOpen: {{ request()->routeIs('products.*') ? 'true' : 'false' }},
         catalogsMenuOpen: {{ request()->routeIs('manufacturers.*') || request()->routeIs('categories.*') || request()->routeIs('subcategories.*') || request()->routeIs('specialties.*') ? 'true' : 'false' }}
     }"
     class="fixed inset-y-0 left-0 z-40 bg-white transition-all duration-300 transform -translate-x-full lg:translate-x-0 shadow-lg"
     :class="{ 'translate-x-0': mobileMenuOpen, 'lg:w-64': desktopSidebarOpen, 'lg:w-20': !desktopSidebarOpen }">
    
    <div class="flex flex-col h-full">
        <div class="flex-shrink-0 p-4 flex items-center justify-center h-24 mt-5">
            <a href="{{ route('dashboard') }}">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </div>

        <div class="flex-1 flex flex-col p-2 space-y-2 overflow-y-auto overflow-x-hidden">
            <!-- Inicio -->
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <x-slot name="icon">
                    <i class="fa-solid fa-house-chimney fa-fw text-lg"></i>
                </x-slot>
                {{ __('Inicio') }}
            </x-nav-link>

            @role('admin')
                <!-- Gestión de Usuarios -->
                <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    <x-slot name="icon">
                        <i class="fa-solid fa-users-cog fa-fw text-lg"></i>
                    </x-slot>
                    {{ __('Gestión de Usuarios') }}
                </x-nav-link>

                <!-- Órdenes de Compra -->
                <x-nav-link :href="route('purchase-orders.index')" :active="request()->routeIs('purchase-orders.*')">
                    <x-slot name="icon">
                        <i class="fa-solid fa-file-invoice-dollar fa-fw text-lg"></i>
                    </x-slot>
                    {{ __('Órdenes de Compra') }}
                </x-nav-link>

                <!-- Proveedores -->
                <x-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">
                    <x-slot name="icon">
                        <i class="fa-solid fa-cart-flatbed text-lg"></i>
                    </x-slot>
                    {{ __('Proveedores') }}
                </x-nav-link>

                <!-- Catálogos con Dropdown -->
                <div class="relative">
                    <!-- Botón principal de Catálogos -->
                    <button @click="catalogsMenuOpen = !catalogsMenuOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group {{ request()->routeIs('manufacturers.*') || request()->routeIs('categories.*') || request()->routeIs('subcategories.*') || request()->routeIs('specialties.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="flex-shrink-0">
                                <i class="fas fa-sitemap fa-fw text-lg"></i>
                            </div>
                            <span class="truncate transition-opacity duration-300" 
                                  :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0': !desktopSidebarOpen }">
                                {{ __('Catálogos') }}
                            </span>
                        </div>
                        <div class="flex-shrink-0 transition-all duration-300" 
                             :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0 lg:w-0': !desktopSidebarOpen }">
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
                               :class="{ 'rotate-180': catalogsMenuOpen }"></i>
                        </div>
                    </button>

                    <!-- Submenú desplegable cuando sidebar está expandido -->
                    <div x-show="catalogsMenuOpen && desktopSidebarOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="mt-2 ml-3 space-y-1 border-l-2 border-indigo-200">
                        
                        <!-- Fabricantes -->
                        <a href="{{ route('manufacturers.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('manufacturers.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-industry fa-fw text-sm"></i>
                            <span class="truncate">{{ __('Fabricantes') }}</span>
                        </a>

                        <!-- Categorías -->
                        <a href="{{ route('categories.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('categories.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-tags fa-fw text-sm"></i>
                            <span class="truncate">{{ __('Categorías') }}</span>
                        </a>

                        <!-- Subcategorías -->
                        <a href="{{ route('subcategories.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('subcategories.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-layer-group fa-fw text-sm"></i>
                            <span class="truncate">{{ __('Subcategorías') }}</span>
                        </a>

                        <!-- Especialidades -->
                        <a href="{{ route('specialties.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('specialties.*') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-stethoscope fa-fw text-sm"></i>
                            <span class="truncate">{{ __('Especialidades') }}</span>
                        </a>
                    </div>

                    <!-- Tooltip flotante para sidebar colapsado -->
                    <div x-show="!desktopSidebarOpen && catalogsMenuOpen" 
                         x-transition
                         @click.outside="catalogsMenuOpen = false"
                         class="hidden lg:block absolute left-full top-0 ml-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            {{ __('Catálogos') }}
                        </div>
                        <a href="{{ route('manufacturers.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('manufacturers.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-industry fa-fw text-sm"></i>
                            <span>{{ __('Fabricantes') }}</span>
                        </a>
                        <a href="{{ route('categories.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('categories.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-tags fa-fw text-sm"></i>
                            <span>{{ __('Categorías') }}</span>
                        </a>
                        <a href="{{ route('subcategories.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('subcategories.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-layer-group fa-fw text-sm"></i>
                            <span>{{ __('Subcategorías') }}</span>
                        </a>
                        <a href="{{ route('specialties.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('specialties.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-stethoscope fa-fw text-sm"></i>
                            <span>{{ __('Especialidades') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Gestión de Productos con Dropdown -->
                <div class="relative">
                    <!-- Botón principal de Productos -->
                    <button @click="productsMenuOpen = !productsMenuOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group {{ request()->routeIs('products.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-boxes-stacked fa-fw text-lg"></i>
                            </div>
                            <span class="truncate transition-opacity duration-300" 
                                  :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0': !desktopSidebarOpen }">
                                {{ __('Gestión de Productos') }}
                            </span>
                           
                        </div>
                        <div class="flex-shrink-0 transition-all duration-300" 
                             :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0 lg:w-0': !desktopSidebarOpen }">
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
                               :class="{ 'rotate-180': productsMenuOpen }"></i>
                        </div>
                    </button>

                    <!-- Submenú desplegable cuando sidebar está expandido -->
                    <div x-show="productsMenuOpen && desktopSidebarOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="mt-2 ml-3 space-y-1 border-l-2 border-indigo-200">
                        
                        <!-- Productos -->
                        <a href="{{ route('products.index') }}" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('products.index') || request()->routeIs('products.create') || request()->routeIs('products.edit') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-box fa-fw text-sm"></i>
                            <span class="truncate">{{ __('Productos') }}</span>
                        </a>
                         <a href="{{route ('product-units.index')}}" :active="request()->routeIs('product-units.*')"
" 
                           class="flex items-center space-x-3 pl-6 pr-3 py-2 text-sm font-medium rounded-r-lg transition-all duration-200 {{ request()->routeIs('product-units.index') || request()->routeIs('product-units.create') || request()->routeIs('product-units.edit') ? 'bg-indigo-50 text-indigo-600 border-l-2 border-indigo-600 -ml-0.5' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-box fa-fw text-sm"></i>
                            <span class="truncate">{{ __('Inventario') }}</span>
                        </a>
                        
                    </div>

                    <!-- Tooltip flotante para sidebar colapsado -->
                    <div x-show="!desktopSidebarOpen && productsMenuOpen" 
                         x-transition
                         @click.outside="productsMenuOpen = false"
                         class="hidden lg:block absolute left-full top-0 ml-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            {{ __('Gestión de Productos') }}
                        </div>
                        <a href="{{ route('products.index') }}" 
                           class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->routeIs('products.index') || request()->routeIs('products.create') || request()->routeIs('products.edit') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-box fa-fw text-sm"></i>
                            <span>{{ __('Productos') }}</span>
                        </a>
                      
                    </div>
                    <!-- Inventario / Unidades de Productos -->
                 

                </div>
            @endrole
        </div>

        <!-- Tarjeta de perfil mejorada -->
        <div class="flex-shrink-0 p-2">
            <!-- Perfil del usuario -->
            <div class="bg-blue-300 rounded-lg p-3 mb-2 backdrop-blur-sm border">
                <div class="flex items-center space-x-3">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <img class="h-10 w-10 rounded-full object-cover ring-2 ring-blue-600" 
                             src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0891b2&color=000000&bold=true" 
                             alt="{{ Auth::user()->name }}">
                    </div>
                    
                    <!-- Información del usuario -->
                    <div class="flex-1 min-w-0 transition-opacity duration-300" 
                         :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0 lg:w-0': !desktopSidebarOpen }">
                        <p class="text-sm font-semibold text-000000 truncate">
                            {{ Auth::user()->name }}
                        </p>
                        <p class="text-xs text-000000 truncate">
                            {{ Auth::user()->email }}
                        </p>
                        
                        @if (Auth::user()->hasRole('admin'))
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-500 text-white mt-1">
                                <i class="fa-solid fa-crown fa-xs mr-1"></i>
                                Admin
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500 text-white mt-1">
                                <i class="fa-solid fa-user fa-xs mr-1"></i>
                                {{ Auth::user()->getRoleNames()->first() }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="flex items-center justify-between" 
                 :class="{ 'justify-between': desktopSidebarOpen, 'justify-center': !desktopSidebarOpen }">
                
                <!-- Botón de perfil -->
                <a href="{{ route('profile.edit') }}" 
                   class="flex items-center justify-center px-5 py-2 text-sm font-medium text-000000 bg-cyan-700/50 rounded-lg hover:bg-cyan-600 transition-all duration-200 group overflow-hidden"
                   :class="{ 'flex-1': desktopSidebarOpen, 'w-10 h-10': !desktopSidebarOpen }"
                   title="Editar perfil">
                    <i class="fa-solid fa-user-gear text-base group-hover:scale-110 transition-transform duration-200 flex-shrink-0"></i>
                    <span class="ml-2 whitespace-nowrap transition-all duration-300 overflow-hidden" 
                          :class="{ 'opacity-100 w-auto': desktopSidebarOpen, 'opacity-0 w-0 ml-0': !desktopSidebarOpen }">
                        Mi Perfil
                    </span>
                </a>
                
                <!-- Botón de logout -->
                <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0 ml-2">
                    @csrf
                    <button type="submit" 
                            class="flex items-center justify-center px-3 py-2 text-sm font-medium text-000000 bg-red-600/70 rounded-lg hover:bg-red-600 transition-all duration-200 group overflow-hidden"
                            :class="{ 'w-auto': desktopSidebarOpen, 'w-10 h-10': !desktopSidebarOpen }"
                            title="Cerrar sesión"
                            onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?')">
                        <i class="fa-solid fa-right-from-bracket text-base group-hover:scale-110 transition-transform duration-200 flex-shrink-0"></i>
                        <span class="ml-2 whitespace-nowrap transition-all duration-300 overflow-hidden" 
                              :class="{ 'opacity-100 w-auto': desktopSidebarOpen, 'opacity-0 w-0 ml-0': !desktopSidebarOpen }">
                            Salir
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>