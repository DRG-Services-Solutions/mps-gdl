<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DRGTrack') }} - Sistema de Gestión Médica</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
            integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
            crossorigin="anonymous" 
            referrerpolicy="no-referrer" />
        <!-- Tom Select -->
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">


        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
            
            /* Animación suave para el sidebar */
            .sidebar-transition {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            /* Patrón de fondo sutil */
            .bg-pattern {
                background-color: #f8fafc;
                background-image: 
                    radial-gradient(circle at 25px 25px, rgba(59, 130, 246, 0.03) 2%, transparent 0%),
                    radial-gradient(circle at 75px 75px, rgba(6, 182, 212, 0.03) 2%, transparent 0%);
                background-size: 100px 100px;
            }

            /* Scroll suave */
            * {
                scroll-behavior: smooth;
            }

            /* Ocultar scrollbar pero mantener funcionalidad */
            .hide-scrollbar::-webkit-scrollbar {
                display: none;
            }
            .hide-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .phpdebugbar,
            .phpdebugbar *,
            .phpdebugbar-tooltip,
            span.phpdebugbar-tooltip {
                z-index: 50 !important;
            }

            /* Dropdown siempre visible */
            [x-ref="dropdown"] {
                z-index: 999999 !important;
            }

        </style>

        @stack('styles')

    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-pattern" x-data="{ mobileMenuOpen: false, desktopSidebarOpen: true }">
            <!-- Navigation -->
            @include('layouts.navigation')

            <!-- Main Content Area -->
            <div 
                class="sidebar-transition"
                :class="{ 'lg:ml-64': desktopSidebarOpen, 'lg:ml-20': !desktopSidebarOpen }">

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow-md border-b border-gray-200">
                        <div class="max-w-8xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1 sidebar-transition min-h-screen">
                    {{ $slot }}
                </main>

                <!-- Footer -->
                <footer class="bg-white border-t border-gray-200 mt-auto">
                    <div class="max-w-8xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        <div class="flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0">
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                    <span class="text-sm text-gray-600">Sistema activo</span>
                                </div>
                            </div>
                            
                            <div class="text-center md:text-left">
                                <p class="text-sm text-gray-600">
                                    &copy; {{ date('Y') }} 
                                    <span class="font-semibold text-blue-600">Distribuciones MPS</span> 
                                    - Todos los derechos reservados
                                </p>
                            </div>

                            <div class="flex items-center space-x-4">
                                <span class="text-xs text-gray-500">
                                    Versión 1.0.0
                                </span>
                                <div class="flex items-center space-x-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span class="text-xs text-gray-500">Potenciado por DRG </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- Scripts Stack -->
        @stack('scripts')

        <!-- Notification Toast (opcional) -->
        <div x-data="{ show: false, message: '' }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             @notify.window="show = true; message = $event.detail; setTimeout(() => show = false, 3000)"
             style="display: none;"
             class="fixed bottom-4 right-4 z-50">
            <div class="bg-white rounded-xl shadow-2xl border border-gray-200 p-4 max-w-sm">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900" x-text="message"></p>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    </body>
</html>