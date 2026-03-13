<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                   Material Para la Salud
                </h2>
                <p class="text-sm mt-1 text-blue-600 font-medium">Bienvenido de vuelta, <span class="font-semibold">{{ Auth::user()->name }}</span></p>
            </div>
            
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Métricas Principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Inventario Total -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-t-4 border-blue-500 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Inventario Total</p>
                                    Productos Registrados
                                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">{{ $products->count() }}</p>
                                    Con stock de:
                                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $units->count() }}</p>
                            
                                    <div class="flex items

                                <div class="flex items-center mt-2">
                                    <svg class="h-4 w-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                  
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="bg-blue-100 rounded-full p-4">
                                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipos en Renta -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-t-4 border-amber-500 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Equipos en Renta</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">89</p>
                                <div class="flex items-center mt-2">
                                    <svg class="h-4 w-4 text-amber-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm text-amber-600 font-semibold">23</span>
                                    <span class="text-xs text-gray-500 ml-1">vencen esta semana</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="bg-amber-100 rounded-full p-4">
                                    <svg class="h-8 w-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ventas del Mes -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-t-4 border-emerald-500 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Ventas del Mes</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">$487,250</p>
                                <div class="flex items-center mt-2">
                                    <svg class="h-4 w-4 text-emerald-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    <span class="text-sm text-emerald-600 font-semibold">+8%</span>
                                    <span class="text-xs text-gray-500 ml-1">vs mes anterior</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="bg-emerald-100 rounded-full p-4">
                                    <svg class="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Bajo -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-t-4 border-red-500 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Stock Bajo</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">15</p>
                                <div class="flex items-center mt-2">
                                    <svg class="h-4 w-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span class="text-sm text-red-600 font-semibold">Requieren reorden</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="bg-red-100 rounded-full p-4">
                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Alertas y Notificaciones -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl">
                        <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-4">
                            <h3 class="text-lg font-bold text-gray-900">Alertas Importantes</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <!-- Alerta Crítica -->
                                <div class="flex items-start p-4 bg-gradient-to-r from-red-50 to-red-50/50 border-l-4 border-red-500 rounded-r-lg shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex-shrink-0">
                                        <div class="bg-red-100 rounded-full p-2">
                                            <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-bold text-red-900">Monitor de Signos Vitales #MSV-001</p>
                                        <p class="text-sm text-red-700 mt-1">Renta vence mañana - Hospital San José</p>
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Urgente
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alerta Advertencia -->
                                <div class="flex items-start p-4 bg-gradient-to-r from-amber-50 to-amber-50/50 border-l-4 border-amber-500 rounded-r-lg shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex-shrink-0">
                                        <div class="bg-amber-100 rounded-full p-2">
                                            <svg class="h-5 w-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-bold text-amber-900">Stock Bajo: Guantes Quirúrgicos</p>
                                        <p class="text-sm text-amber-700 mt-1">Solo quedan 50 cajas - Punto de reorden: 100</p>
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                Advertencia
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alerta Información -->
                                <div class="flex items-start p-4 bg-gradient-to-r from-blue-50 to-blue-50/50 border-l-4 border-blue-500 rounded-r-lg shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex-shrink-0">
                                        <div class="bg-blue-100 rounded-full p-2">
                                            <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-bold text-blue-900">Nueva Orden de Compra</p>
                                        <p class="text-sm text-blue-700 mt-1">OC #2024-0156 por $45,000 - Requiere aprobación</p>
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Información
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl">
                        <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-4">
                            <h3 class="text-lg font-bold text-gray-900">Acciones Rápidas</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <a href="#" class="group flex items-center p-4 bg-gradient-to-r from-blue-50 to-blue-50/50 hover:from-blue-100 hover:to-blue-100/50 rounded-xl transition-all duration-200 border border-blue-100 hover:border-blue-200 hover:shadow-md">
                                    <div class="flex-shrink-0">
                                        <div class="bg-blue-500 rounded-lg p-2 group-hover:scale-110 transition-transform duration-200">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="ml-4 text-sm font-semibold text-blue-900 group-hover:text-blue-700">Registrar Venta</span>
                                    <svg class="ml-auto h-5 w-5 text-blue-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                
                                <a href="#" class="group flex items-center p-4 bg-gradient-to-r from-emerald-50 to-emerald-50/50 hover:from-emerald-100 hover:to-emerald-100/50 rounded-xl transition-all duration-200 border border-emerald-100 hover:border-emerald-200 hover:shadow-md">
                                    <div class="flex-shrink-0">
                                        <div class="bg-emerald-500 rounded-lg p-2 group-hover:scale-110 transition-transform duration-200">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="ml-4 text-sm font-semibold text-emerald-900 group-hover:text-emerald-700">Nueva Renta</span>
                                    <svg class="ml-auto h-5 w-5 text-emerald-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                
                                <a href="#" class="group flex items-center p-4 bg-gradient-to-r from-purple-50 to-purple-50/50 hover:from-purple-100 hover:to-purple-100/50 rounded-xl transition-all duration-200 border border-purple-100 hover:border-purple-200 hover:shadow-md">
                                    <div class="flex-shrink-0">
                                        <div class="bg-purple-500 rounded-lg p-2 group-hover:scale-110 transition-transform duration-200">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="ml-4 text-sm font-semibold text-purple-900 group-hover:text-purple-700">Gestionar Inventario</span>
                                    <svg class="ml-auto h-5 w-5 text-purple-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                
                                <a href="#" class="group flex items-center p-4 bg-gradient-to-r from-cyan-50 to-cyan-50/50 hover:from-cyan-100 hover:to-cyan-100/50 rounded-xl transition-all duration-200 border border-cyan-100 hover:border-cyan-200 hover:shadow-md">
                                    <div class="flex-shrink-0">
                                        <div class="bg-cyan-500 rounded-lg p-2 group-hover:scale-110 transition-transform duration-200">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="ml-4 text-sm font-semibold text-cyan-900 group-hover:text-cyan-700">Ver Reportes</span>
                                    <svg class="ml-auto h-5 w-5 text-cyan-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Detalladas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Equipos Más Rentados -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl">
                    <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-4">
                        <h3 class="text-lg font-bold text-gray-900">Equipos Más Rentados</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-5">
                            <div class="flex items-center">
                                <div class="flex items-center min-w-0 flex-1">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 block truncate">Ventiladores</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-500" style="width: 85%"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-lg font-bold text-gray-900 ml-4 w-8 text-right">24</span>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex items-center min-w-0 flex-1">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 block truncate">Monitores</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                            <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-2 rounded-full transition-all duration-500" style="width: 70%"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-lg font-bold text-gray-900 ml-4 w-8 text-right">18</span>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex items-center min-w-0 flex-1">
                                    <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                        <svg class="h-5 w-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 block truncate">Camas Hospital</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                            <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 h-2 rounded-full transition-all duration-500" style="width: 60%"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-lg font-bold text-gray-900 ml-4 w-8 text-right">15</span>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex items-center min-w-0 flex-1">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                        <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 block truncate">Bombas Infusión</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                            <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all duration-500" style="width: 45%"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-lg font-bold text-gray-900 ml-4 w-8 text-right">12</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl">
                    <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-4">
                        <h3 class="text-lg font-bold text-gray-900">Actividad Reciente</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-start space-x-4 p-3 rounded-xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-200">
                                <div class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-sm">
                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between">
                                        <p class="text-sm font-semibold text-gray-900">Nueva venta registrada</p>
                                        <p class="text-xs text-gray-500 whitespace-nowrap ml-2">Hace 2h</p>
                                    </div>
                                    <p class="text-sm text-emerald-600 font-bold mt-1">$12,500</p>
                                    <p class="text-xs text-gray-600 mt-1">Cliente: Hospital San José</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-4 p-3 rounded-xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-200">
                                <div class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 shadow-sm">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between">
                                        <p class="text-sm font-semibold text-gray-900">Equipo devuelto</p>
                                        <p class="text-xs text-gray-500 whitespace-nowrap ml-2">Hace 4h</p>
                                    </div>
                                    <p class="text-sm text-blue-600 font-medium mt-1">Monitor MSV-045</p>
                                    <p class="text-xs text-gray-600 mt-1">Cliente: Clínica Los Andes</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-4 p-3 rounded-xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-200">
                                <div class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-400 to-cyan-600 shadow-sm">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between">
                                        <p class="text-sm font-semibold text-gray-900">Stock actualizado</p>
                                        <p class="text-xs text-gray-500 whitespace-nowrap ml-2">Hace 6h</p>
                                    </div>
                                    <p class="text-sm text-cyan-600 font-medium mt-1">Mascarillas N95</p>
                                    <p class="text-xs text-gray-600 mt-1">Cantidad: +500 unidades</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4 p-3 rounded-xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-200">
                                <div class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 shadow-sm">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between">
                                        <p class="text-sm font-semibold text-gray-900">Nueva renta iniciada</p>
                                        <p class="text-xs text-gray-500 whitespace-nowrap ml-2">Hace 8h</p>
                                    </div>
                                    <p class="text-sm text-purple-600 font-medium mt-1">Ventilador VEN-023</p>
                                    <p class="text-xs text-gray-600 mt-1">Hospital Central</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer del Dashboard -->
            <div class="mt-6 pb-6">
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-4 border border-blue-100">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-gray-600">
                            Dashboard actualizado el <span class="font-semibold text-blue-600">{{ now()->format('d/m/Y') }}</span> a las <span class="font-semibold text-blue-600">{{ now()->format('H:i') }}</span> hrs
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>