{{-- resources/views/surgeries/preparations/picking.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-hand-holding-box mr-2 text-indigo-600"></i>
                    Surtido de Productos
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $surgery->code }} - {{ $surgery->patient_name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('surgeries.preparations.verify', $surgery) }}" 
                   class="inline-flex items-center px-4 py-2 {{ $preparation->getCompletenessPercentage() >= 100 ? 'bg-green-600 hover:bg-green-700' : 'bg-purple-600 hover:bg-purple-700' }} text-white font-semibold rounded-lg shadow-md transition-all duration-200">
                    <i class="fas fa-check-double mr-2"></i>
                    Verificar y Completar
                </a>
                <a href="{{ route('surgeries.preparations.compare', $surgery) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Progreso Total de Surtido</h3>
                        <p class="text-sm text-blue-100">
                            {{ $preparation->items->whereIn('status', ['complete', 'in_package'])->count() }} 
                            de {{ $preparation->items->count() }} productos en caja
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold">{{ number_format($preparation->getCompletenessPercentage(), 0) }}%</p>
                    </div>
                </div>
                <div class="w-full bg-white bg-opacity-20 rounded-full h-3">
                    <div class="bg-white h-3 rounded-full transition-all duration-700 ease-out" 
                         style="width: {{ $preparation->getCompletenessPercentage() }}%">
                    </div>
                </div>
                @if($preparation->preAssembledPackage)
                    <div class="mt-4 pt-4 border-t border-white border-opacity-10 flex items-center text-xs text-blue-100 italic">
                        <i class="fas fa-box-open mr-2"></i>
                        Paquete actual: <strong>{{ $preparation->preAssembledPackage->code }}</strong>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <form id="scanForm" class="flex items-end space-x-4">
                        @csrf
                        <div class="flex-1">
                            <label for="epc_scan" class="block text-sm font-medium text-gray-700 mb-2">Escanear EPC del Producto Faltante</label>
                            <input type="text" id="epc_scan" name="epc" 
                                   class="w-full font-mono text-lg rounded-lg border-gray-300 focus:ring-indigo-500" 
                                   placeholder="Acerque el lector al tag..." autofocus>
                        </div>
                        <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-indigo-700">
                            AGREGAR
                        </button>
                    </form>
                    <div id="scanResult" class="mt-4 hidden transition-all duration-300"></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-red-100 overflow-hidden">
                <div class="px-6 py-4 bg-red-50 border-b border-red-100 flex justify-between items-center">
                    <h3 class="text-red-800 font-bold flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        PRODUCTOS POR SURTIR ({{ $pendingItems->count() }})
                    </h3>
                </div>
                
                <div id="listView" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Faltan</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Surtidos</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Ubicación</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($pendingItems as $item)
                                <tr class="hover:bg-red-50 transition-colors" id="item-row-{{ $item->id }}">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $item->product->code }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-md font-bold">
                                            {{ $item->quantity_missing }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span id="picked-{{ $item->id }}" class="px-2 py-1 bg-gray-100 text-gray-700 rounded-md font-bold">
                                            {{ $item->quantity_picked }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($item->storageLocation)
                                            <span class="text-sm text-indigo-600 font-semibold">
                                                <i class="fas fa-map-marker-alt mr-1"></i> {{ $item->storageLocation->code }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs italic">No definida</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">
                                        ¡Excelente! No hay productos pendientes por surtir.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-3 flex justify-between items-center cursor-pointer">
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">
                        Productos ya en caja ({{ $preparation->items->count() - $pendingItems->count() }})
                    </h3>
                </div>
                <div class="px-6 pb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($preparation->items->whereIn('status', ['complete', 'in_package']) as $item)
                        <div class="bg-white p-3 rounded border border-green-200 flex items-center shadow-sm">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <div>
                                <p class="text-xs font-bold text-gray-800">{{ $item->product->name }}</p>
                                <p class="text-[10px] text-gray-500">Completado</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <div class="mt-10 p-4 bg-black text-green-400 font-mono text-xs rounded-lg shadow-2xl opacity-80 hover:opacity-100 transition-opacity">
        <h4 class="border-b border-green-900 mb-2 pb-1 text-white uppercase font-bold">🛠 Debug de Preparación</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p>> ID Preparación: {{ $preparation->id }}</p>
                <p>> Estado: <span class="text-yellow-400">{{ $preparation->status }}</span></p>
                <p>> Paquete ID: {{ $preparation->pre_assembled_package_id ?? 'N/A' }}</p>
                <p>> % Completo: {{ $preparation->getCompletenessPercentage() }}%</p>
            </div>
            <div>
                <p>> Total Items: {{ $preparation->items->count() }}</p>
                <p>> Items Pendientes: {{ $pendingItems->count() }}</p>
                <p>> Items en Paquete: {{ $preparation->items->where('status', 'in_package')->count() }}</p>
                <p>> Items Surtidos: {{ $preparation->items->where('status', 'complete')->count() }}</p>
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

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('epc_scan').focus();
        });
    </script>
    @endpush
</x-app-layout>

    