<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Detalles del Layout') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Ubicación **{{ $productLayout->shelf }}-{{ $productLayout->level }}-{{ $productLayout->position }}**
                </p>
            </div>
            
            {{-- Botón de Edición Rápida --}}
            <a href="{{ route('product_layouts.edit', $productLayout) }}" 
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                <i class="fas fa-edit mr-2"></i>
                {{ __('Editar Layout') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden divide-y divide-gray-200">
                
                {{-- SECCIÓN DE UBICACIÓN FÍSICA --}}
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('Ubicación Geográfica') }}</h3>
                    
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        
                        {{-- Bodega Principal --}}
                        <div class="border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Bodega Principal') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">
                                {{ $productLayout->storageLocation->name ?? 'N/A' }}
                            </dd>
                            <dd class="text-sm text-gray-600">Código: {{ $productLayout->storageLocation->code ?? 'N/A' }}</dd>
                        </div>

                        {{-- Producto Asociado --}}
                        <div class="border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">{{ __('ID de Producto') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">
                                #{{ $productLayout->product_id }} 
                            </dd>
                            <dd class="text-sm text-gray-600">
                                {{-- Aquí podrías mostrar el nombre del producto si hubieras cargado la relación 'product' --}}
                                Producto sin nombre cargado
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- SECCIÓN DE COORDENADAS EXACTAS --}}
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('Coordenadas del Layout') }}</h3>
                    
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-4">
                        
                        {{-- Estante (Shelf) --}}
                        <div class="border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500"><i class="fas fa-warehouse mr-1 text-indigo-500"></i> {{ __('Estante') }}</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $productLayout->shelf }}</dd>
                        </div>
                        
                        {{-- Nivel (Level) --}}
                        <div class="border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500"><i class="fas fa-layer-group mr-1 text-indigo-500"></i> {{ __('Nivel') }}</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $productLayout->level }}</dd>
                        </div>
                        
                        {{-- Posición (Position) --}}
                        <div class="border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500"><i class="fas fa-map-marker-alt mr-1 text-indigo-500"></i> {{ __('Posición') }}</dt>
                            {{-- Formateamos a 2 decimales para precisión visual --}}
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($productLayout->position, 2) }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- SECCIÓN DE METADATOS --}}
                <div class="p-6 bg-gray-50">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('Metadatos') }}</h3>
                    
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Creado el') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $productLayout->created_at->format('d/m/Y H:i') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Última Actualización') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $productLayout->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
                
                {{-- Botón Volver --}}
                <div class="p-6 pt-4 flex justify-end">
                    <a href="{{ route('product_layouts.index') }}" 
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 shadow-sm transition-colors">
                        {{ __('Volver al Listado') }}
                    </a>
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>