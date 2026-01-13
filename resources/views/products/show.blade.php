<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="text-sm font-medium">{{ __('Volver al catálogo') }}</span>
                </a>
                <div class="border-l border-gray-300 pl-4">
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ __('Detalles del Producto') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Información completa del producto') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('products.edit', $product) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                    <i class="fas fa-edit mr-2"></i>
                    {{ __('Editar Producto') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Encabezado del Producto --}}
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="h-20 w-20 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                                    <i class="fas fa-box text-white text-3xl"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span class="inline-flex items-center">
                                        <i class="fas fa-barcode mr-2"></i>
                                        <strong class="mr-1">{{ __('Código:') }}</strong>
                                        {{ $product->code }}
                                    </span>
                                </div>
                                @if($product->description)
                                    <p class="mt-3 text-gray-700 leading-relaxed">{{ $product->description }}</p>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Badge de Estado --}}
                        <div>
                            @switch($product->status)
                                @case('active')
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800 shadow-sm">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        {{ __('Activo') }}
                                    </span>
                                    @break
                                @case('inactive')
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 shadow-sm">
                                        <i class="fas fa-pause-circle mr-2"></i>
                                        {{ __('Inactivo') }}
                                    </span>
                                    @break
                                @case('discontinued')
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-800 shadow-sm">
                                        <i class="fas fa-ban mr-2"></i>
                                        {{ __('Descontinuado') }}
                                    </span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Columna Principal --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- UBICACIONES FÍSICAS --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-map-marker-alt text-indigo-600 mr-2"></i>
                                {{ __('Ubicaciones Físicas') }}
                                @if($product->productLayouts->isNotEmpty())
                                    <span class="ml-2 px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-bold rounded-full">
                                        {{ $product->productLayouts->count() }}
                                    </span>
                                @endif
                            </h3>
                            <a href="{{ route('product_layouts.index') }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-white hover:bg-indigo-50 text-indigo-600 text-sm font-medium rounded-lg border border-indigo-200 transition-all duration-200">
                                <i class="fas fa-plus mr-2"></i>
                                {{ __('Asignar Nueva Ubicación') }}
                            </a>
                        </div>
                        <div class="p-6">
                            @if($product->productLayouts->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($product->productLayouts as $layout)
                                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-indigo-50 rounded-lg border border-indigo-100 hover:border-indigo-300 transition-all duration-200 group">
                                            <div class="flex items-center gap-4 flex-1">
                                                {{-- Icono de Ubicación --}}
                                                <div class="flex-shrink-0">
                                                    <div class="h-12 w-12 rounded-lg bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                                                        <i class="fas fa-warehouse text-indigo-600 text-xl"></i>
                                                    </div>
                                                </div>
                                                
                                                {{-- Información de Ubicación --}}
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <h4 class="font-semibold text-gray-900">
                                                            {{ $layout->storageLocation->name ?? 'N/A' }}
                                                        </h4>
                                                        <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 text-xs font-bold rounded">
                                                            {{ $layout->storageLocation->code ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="flex items-center gap-4 text-sm text-gray-600">
                                                        <span class="inline-flex items-center">
                                                            <i class="fas fa-layer-group text-indigo-500 mr-1"></i>
                                                            <strong class="mr-1">Estante:</strong> {{ $layout->shelf }}
                                                        </span>
                                                        <span class="inline-flex items-center">
                                                            <i class="fas fa-sort text-indigo-500 mr-1"></i>
                                                            <strong class="mr-1">Nivel:</strong> {{ $layout->level }}
                                                        </span>
                                                        <span class="inline-flex items-center">
                                                            <i class="fas fa-map-pin text-indigo-500 mr-1"></i>
                                                            <strong class="mr-1">Posición:</strong> {{ number_format($layout->position, 2) }}
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="mt-2">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-600 text-white">
                                                            <i class="fas fa-barcode mr-1"></i>
                                                            {{ $layout->full_location_code }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Acciones --}}
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('product_layouts.show', $layout) }}" 
                                                   class="inline-flex items-center px-3 py-2 bg-white hover:bg-indigo-50 text-indigo-600 rounded-lg border border-indigo-200 transition-all duration-200"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <form action="{{ route('product_layouts.remove-product', $layout) }}" 
                                                      method="POST"
                                                      onsubmit="return confirm('¿Estás seguro de que deseas remover este producto de esta ubicación?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg border border-red-200 transition-all duration-200"
                                                            title="Remover de esta ubicación">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Resumen de ubicaciones --}}
                                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-center gap-2 text-sm text-blue-800">
                                        <i class="fas fa-info-circle"></i>
                                        <p>
                                            <strong>{{ $product->productLayouts->count() }}</strong> 
                                            {{ $product->productLayouts->count() === 1 ? 'ubicación asignada' : 'ubicaciones asignadas' }} 
                                            en 
                                            <strong>{{ $product->productLayouts->pluck('storageLocation')->unique('id')->count() }}</strong>
                                            {{ $product->productLayouts->pluck('storageLocation')->unique('id')->count() === 1 ? 'bodega' : 'bodegas' }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 mb-4">
                                        <i class="fas fa-map-marker-alt text-amber-600 text-2xl"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">
                                        {{ __('Sin ubicaciones asignadas') }}
                                    </h4>
                                    <p class="text-sm text-gray-600 mb-4">
                                        {{ __('Este producto no tiene ninguna ubicación física asignada en las bodegas') }}
                                    </p>
                                    <a href="{{ route('product_layouts.index') }}" 
                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        {{ __('Asignar Primera Ubicación') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- CLASIFICACIÓN --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-sitemap text-indigo-600 mr-2"></i>
                                {{ __('Clasificación') }}
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Proveedor --}}
                                <div>
                                    <label class="text-sm font-medium text-gray-500 mb-1 block">
                                        <i class="fas fa-industry text-gray-400 mr-1"></i>
                                        {{ __('Proveedor') }}
                                    </label>
                                    @if($product->supplier)
                                        <p class="text-base font-semibold text-gray-900">{{ $product->supplier->name }}</p>
                                    @else
                                        <p class="text-base text-gray-400 italic">{{ __('Sin proveedor asignado') }}</p>
                                    @endif
                                </div>

                                {{-- Categoría --}}
                                <div>
                                    <label class="text-sm font-medium text-gray-500 mb-1 block">
                                        <i class="fas fa-tags text-gray-400 mr-1"></i>
                                        {{ __('Categoría') }}
                                    </label>
                                    @if($product->category)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-tag mr-1"></i>
                                            {{ $product->category->name }}
                                        </span>
                                    @else
                                        <p class="text-base text-gray-400 italic">{{ __('Sin categoría') }}</p>
                                    @endif
                                </div>

                                {{-- Subcategoría --}}
                                <div>
                                    <label class="text-sm font-medium text-gray-500 mb-1 block">
                                        <i class="fas fa-layer-group text-gray-400 mr-1"></i>
                                        {{ __('Subcategoría') }}
                                    </label>
                                    @if($product->subcategory)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                            {{ $product->subcategory->name }}
                                        </span>
                                    @else
                                        <p class="text-base text-gray-400 italic">{{ __('Sin subcategoría') }}</p>
                                    @endif
                                </div>

                                
                            </div>
                        </div>
                    </div>

                    {{-- CARACTERÍSTICAS --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-check-square text-indigo-600 mr-2"></i>
                                {{ __('Características y Requisitos') }}
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Esterilización --}}
                                <div class="flex items-center justify-between p-4 rounded-lg border-2 {{ $product->requires_sterilization ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                                    <div class="flex items-center">
                                        @if($product->requires_sterilization)
                                            <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                                        @else
                                            <i class="fas fa-times-circle text-gray-400 text-2xl mr-3"></i>
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">{{ __('Esterilización') }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $product->requires_sterilization ? __('Requerida') : __('No requerida') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Refrigeración --}}
                                <div class="flex items-center justify-between p-4 rounded-lg border-2 {{ $product->requires_refrigeration ? 'border-blue-200 bg-blue-50' : 'border-gray-200 bg-gray-50' }}">
                                    <div class="flex items-center">
                                        @if($product->requires_refrigeration)
                                            <i class="fas fa-check-circle text-blue-600 text-2xl mr-3"></i>
                                        @else
                                            <i class="fas fa-times-circle text-gray-400 text-2xl mr-3"></i>
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">{{ __('Refrigeración') }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $product->requires_refrigeration ? __('Requerida') : __('No requerida') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Control de Temperatura --}}
                                <div class="flex items-center justify-between p-4 rounded-lg border-2 {{ $product->requires_temperature ? 'border-orange-200 bg-orange-50' : 'border-gray-200 bg-gray-50' }}">
                                    <div class="flex items-center">
                                        @if($product->requires_temperature)
                                            <i class="fas fa-check-circle text-orange-600 text-2xl mr-3"></i>
                                        @else
                                            <i class="fas fa-times-circle text-gray-400 text-2xl mr-3"></i>
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">{{ __('Temperatura') }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $product->requires_temperature ? __('Control requerido') : __('Sin control') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TIPO DE TRACKING --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-satellite-dish text-indigo-600 mr-2"></i>
                                {{ __('Control y Trazabilidad') }}
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @switch($product->tracking_type)
                                        @case('code')
                                            <div class="h-16 w-16 rounded-xl bg-green-100 flex items-center justify-center">
                                                <i class="fas fa-boxes text-green-600 text-2xl"></i>
                                            </div>
                                            @break
                                        @case('rfid')
                                            <div class="h-16 w-16 rounded-xl bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-wifi text-blue-600 text-2xl"></i>
                                            </div>
                                            @break
                                        @case('serial')
                                            <div class="h-16 w-16 rounded-xl bg-yellow-100 flex items-center justify-center">
                                                <i class="fas fa-hashtag text-yellow-600 text-2xl"></i>
                                            </div>
                                            @break
                                    @endswitch
                                </div>
                                <div class="flex-1">
                                    <label class="text-sm font-medium text-gray-500 mb-1 block">
                                        {{ __('Tipo de Rastreo') }}
                                    </label>
                                    @switch($product->tracking_type)
                                        @case('code')
                                            <p class="text-xl font-bold text-gray-900">{{ __('Code') }}</p>
                                            <p class="text-sm text-gray-600">{{ __('Rastreo por lote - ideal para productos consumibles') }}</p>
                                            @break
                                        @case('rfid')
                                            <p class="text-xl font-bold text-gray-900">{{ __('RFID') }}</p>
                                            <p class="text-sm text-gray-600">{{ __('Rastreo individual con tecnología RFID') }}</p>
                                            @break
                                        @case('serial')
                                            <p class="text-xl font-bold text-gray-900">{{ __('Serial') }}</p>
                                            <p class="text-sm text-gray-600">{{ __('Rastreo único por número de serie') }}</p>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Columna Lateral --}}
                <div class="space-y-6">
                    
                    {{-- INVENTARIO --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-warehouse text-indigo-600 mr-2"></i>
                                {{ __('Inventario') }}
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            {{-- Stock Mínimo --}}
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-box text-gray-600 text-xl mr-3"></i>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">{{ __('Stock Mínimo') }}</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ $product->minimum_stock }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Precio de Lista --}}
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex items-center">
                                    <i class="fas fa-dollar-sign text-green-600 text-xl mr-3"></i>
                                    <div>
                                        <p class="text-xs font-medium text-green-700">{{ __('Precio de Lista') }}</p>
                                        <p class="text-2xl font-bold text-green-900">${{ number_format($product->list_price, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- INFORMACIÓN DEL SISTEMA --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                                {{ __('Información del Sistema') }}
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex items-start">
                                <i class="fas fa-calendar-plus text-gray-400 mt-1 mr-3"></i>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">{{ __('Fecha de Creación') }}</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $product->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-calendar-check text-gray-400 mt-1 mr-3"></i>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">{{ __('Última Actualización') }}</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $product->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-hashtag text-gray-400 mt-1 mr-3"></i>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">{{ __('ID del Producto') }}</p>
                                    <p class="text-sm font-semibold text-gray-900">#{{ $product->id }}</p>
                                </div>
                            </div>
                            @if($product->deleted_at)
                                <div class="flex items-start pt-3 border-t border-red-200">
                                    <i class="fas fa-trash-alt text-red-500 mt-1 mr-3"></i>
                                    <div>
                                        <p class="text-xs font-medium text-red-700">{{ __('Fecha de Eliminación') }}</p>
                                        <p class="text-sm font-semibold text-red-900">{{ $product->deleted_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ACCIONES RÁPIDAS --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-bolt text-indigo-600 mr-2"></i>
                                {{ __('Acciones Rápidas') }}
                            </h3>
                        </div>
                        
                        <div class="p-6 space-y-3">

                            <a href="{{ route('products.edit', $product) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                                <i class="fas fa-edit mr-2"></i>
                                {{ __('Editar Producto') }}
                            </a>
                            
                            <form action="{{ route('products.destroy', $product) }}" method="POST" 
                                  onsubmit="return confirm('¿Está seguro de eliminar este producto?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-white hover:bg-red-50 text-red-700 font-medium rounded-lg border-2 border-red-300 transition-all duration-200">
                                    <i class="fas fa-trash-alt mr-2"></i>
                                    {{ __('Eliminar Producto') }}
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>