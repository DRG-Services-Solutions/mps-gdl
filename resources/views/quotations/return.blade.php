<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-undo mr-2 text-green-600"></i>
                    {{ __('Registrar Retorno de Cirugía') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $quotation->quotation_number }} - {{ $quotation->hospital->name }}</p>
            </div>
            <a href="{{ route('quotations.show', $quotation) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Info Box -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">
                            Instrucciones
                        </h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Marca cada producto que <strong>SÍ regresó</strong> del hospital.</p>
                            <p class="mt-1">Los productos <strong>NO marcados</strong> se considerarán como <strong>faltantes/usados</strong>.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Surgery Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Hospital</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation->hospital->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tipo de Cirugía</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation->surgery_type ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Fecha</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation->surgery_date?->format('d/m/Y') ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Return Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('quotations.register-return', $quotation) }}">
                    @csrf
                    
                    <div class="p-6 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-list-check mr-2"></i>Productos Enviados
                        </h3>

                        <div class="space-y-3">
                            @foreach($quotation->items as $item)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4 flex-1">
                                            <!-- Checkbox -->
                                            <div class="flex items-center h-5">
                                                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                                <input id="item-{{ $item->id }}" 
                                                       name="items[{{ $loop->index }}][returned]" 
                                                       type="checkbox" 
                                                       value="1"
                                                       class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer">
                                            </div>
                                            
                                            <!-- Product Info -->
                                            <label for="item-{{ $item->id }}" class="flex-1 cursor-pointer">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">
                                                            {{ $item->product->name }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            Código: {{ $item->product->code }} | 
                                                            EPC/Serial: {{ $item->productUnit->epc ?? $item->productUnit->serial_number ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                    
                                                    <!-- Billing Mode Badge -->
                                                    <div>
                                                        @if($item->billing_mode === 'rental')
                                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                                <i class="fas fa-sync mr-1"></i>RENTA
                                                            </span>
                                                        @else
                                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                                <i class="fas fa-handshake mr-1"></i>CONSIGNACIÓN
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <i class="fas fa-building mr-1"></i>{{ $item->sourceLegalEntity->business_name }}
                                                    @if($item->sourceSubWarehouse)
                                                        → {{ $item->sourceSubWarehouse->name }}
                                                    @endif
                                                </p>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Warning Box -->
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        Importante
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li><strong>RENTA:</strong> Se facturará independientemente de si regresa o no</li>
                                            <li><strong>CONSIGNACIÓN:</strong> Solo se facturará si NO regresa (quedó en el hospital)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
                        <a href="{{ route('quotations.show', $quotation) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Marca los productos que <strong>SÍ regresaron</strong>
                        </div>
                        
                        <button type="submit" 
                                onclick="return confirm('¿Confirmar retorno de cirugía? Esta acción cambiará el estado de la cotización.')"
                                class="inline-flex items-center px-6 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring focus:ring-green-300 transition">
                            <i class="fas fa-check mr-2"></i>
                            Confirmar Retorno
                        </button>
                    </div>
                </form>
            </div>

            <!-- Help Section -->
            <div class="bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">
                    <i class="fas fa-question-circle mr-2"></i>¿Qué sucede después?
                </h4>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                        <p>La cotización pasará a estado <strong>"Completada"</strong></p>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                        <p>Los productos marcados volverán a estado <strong>"Disponible"</strong> en inventario</p>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                        <p>Podrás <strong>generar las ventas automáticamente</strong> según la modalidad de cada producto</p>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                        <p>Se crearán movimientos de inventario para cada producto retornado</p>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>