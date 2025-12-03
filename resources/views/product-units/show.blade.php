<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('product-units.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors duration-150">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Detalle de Unidad') }}
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimir
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('product-units.index') }}" 
                           class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            Inventario
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $productUnit->unique_identifier }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Principal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Tarjeta Principal -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-white<x-app-layout">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('product-units.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors duration-150">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Detalle de Unidad') }}
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimir
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('product-units.index') }}" 
                           class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            Inventario
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $productUnit->unique_identifier }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Principal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Tarjeta Principal -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-white bg-opacity-20 rounded-lg p-3">
                                        @if($productUnit->epc)
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        @else
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-2xl font-bold text-white">{{ $productUnit->unique_identifier }}</h3>
                                        <p class="mt-1 text-indigo-100">{{ $productUnit->epc ? 'RFID' : 'Número de Serie' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold shadow-lg
                                        {{ $productUnit->status_color === 'green' ? 'bg-green-500 text-white' : '' }}
                                        {{ $productUnit->status_color === 'blue' ? 'bg-blue-500 text-white' : '' }}
                                        {{ $productUnit->status_color === 'yellow' ? 'bg-yellow-500 text-white' : '' }}
                                        {{ $productUnit->status_color === 'purple' ? 'bg-purple-500 text-white' : '' }}
                                        {{ $productUnit->status_color === 'orange' ? 'bg-orange-500 text-white' : '' }}
                                        {{ $productUnit->status_color === 'red' ? 'bg-red-500 text-white' : '' }}
                                        {{ $productUnit->status_color === 'gray' ? 'bg-gray-500 text-white' : '' }}">
                                        {{ $productUnit->status_label }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Producto -->
                        <div class="p-6 border-b border-gray-200">
                            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                Información del Producto
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Código</p>
                                    <p class="mt-1 text-sm text-gray-900 font-semibold">{{ $productUnit->product->code }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Nombre</p>
                                    <p class="mt-1 text-sm text-gray-900 font-semibold">{{ $productUnit->product->name }}</p>
                                </div>
                                @if($productUnit->product->description)
                                    <div class="col-span-2">
                                        <p class="text-sm font-medium text-gray-500">Descripción</p>
                                        <p class="mt-1 text-sm text-gray-900">{{ $productUnit->product->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Información de Lote y Fechas -->
                        <div class="p-6 border-b border-gray-200">
                            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Lote y Fechas
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Número de Lote</p>
                                    <p class="mt-1 text-sm text-gray-900 font-semibold">{{ $productUnit->batch_number ?? 'No aplica' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Fecha de Fabricación</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $productUnit->manufacture_date ? $productUnit->manufacture_date->format('d/m/Y') : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Fecha de Adquisición</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $productUnit->acquisition_date ? $productUnit->acquisition_date->format('d/m/Y') : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Fecha de Caducidad</p>
                                    @if($productUnit->expiration_date)
                                        <p class="mt-1 text-sm font-semibold
                                            {{ $productUnit->isExpired() ? 'text-red-600' : ($productUnit->isExpiringSoon() ? 'text-orange-600' : 'text-green-600') }}">
                                            {{ $productUnit->expiration_date->format('d/m/Y') }}
                                        </p>
                                        @if($productUnit->isExpired())
                                            <p class="text-xs text-red-600 font-semibold">⚠️ Producto caducado</p>
                                        @elseif($productUnit->isExpiringSoon())
                                            <p class="text-xs text-orange-600">⚠️ Caduca en {{ $productUnit->days_until_expiration }} días</p>
                                        @else
                                            <p class="text-xs text-green-600">✓ {{ $productUnit->days_until_expiration }} días restantes</p>
                                        @endif
                                    @else
                                        <p class="mt-1 text-sm text-gray-900">No aplica</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="p-6 border-b border-gray-200">
                            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                Ubicación Actual
                            </h4>
                            @if($productUnit->currentLocation)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Código de Ubicación</p>
                                            <p class="mt-1 text-lg font-bold text-gray-900">{{ $productUnit->currentLocation->code }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Nombre</p>
                                            <p class="mt-1 text-lg font-bold text-gray-900">{{ $productUnit->currentLocation->name }}</p>
                                        </div>
                                    </div>
                                    @if($productUnit->currentLocation->description)
                                        <p class="mt-2 text-sm text-gray-600">{{ $productUnit->currentLocation->description }}</p>
                                    @endif
                                </div>
                            @else
                                <p class="text-sm text-gray-500">Sin ubicación asignada</p>
                            @endif
                        </div>

                        <!-- Información de Costos -->
                        @if($productUnit->acquisition_cost)
                            <div class="p-6 border-b border-gray-200">
                                <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Información Financiera
                                </h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Costo de Adquisición</p>
                                        <p class="mt-1 text-lg font-bold text-green-600">${{ number_format($productUnit->acquisition_cost, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Notas -->
                        @if($productUnit->notes)
                            <div class="p-6 border-b border-gray-200">
                                <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Notas
                                </h4>
                                <p class="text-sm text-gray-700">{{ $productUnit->notes }}</p>
                            </div>
                        @endif

                        <!-- Auditoría -->
                        <div class="p-6 bg-gray-50">
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Información de Registro</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="font-medium text-gray-500">Registrado por</p>
                                    <p class="mt-1 text-gray-900">{{ $productUnit->createdBy->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-500">Fecha de Registro</p>
                                    <p class="mt-1 text-gray-900">{{ $productUnit->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                @if($productUnit->updated_at != $productUnit->created_at)
                                    <div>
                                        <p class="font-medium text-gray-500">Última Actualización</p>
                                        <p class="mt-1 text-gray-900">{{ $productUnit->updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    @if($productUnit->updatedBy)
                                        <div>
                                            <p class="font-medium text-gray-500">Actualizado por</p>
                                            <p class="mt-1 text-gray-900">{{ $productUnit->updatedBy->name }}</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Lateral -->
                <div class="space-y-6">
                    <!-- Código QR -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                            <h4 class="text-lg font-bold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                                Código QR
                            </h4>
                        </div>
                        <div class="p-6">
                            <div class="bg-white border-4 border-gray-200 rounded-lg p-4">
                                <!-- Aquí iría el QR generado con una librería -->
                                <div class="flex items-center justify-center bg-gray-100 rounded-lg p-8">
                                    <div class="text-center">
                                        <svg class="w-48 h-48 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                        </svg>
                                        <p class="mt-4 text-xs text-gray-500">Código QR del producto</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <p class="text-xs font-mono text-gray-600 bg-gray-100 rounded px-3 py-2">
                                    {{ $productUnit->unique_identifier }}
                                </p>
                            </div>
                            <button onclick="window.print()" 
                                    class="mt-4 w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Descargar Etiqueta
                            </button>
                        </div>
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                            <h4 class="text-lg font-bold text-gray-900">Acciones Rápidas</h4>
                        </div>
                        <div class="p-6 space-y-3">
                            @if($productUnit->status === 'available')
                                <button class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Registrar Salida
                                </button>
                            @endif
                            
                            <button class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                Cambiar Ubicación
                            </button>
                            
                            <button class="w-full inline-flex items-center justify-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar Información
                            </button>
                            
                            <a href="{{ route('product-units.index') }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Volver al Inventario
                            </a>
                        </div>
                    </div>

                    <!-- Alertas -->
                    @if($productUnit->isExpiringSoon() || $productUnit->isExpired() || $productUnit->needsMaintenance())
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border-l-4 
                            {{ $productUnit->isExpired() ? 'border-red-500' : 'border-orange-500' }}">
                            <div class="p-6">
                                <h4 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 {{ $productUnit->isExpired() ? 'text-red-600' : 'text-orange-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Alertas
                                </h4>
                                <div class="space-y-2">
                                    @if($productUnit->isExpired())
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                            <p class="text-sm font-semibold text-red-800">⚠️ Producto Caducado</p>
                                            <p class="text-xs text-red-600 mt-1">Este producto debe ser retirado del inventario</p>
                                        </div>
                                    @elseif($productUnit->isExpiringSoon())
                                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                                            <p class="text-sm font-semibold text-orange-800">⚠️ Próximo a Caducar</p>
                                            <p class="text-xs text-orange-600 mt-1">Caduca en {{ $productUnit->days_until_expiration }} días</p>
                                        </div>
                                    @endif
                                    
                                    @if($productUnit->needsMaintenance())
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                            <p class="text-sm font-semibold text-yellow-800">🔧 Mantenimiento Requerido</p>
                                            <p class="text-xs text-yellow-600 mt-1">Vencimiento: {{ $productUnit->next_maintenance_date->format('d/m/Y') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Estilos para impresión -->
    <style media="print">
        @page {
            margin: 1cm;
        }
        
        body {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
        
        /* Ocultar elementos no necesarios en impresión */
        nav, header, .no-print {
            display: none !important;
        }
        
        /* Ajustar el QR para impresión */
        .qr-code {
            page-break-inside: avoid;
        }
    </style>
</x-app-layout>