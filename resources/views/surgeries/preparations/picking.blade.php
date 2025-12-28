{{-- resources/views/surgeries/preparations/picking.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-hand-holding-box mr-2 text-green-600"></i>
                    {{ __('Surtido de Productos') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $surgery->code }} - {{ $surgery->patient_name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('surgeries.preparations.verify', $surgery) }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-check-double mr-2"></i>
                    Verificar y Completar
                </a>
                <a href="{{ route('surgeries.preparations.compare', $surgery) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver a Comparación
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Progreso -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Progreso de Surtido</h3>
                        <p class="text-sm text-green-100">{{ $preparation->items->where('status', 'complete')->count() }} de {{ $preparation->items->count() }} productos completados</p>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold">{{ number_format($preparation->getCompletenessPercentage(), 0) }}%</p>
                    </div>
                </div>
                <div class="w-full bg-green-400 bg-opacity-30 rounded-full h-3">
                    <div class="bg-white h-3 rounded-full transition-all duration-500" 
                         style="width: {{ $preparation->getCompletenessPercentage() }}%"></div>
                </div>
            </div>

            <!-- Escaneo Rápido -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-barcode mr-2 text-indigo-600"></i>
                        Escaneo Rápido de Productos
                    </h3>
                </div>
                
                <div class="p-6">
                    <form id="scanForm" class="space-y-4">
                        @csrf
                        <div class="flex items-end space-x-4">
                            <div class="flex-1">
                                <label for="epc_scan" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-barcode mr-1"></i>
                                    Escanear EPC del Producto
                                </label>
                                <input type="text" 
                                       id="epc_scan" 
                                       name="epc"
                                       placeholder="Escanea o ingresa el EPC..."
                                       class="w-full font-mono text-lg rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                       autofocus>
                            </div>
                            <button type="submit" 
                                    class="px-6 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-plus mr-1"></i>
                                Agregar
                            </button>
                        </div>
                    </form>

                    <!-- Resultado del Escaneo -->
                    <div id="scanResult" class="mt-4 hidden">
                        <!-- Se llena dinámicamente con JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Productos Faltantes -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                        Productos Faltantes ({{ $preparation->items->where('quantity_missing', '>', 0)->count() }})
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Vista:</span>
                        <button onclick="toggleView('list')" 
                                class="px-3 py-1 text-sm rounded bg-gray-200 hover:bg-gray-300 transition-colors">
                            <i class="fas fa-list"></i>
                        </button>
                        <button onclick="toggleView('grid')" 
                                class="px-3 py-1 text-sm rounded bg-gray-200 hover:bg-gray-300 transition-colors">
                            <i class="fas fa-th-large"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Vista de Lista -->
                <div id="listView" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Faltante</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Surtido</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($preparation->items->where('quantity_missing', '>', 0) as $item)
                            <tr class="hover:bg-gray-50" id="item-row-{{ $item->id }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-red-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-red-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $item->product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->product->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                        {{ $item->quantity_missing }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800" id="picked-{{ $item->id }}">
                                        {{ $item->quantity_picked }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->storageLocation)
                                        <div class="flex items-center">
                                            <i class="fas fa-map-marker-alt text-indigo-600 mr-2"></i>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $item->storageLocation->code }}</div>
                                                <div class="text-xs text-gray-500">{{ $item->storageLocation->name }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">Sin ubicación</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($item->quantity_picked >= $item->quantity_missing)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Completo
                                        </span>
                                    @elseif($item->quantity_picked > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-spinner mr-1"></i>
                                            Parcial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Vista de Tarjetas (Grid) -->
                <div id="gridView" class="p-6 hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($preparation->items->where('quantity_missing', '>', 0) as $item)
                        <div class="bg-white border-2 {{ $item->quantity_picked >= $item->quantity_missing ? 'border-green-200' : 'border-red-200' }} rounded-lg p-4 hover:shadow-lg transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900">{{ $item->product->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $item->product->code }}</p>
                                </div>
                                @if($item->storageLocation)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ $item->storageLocation->code }}
                                    </span>
                                @endif
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Faltante:</span>
                                    <span class="font-semibold text-red-600">{{ $item->quantity_missing }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Surtido:</span>
                                    <span class="font-semibold text-green-600">{{ $item->quantity_picked }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    @php
                                        $percentage = $item->quantity_missing > 0 ? ($item->quantity_picked / $item->quantity_missing * 100) : 100;
                                    @endphp
                                    <div class="bg-green-500 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Productos Ya Completos -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-check-circle mr-2 text-green-600"></i>
                        Productos Completos ({{ $preparation->items->where('status', 'complete')->count() + $preparation->items->where('status', 'in_package')->count() }})
                    </h3>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($preparation->items->whereIn('status', ['complete', 'in_package']) as $item)
                        <div class="flex items-center p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $item->product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->quantity_required }} unidades</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Instrucciones -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900 mb-2">Instrucciones de Surtido</h4>
                        <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                            <li>Escanea los EPCs de los productos faltantes en el campo superior</li>
                            <li>El sistema validará automáticamente cada producto escaneado</li>
                            <li>Verifica las ubicaciones físicas para encontrar los productos más rápido</li>
                            <li>Una vez completados todos los productos, presiona "Verificar y Completar"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Función para alternar entre vista de lista y grid
        function toggleView(view) {
            const listView = document.getElementById('listView');
            const gridView = document.getElementById('gridView');
            
            if (view === 'list') {
                listView.classList.remove('hidden');
                gridView.classList.add('hidden');
            } else {
                listView.classList.add('hidden');
                gridView.classList.remove('hidden');
            }
        }

        // Manejo del formulario de escaneo
        document.getElementById('scanForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const epcInput = document.getElementById('epc_scan');
            const epc = epcInput.value.trim();
            
            if (!epc) return;
            
            try {
                const response = await fetch('{{ route("surgeries.preparations.add-picked-product", $surgery) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ epc: epc })
                });
                
                const data = await response.json();
                
                const resultDiv = document.getElementById('scanResult');
                resultDiv.classList.remove('hidden');
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                                <div>
                                    <p class="font-semibold text-green-900">${data.message}</p>
                                    <p class="text-sm text-green-700">${data.product_name}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Actualizar contador en la tabla
                    const pickedSpan = document.getElementById('picked-' + data.item_id);
                    if (pickedSpan) {
                        pickedSpan.textContent = data.quantity_picked;
                    }
                    
                    // Limpiar input
                    epcInput.value = '';
                    
                    // Recargar página si se completó todo
                    if (data.all_complete) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                } else {
                    resultDiv.innerHTML = `
                        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-times-circle text-red-600 text-2xl mr-3"></i>
                                <div>
                                    <p class="font-semibold text-red-900">Error</p>
                                    <p class="text-sm text-red-700">${data.message}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                // Ocultar mensaje después de 3 segundos
                setTimeout(() => {
                    resultDiv.classList.add('hidden');
                }, 3000);
                
            } catch (error) {
                console.error('Error:', error);
                const resultDiv = document.getElementById('scanResult');
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = `
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-700">Error al procesar el escaneo</p>
                    </div>
                `;
            }
        });

        // Auto-focus en el input de escaneo
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('epc_scan').focus();
        });
    </script>
    @endpush
</x-app-layout>