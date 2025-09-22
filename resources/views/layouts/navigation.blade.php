<div>
    <div class="fixed top-4 right-4 z-50 flex items-center lg:hidden">
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-gray-700 rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
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
     class="fixed inset-y-0 left-0 z-40 bg-white transition-all duration-300 transform -translate-x-full lg:translate-x-0"
     :class="{ 'translate-x-0': mobileMenuOpen, 'lg:w-64': desktopSidebarOpen, 'lg:w-20': !desktopSidebarOpen }">
    
    <div class="flex flex-col h-full">
        <div class="flex-shrink-0 p-4 flex items-center justify-center h-24 mt-5">
            <a href="{{ route('dashboard') }}">
            <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </div>

        <div class="flex-1 flex flex-col p-2 space-y-2 overflow-y-auto overflow-x-hidden">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <x-slot name="icon">
                    <i class="fa-solid fa-house-chimney fa-fw text-lg"></i>
                </x-slot>
                {{ __('Inicio') }}
            </x-nav-link>

 
        @role('admin')
            <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                <x-slot name="icon">
                    <i class="fa-solid fa-users-cog fa-fw text-sm"></i>
                </x-slot>
                {{ __('Gestión de Usuarios') }}
            </x-nav-link>
        @endrole
        </div>


        <!-- Tarjeta de perfil mejorada -->
        <div class="flex-shrink-0 p-2">
            <!-- Perfil del usuario -->
            <div class="bg-blue-300 rounded-lg p-3 mb-2 backdrop-blur-sm border ">
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
                        
                        
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-500 text-white mt-1">
                                <i class="fa-solid fa-crown fa-xs mr-1"></i>
                                Admin
                            </span>
                       
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="flex items-center justify-between" 
                 :class="{ 'justify-between': desktopSidebarOpen, 'justify-center': !desktopSidebarOpen }">
                
                <!-- Botón de perfil  -->
                <a href="{{ route('profile.edit') }}" 
                   class="flex items-center justify-center px-3 py-2 text-sm font-medium text-000000 bg-cyan-700/50 rounded-lg hover:bg-cyan-600 transition-all duration-200 group overflow-hidden"
                   :class="{ 'flex-1': desktopSidebarOpen, 'w-10 h-10': !desktopSidebarOpen }"
                   title="Editar perfil">
                    <i class="fa-solid fa-user-gear text-base group-hover:scale-110 transition-transform duration-200 flex-shrink-0"></i>
                    <span class="ml-2 whitespace-nowrap transition-all duration-300 overflow-hidden" 
                          :class="{ 'opacity-100 w-auto': desktopSidebarOpen, 'opacity-0 w-0 ml-0': !desktopSidebarOpen }">
                        Mi Perfil
                    </span>
                </a>
                
                <!-- Botón de logout -->
                <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
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