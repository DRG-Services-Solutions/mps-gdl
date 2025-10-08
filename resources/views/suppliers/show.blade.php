<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Detalles del Proveedor') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Información completa del proveedor') }}
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('suppliers.edit', $supplier->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    {{ __('Editar') }}
                </a>
                <a href="{{ route('suppliers.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    {{ __('Volver') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Card Principal --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                {{-- Header con Estado --}}
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 h-12 w-12 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-building text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $supplier->name }}</h3>
                                <p class="text-sm text-gray-600">{{ __('Código:') }} <span class="font-mono font-semibold">{{ $supplier->code }}</span></p>
                            </div>
                        </div>
                        @if($supplier->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                {{ __('Activo') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-times-circle mr-1"></i>
                                {{ __('Inactivo') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Información de Contacto --}}
                <div class="p-6">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                        {{ __('Información de Contacto') }}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Persona de Contacto --}}
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Persona de Contacto') }}</p>
                                <p class="text-base font-semibold text-gray-900">{{ $supplier->contact_person ?? 'No especificado' }}</p>
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-envelope text-green-600"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Correo Electrónico') }}</p>
                                <a href="mailto:{{ $supplier->email }}" class="text-base font-semibold text-indigo-600 hover:text-indigo-800">
                                    {{ $supplier->email }}
                                </a>
                            </div>
                        </div>

                        {{-- Teléfono --}}
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-phone text-purple-600"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Teléfono') }}</p>
                                <p class="text-base font-semibold text-gray-900">{{ $supplier->phone ?? 'No especificado' }}</p>
                            </div>
                        </div>

                        {{-- Dirección --}}
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-map-marker-alt text-orange-600"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Dirección') }}</p>
                                <p class="text-base font-semibold text-gray-900">{{ $supplier->address ?? 'No especificada' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card de Órdenes de Compra --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-file-invoice text-gray-600"></i>
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('Órdenes de Compra') }}</h4>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $supplier->purchaseOrders->count() }} {{ __('órdenes') }}
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    @if($supplier->purchaseOrders->count() > 0)
                        <div class="space-y-3">
                            @foreach($supplier->purchaseOrders->take(5) as $order)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Orden #{{ $order->id }}</p>
                                        <p class="text-xs text-gray-600">{{ $order->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        {{ __('Ver detalles') }} →
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        @if($supplier->purchaseOrders->count() > 5)
                            <div class="mt-4 text-center">
                                <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                    {{ __('Ver todas las órdenes') }} ({{ $supplier->purchaseOrders->count() }})
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                            <p class="text-gray-500">{{ __('No hay órdenes de compra registradas para este proveedor') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>