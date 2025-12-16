<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Aplicar Prearmado a Cotización: {{ $surgicalKit->name }}
            </h2>
            <a href="{{ route('surgical-kits.show', $surgicalKit) }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            {{-- Mensajes --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Alert de disponibilidad --}}
            @if(!$availability['all_available'])
                <div class="mb-6 p-4 bg-yellow-100 border border-yellow-400 rounded-md">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-yellow-800">
                                ⚠ Stock insuficiente detectado
                            </p>
                            <p class="mt-1 text-sm text-yellow-700">
                                Algunos productos no tienen inventario completo. Al aplicar el prearmado se agregarán solo las cantidades disponibles.
                                <a href="{{ route('surgical-kits.check-stock', $surgicalKit) }}" class="font-medium underline">Ver detalle completo</a>
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-6 p-4 bg-green-100 border border-green-400 rounded-md">
                    <div class="flex">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                ✓ Stock completo disponible
                            </p>
                            <p class="mt-1 text-sm text-green-700">
                                Todos los productos del prearmado están disponibles en inventario.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Información del prearmado --}}
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Información del Prearmado</h3>
                    
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Código</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $surgicalKit->code }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tipo de Cirugía</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $surgicalKit->surgery_type }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total de Piezas</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $surgicalKit->total_pieces }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Seleccionar cotización --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Selecciona una Cotización en Borrador</h3>
                    
                    @if($quotations->count() > 0)
                        <div class="space-y-4">
                            @foreach($quotations as $quotation)
                                <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $quotation->quotation_number }}
                                                </span>
                                                <span class="text-sm font-medium text-gray-900">
                                                    {{ $quotation->hospital->name }}
                                                </span>
                                            </div>
                                            
                                            <div class="mt-2 grid grid-cols-1 gap-2 md:grid-cols-3">
                                                <div>
                                                    <p class="text-xs text-gray-500">Doctor</p>
                                                    <p class="text-sm text-gray-900">{{ $quotation->doctor->full_name }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500">Tipo de Cirugía</p>
                                                    <p class="text-sm text-gray-900">{{ $quotation->surgery_type ?? 'No especificado' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500">Fecha</p>
                                                    <p class="text-sm text-gray-900">{{ $quotation->quotation_date->format('d/m/Y') }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ml-4">
                                            <form method="POST" action="{{ route('surgical-kits.apply-to-quotation', $surgicalKit) }}">
                                                @csrf
                                                <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
                                                @if(!$availability['all_available'])
                                                    <input type="hidden" name="force" value="1">
                                                @endif
                                                
                                                <button type="submit" 
                                                        onclick="return confirm('¿Confirmas aplicar el prearmado {{ $surgicalKit->name }} a la cotización {{ $quotation->quotation_number }}?')"
                                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md {{ $availability['all_available'] ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700' }}">
                                                    @if($availability['all_available'])
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Aplicar Prearmado
                                                    @else
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                        </svg>
                                                        Aplicar Parcialmente
                                                    @endif
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Información adicional --}}
                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                            <div class="flex">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-800">
                                        <strong>Nota:</strong> Los productos del prearmado se agregarán automáticamente a la cotización seleccionada. 
                                        Los ProductUnits disponibles se asignarán hasta completar las cantidades requeridas.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-4 text-lg font-medium text-gray-900">No hay cotizaciones en borrador</p>
                            <p class="mt-2 text-sm text-gray-500">
                                Crea una nueva cotización en borrador para poder aplicar este prearmado.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('quotations.create') }}" 
                                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                                    Nueva Cotización
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>