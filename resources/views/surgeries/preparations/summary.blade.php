{{-- resources/views/surgeries/preparations/summary.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-check-double mr-2 text-green-600"></i>
                    {{ __('Preparación Completada') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $surgery->code }} - {{ $surgery->patient_name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @if(!$surgery->invoice)
                    <a href="{{ route('invoices.create-from-surgery', $surgery) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Generar Remisión
                    </a>
                @endif
                <a href="{{ route('surgeries.show', $surgery) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-eye mr-2"></i>
                    Ver Cirugía
                </a>
                <a href="{{ route('surgeries.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-list mr-2"></i>
                    Ver Todas
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Mensaje de Éxito -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-8 text-white text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-4">
                    <i class="fas fa-check text-5xl"></i>
                </div>
                <h2 class="text-3xl font-bold mb-2">¡Preparación Completada!</h2>
                <p class="text-lg text-green-100">La cirugía está lista para realizarse</p>
            </div>

            <!-- Resumen de Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Items -->
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-full mb-3">
                        <i class="fas fa-list text-2xl text-indigo-600"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-900">{{ $preparation->items->count() }}</div>
                    <div class="text-sm text-gray-600 mt-1">Productos Totales</div>
                </div>

                <!-- Desde Paquete -->
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-3">
                        <i class="fas fa-box-open text-2xl text-blue-600"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-900">{{ $preparation->items->where('status', 'in_package')->count() }}</div>
                    <div class="text-sm text-gray-600 mt-1">Desde Paquete</div>
                </div>

                <!-- Surtidos -->
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full mb-3">
                        <i class="fas fa-hand-holding-box text-2xl text-purple-600"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-900">{{ $preparation->items->sum('quantity_picked') }}</div>
                    <div class="text-sm text-gray-600 mt-1">Productos Surtidos</div>
                </div>

                <!-- Tiempo -->
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3">
                        <i class="fas fa-clock text-2xl text-green-600"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-900">
                        {{ $preparation->started_at->diffInMinutes($preparation->completed_at) }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Minutos</div>
                </div>
            </div>

            <!-- Detalles de la Preparación -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle mr-2 text-indigo-600"></i>
                        Detalles de la Preparación
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Iniciada Por</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $preparation->preparer->name ?? 'Sistema' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Verificada Por</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $preparation->verifier->name ?? 'Sistema' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Inicio</dt>
                            <dd class="text-sm text-gray-900">{{ $preparation->started_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Finalización</dt>
                            <dd class="text-sm text-gray-900">{{ $preparation->completed_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if($preparation->preAssembledPackage)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 mb-1">Paquete Pre-Armado Utilizado</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="{{ route('pre-assembled.show', $preparation->preAssembledPackage) }}" 
                                   class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $preparation->preAssembledPackage->name }} ({{ $preparation->preAssembledPackage->code }})
                                </a>
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Lista Completa de Productos -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-box mr-2 text-indigo-600"></i>
                        Productos Preparados
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Origen</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">EPCs</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($preparation->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-check text-green-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $item->product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->product->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-800">
                                        {{ $item->quantity_required }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($item->quantity_in_package > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-box-open mr-1"></i>
                                            Paquete ({{ $item->quantity_in_package }})
                                        </span>
                                    @endif
                                    @if($item->quantity_picked > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 ml-1">
                                            <i class="fas fa-hand-holding mr-1"></i>
                                            Surtido ({{ $item->quantity_picked }})
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick="showEPCs({{ $item->id }})" 
                                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        <i class="fas fa-barcode mr-1"></i>
                                        Ver EPCs ({{ $item->units->count() }})
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Próximos Pasos -->
            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-lightbulb text-yellow-600 text-3xl mr-4"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-yellow-900 mb-3">Próximos Pasos</h4>
                        <div class="space-y-2">
                            @if(!$surgery->invoice)
                                <div class="flex items-center text-sm text-yellow-800">
                                    <i class="fas fa-circle text-xs mr-2"></i>
                                    Generar la remisión para la cirugía
                                </div>
                            @else
                                <div class="flex items-center text-sm text-green-700">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Remisión generada: {{ $surgery->invoice->invoice_number }}
                                </div>
                            @endif
                            <div class="flex items-center text-sm text-yellow-800">
                                <i class="fas fa-circle text-xs mr-2"></i>
                                Trasladar los productos al área quirúrgica
                            </div>
                            <div class="flex items-center text-sm text-yellow-800">
                                <i class="fas fa-circle text-xs mr-2"></i>
                                Verificar que todo esté listo antes de la cirugía
                            </div>
                        </div>
                        
                        @if(!$surgery->invoice)
                        <div class="mt-4">
                            <a href="{{ route('invoices.create-from-surgery', $surgery) }}" 
                               class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors">
                                <i class="fas fa-file-invoice mr-2"></i>
                                Generar Remisión Ahora
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showEPCs(itemId) {
            alert('Modal para mostrar EPCs del item: ' + itemId + '\n\nEsta funcionalidad mostrará todos los EPCs escaneados para este producto.');
            // TODO: Implementar modal con lista de EPCs
        }
    </script>
    @endpush
</x-app-layout>