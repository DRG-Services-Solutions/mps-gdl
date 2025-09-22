<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Dashboard') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Bienvenido de vuelta, {{ Auth::user()->name }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">{{ now()->format('d/m/Y') }}</p>
                <p class="text-xs text-gray-400">{{ now()->format('H:i') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Métricas Principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Inventario Total -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-500">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Inventario Total</p>
                                <p class="text-2xl font-semibold text-gray-900">1,247</p>
                                <p class="text-xs text-green-600">+12% vs mes anterior</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipos en Renta -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-500">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Equipos en Renta</p>
                                <p class="text-2xl font-semibold text-gray-900">89</p>
                                <p class="text-xs text-blue-600">23 vencen esta semana</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ventas del Mes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-500">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Ventas del Mes</p>
                                <p class="text-2xl font-semibold text-gray-900">$487,250</p>
                                <p class="text-xs text-green-600">+8% vs mes anterior</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Bajo -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-red-500">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Stock Bajo</p>
                                <p class="text-2xl font-semibold text-gray-900">15</p>
                                <p class="text-xs text-red-600">Requieren reorden</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Alertas y Notificaciones -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Alertas Importantes</h3>
                            <div class="space-y-3">
                                <!-- Alerta Crítica -->
                                <div class="flex items-start p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <svg class="h-5 w-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800">Monitor de Signos Vitales #MSV-001</p>
                                        <p class="text-sm text-red-600">Renta vence mañana - Hospital San José</p>
                                    </div>
                                </div>

                                <!-- Alerta Advertencia -->
                                <div class="flex items-start p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <svg class="h-5 w-5 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-yellow-800">Stock Bajo: Guantes Quirúrgicos</p>
                                        <p class="text-sm text-yellow-600">Solo quedan 50 cajas - Reorder Point: 100</p>
                                    </div>
                                </div>

                                <!-- Alerta Información -->
                                <div class="flex items-start p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-blue-800">Nueva Orden de Compra</p>
                                        <p class="text-sm text-blue-600">OC #2024-0156 por $45,000 - Requiere aprobación</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="space-y-6">
                    <!-- Acciones Rápidas -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones Rápidas</h3>
                            <div class="space-y-3">
                                <a href="#" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    <span class="ml-3 text-sm font-medium text-blue-900">Registrar Venta</span>
                                </a>
                                
                                <a href="#" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200">
                                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="ml-3 text-sm font-medium text-green-900">Nueva Renta</span>
                                </a>
                                
                                <a href="#" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200">
                                    <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <span class="ml-3 text-sm font-medium text-purple-900">Gestionar Inventario</span>
                                </a>
                                
                                <a href="#" class="flex items-center p-3 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors duration-200">
                                    <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="ml-3 text-sm font-medium text-orange-900">Ver Reportes</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Próximos Vencimientos -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Próximos Vencimientos</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Ventilador #VEN-023</p>
                                        <p class="text-xs text-gray-500">Hospital ABC</p>
                                    </div>
                                    <span class="text-xs font-medium text-red-600">Mañana</span>
                                </div>
                                
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Mesa Quirúrgica #MQ-008</p>
                                        <p class="text-xs text-gray-500">Clínica San Rafael</p>
                                    </div>
                                    <span class="text-xs font-medium text-yellow-600">3 días</span>
                                </div>
                                
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Bomba de Infusión #BI-012</p>
                                        <p class="text-xs text-gray-500">Hospital Central</p>
                                    </div>
                                    <span class="text-xs font-medium text-yellow-600">5 días</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Detalladas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Equipos Más Rentados -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Equipos Más Rentados</h3>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center min-w-0 flex-1">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-4 flex-shrink-0"></div>
                                    <span class="text-sm font-medium text-gray-700 truncate">Ventiladores</span>
                                </div>
                                <div class="flex items-center ml-4">
                                    <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-4">
                                        <div class="bg-blue-500 h-2.5 rounded-full transition-all duration-300" style="width: 85%"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 w-6 text-right">24</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center min-w-0 flex-1">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-4 flex-shrink-0"></div>
                                    <span class="text-sm font-medium text-gray-700 truncate">Monitores</span>
                                </div>
                                <div class="flex items-center ml-4">
                                    <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-4">
                                        <div class="bg-green-500 h-2.5 rounded-full transition-all duration-300" style="width: 70%"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 w-6 text-right">18</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center min-w-0 flex-1">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-4 flex-shrink-0"></div>
                                    <span class="text-sm font-medium text-gray-700 truncate">Camas Hospital</span>
                                </div>
                                <div class="flex items-center ml-4">
                                    <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-4">
                                        <div class="bg-yellow-500 h-2.5 rounded-full transition-all duration-300" style="width: 60%"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 w-6 text-right">15</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center min-w-0 flex-1">
                                    <div class="w-3 h-3 bg-purple-500 rounded-full mr-4 flex-shrink-0"></div>
                                    <span class="text-sm font-medium text-gray-700 truncate">Bombas Infusión</span>
                                </div>
                                <div class="flex items-center ml-4">
                                    <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-4">
                                        <div class="bg-purple-500 h-2.5 rounded-full transition-all duration-300" style="width: 45%"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 w-6 text-right">12</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Actividad Reciente</h3>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-500 flex-shrink-0">
                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">Nueva venta registrada por <span class="text-green-600 font-semibold">$12,500</span></p>
                                        <p class="text-xs text-gray-500 whitespace-nowrap ml-2">Hace 2h</p>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Cliente: Hospital San José</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500 flex-shrink-0">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">Equipo <span class="font-semibold">Monitor MSV-045</span> devuelto</p>
                                        <p class="text-xs text-gray-500 whitespace-nowrap ml-2">Hace 4h</p>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Cliente: Clínica Los Andes</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-500 flex-shrink-0">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">Stock actualizado: <span class="font-semibold">Mascarillas N95</span></p>
                                        <p class="text-xs text-gray-500 whitespace-nowrap ml-2">Hace 6h</p>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Cantidad: +500 unidades</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-500 flex-shrink-0">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">Nueva renta iniciada</p>
                                        <p class="text-xs text-gray-500 whitespace-nowrap ml-2">Hace 8h</p>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Ventilador VEN-023 - Hospital Central</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección adicional para mejor visualización -->
            <div class="mt-8 pb-8">
                <div class="text-center">
                    <p class="text-sm text-gray-500">Dashboard actualizado el {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>