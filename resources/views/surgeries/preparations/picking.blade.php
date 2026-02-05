{{-- resources/views/surgeries/preparations/pick.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-hand-holding-box mr-2 text-indigo-600"></i>
                    Surtido de Productos
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $surgery->code }} - {{ $surgery->patient->name ?? $surgery->patient_name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @if($summary['mandatory_pending'] == 0)
                    <a href="{{ route('surgeries.preparations.verify', $surgery) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200">
                        <i class="fas fa-check-double mr-2"></i>
                        Verificar y Completar
                    </a>
                @else
                    <button disabled
                            class="inline-flex items-center px-4 py-2 bg-gray-400 text-white font-semibold rounded-lg shadow-md cursor-not-allowed opacity-60">
                        <i class="fas fa-check-double mr-2"></i>
                        Verificar ({{ $summary['mandatory_pending'] }} pendientes)
                    </button>
                @endif
                
                <a href="{{ route('surgeries.preparations.compare', $surgery) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>

                <button onclick="openCancelModal()" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
            </div>
        </div>
    </x-slot>

    {{-- 🆕 IMPORTANTE: data-surgery-id para JavaScript --}}
    <div class="py-6" data-surgery-id="{{ $surgery->id }}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Barra de Progreso Global --}}
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Progreso Total de Surtido</h3>
                        <p class="text-sm text-blue-100">
                            <span id="required-quantity">{{ $summary['total_quantity_required'] }}</span> Unidades Totales
                        </p>
                        <p class="text-xs text-blue-200 mt-1">
                            <i class="fas fa-box mr-1"></i>
                            En paquete: {{ $summary['total_quantity_in_package'] }} | 
                            <i class="fas fa-hand-holding mr-1"></i>
                            Surtidas: <span id="picked-quantity">{{ $summary['total_quantity_picked'] }}</span> |
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Faltantes: <span id="missing-quantity">{{ $summary['total_quantity_missing'] }}</span>
                        </p>
                        <p class="text-xs text-blue-200 mt-1">
                            Productos Diferentes Faltantes: <strong id="mandatory-pending">{{ $summary['mandatory_pending'] }}</strong>
                        </p>
                    </div>
                    <div class="text-right">
                        <p id="progress-percentage" class="text-4xl font-bold">{{ number_format($summary['completion_percentage'], 0) }}%</p>
                        <p class="text-xs text-blue-200 mt-1">Completitud</p>
                    </div>
                </div>
                
                <div class="w-full bg-white bg-opacity-20 rounded-full h-3">
                    <div id="progress-bar" 
                        class="bg-white h-3 rounded-full transition-all duration-700 ease-out" 
                        style="width: {{ $summary['completion_percentage'] }}%">
                    </div>
                </div>
                
                @if($preparation->preAssembledPackage)
                    <div class="mt-4 pt-4 border-t border-white border-opacity-10 flex items-center justify-between text-sm text-blue-100">
                        <div class="flex items-center">
                            <i class="fas fa-box-open mr-2"></i>
                            Paquete actual: <strong class="ml-1">{{ $preparation->preAssembledPackage->code }}</strong>
                        </div>
                        @if($preparation->preAssembledPackage->storageLocation)
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <span>{{ $preparation->preAssembledPackage->storageLocation->code }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Toggle de Modo (Manual / RFID) --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-center space-x-4">
                    <button id="manualModeBtn" 
                            onclick="switchMode('manual')"
                            class="mode-btn active flex items-center px-6 py-3 rounded-lg font-semibold transition-all duration-200">
                        <i class="fas fa-barcode mr-2"></i>
                        Modo Manual (Barcode)
                    </button>
                    <button id="rfidModeBtn" 
                            onclick="switchMode('rfid')"
                            class="mode-btn flex items-center px-6 py-3 rounded-lg font-semibold transition-all duration-200">
                        <i class="fas fa-broadcast-tower mr-2"></i>
                        Modo RFID
                    </button>
                </div>
            </div>


            {{-- 🆕 PANEL DE ESTADÍSTICAS RFID EN VIVO --}}
            <div id="rfid-stats-panel" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-green-600" id="rfid-correct-count">0</p>
                        <p class="text-xs text-gray-500 uppercase">Tags Correctos</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-red-600" id="rfid-incorrect-count">0</p>
                        <p class="text-xs text-gray-500 uppercase">Tags Incorrectos</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-blue-600" id="rfid-total-scanned">0</p>
                        <p class="text-xs text-gray-500 uppercase">Total Escaneados</p>
                    </div>
                </div>
            </div>

            {{-- 🆕 PANEL DE TAGS INCORRECTOS DETECTADOS --}}
            <div id="incorrect-tags-panel" class="hidden bg-red-50 rounded-lg shadow-sm border border-red-200">
                <div class="px-6 py-4 bg-red-100 border-b border-red-200 flex justify-between items-center">
                    <h3 class="text-red-800 font-bold flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        TAGS INCORRECTOS DETECTADOS (<span id="incorrect-tags-count">0</span>)
                    </h3>
                    <button onclick="clearIncorrectTags()" 
                            class="text-sm text-red-600 hover:text-red-800 font-medium">
                        <i class="fas fa-trash mr-1"></i>
                        Limpiar
                    </button>
                </div>
                <div id="incorrect-tags-list" class="p-4 space-y-2 max-h-60 overflow-y-auto">
                    {{-- Se llena dinámicamente --}}
                </div>
            </div>

            {{-- 🆕 CONTENEDOR DE TOAST NOTIFICATIONS --}}
            <div id="toast-container" class="fixed top-20 right-4 z-50 space-y-2" style="max-width: 400px;">
                {{-- Toasts se agregan aquí dinámicamente --}}
            </div>

            {{-- MODO MANUAL: Escaneo de Barcode --}}
            <div id="manualModeSection" class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <form id="barcodeForm" class="flex items-end space-x-4">
                        @csrf
                        <div class="flex-1">
                            <label for="barcode_scan" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-barcode mr-1"></i>
                                Escanear Código de Barras
                            </label>
                            <input type="text" 
                                   id="barcode_scan" 
                                   name="barcode" 
                                   class="w-full font-mono text-lg rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" 
                                   placeholder="Escanea el código de barras del producto..." 
                                   autofocus>
                            <p class="text-xs text-gray-500 mt-1">
                                💡 El sistema seleccionará automáticamente la unidad más próxima a caducar (FEFO) o más antigua (FIFO)
                            </p>
                        </div>
                        <button type="submit" 
                                id="barcodeButton"
                                class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-plus mr-2"></i>
                            AGREGAR
                        </button>
                    </form>
                    
                    {{-- Área de Resultados Manual --}}
                    <div id="barcodeResult" class="mt-4 hidden transition-all duration-300"></div>
                </div>
            </div>

            {{-- MODO RFID: Control del Lector RFD90 --}}
            {{-- MODO RFID: Control del Lector RFD90 --}}
            <div id="rfidModeSection" class="bg-white rounded-lg shadow-sm border border-gray-200 hidden">
                <div class="p-6">
                    {{-- Estado y Controles del Lector --}}
                    <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-broadcast-tower text-purple-600 text-2xl"></i>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Lector RFID RFD90</h4>
                                    <p id="rfid-status" class="text-sm text-gray-600">Estado: Desconectado</p>
                                </div>
                            </div>
                            <div id="rfid-tags-count" class="text-right">
                                <p class="text-2xl font-bold text-purple-600">0</p>
                                <p class="text-xs text-gray-500">Tags detectados</p>
                            </div>
                        </div>
                        
                        {{-- Botones de Control --}}
                        <div class="flex flex-wrap gap-2">
                            <button type="button" 
                                    id="rfid-connect-btn"
                                    onclick="connectRFIDReader()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-plug mr-1"></i>
                                Conectar Lector
                            </button>
                            <button type="button" 
                                    id="rfid-disconnect-btn"
                                    onclick="disconnectRFIDReader()"
                                    disabled
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-plug-circle-xmark mr-1"></i>
                                Desconectar
                            </button>
                            <button type="button" 
                                    id="rfid-start-btn"
                                    onclick="startRFIDReading()"
                                    disabled
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-play mr-1"></i>
                                Iniciar Verificación
                            </button>
                            <button type="button" 
                                    id="rfid-stop-btn"
                                    onclick="stopRFIDReading()"
                                    disabled
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-stop mr-1"></i>
                                Detener Verificación
                            </button>
                        </div>

                        {{-- 🆕 Feedback Visual --}}
                        <div id="rfid-feedback" class="mt-3 text-sm text-gray-600 font-semibold"></div>
                    </div>

                    {{-- Consola de Eventos RFID --}}
                    <div class="mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <h5 class="text-sm font-medium text-gray-700">
                                <i class="fas fa-terminal mr-1"></i>
                                Registro de Escaneos
                            </h5>
                            <button type="button" 
                                    onclick="clearRFIDConsole()"
                                    class="text-xs text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eraser mr-1"></i>
                                Limpiar
                            </button>
                        </div>
                        <div id="rfid-console" 
                            class="h-40 overflow-y-auto p-3 bg-gray-900 text-green-400 font-mono text-xs rounded-lg border border-gray-700">
                            {{-- Mensajes se añaden dinámicamente --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Loading Indicator --}}
            <div id="loadingIndicator" class="hidden">
                <div class="flex items-center justify-center p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600 mr-3"></div>
                    <span class="text-indigo-700 font-medium">Procesando...</span>
                </div>
            </div>

            {{-- Tabla de Productos Pendientes --}}
            <div class="bg-white rounded-lg shadow-sm border border-red-100 overflow-hidden">
                <div class="px-6 py-4 bg-red-50 border-b border-red-100 flex justify-between items-center">
                    <h3 class="text-red-800 font-bold flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        PRODUCTOS POR SURTIR (<span id="pending-count">{{ $pendingItems->count() }} Productos con {{ $summary['total_quantity_missing']}} piezas</span>)
                    </h3>
                    
                    @if($pendingItems->count() > 0)
                        <button onclick="refreshPage()" 
                                class="text-sm text-red-600 hover:text-red-800 font-medium">
                            <i class="fas fa-sync-alt mr-1"></i>
                            Actualizar
                        </button>
                    @endif
                </div>
                
                <div id="pendingItemsTable" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Faltan</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Surtidos</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Requeridos</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ubicación</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Obligatorio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($pendingItems as $item)
                                <tr class="hover:bg-red-50 transition-colors" id="item-row-{{ $item->id }}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            @if($item->is_mandatory)
                                                <i class="fas fa-star text-yellow-500 mr-2" title="Obligatorio"></i>
                                            @endif
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $item->product->name }}</div>
                                                <div class="text-xs text-gray-500 font-mono">{{ $item->product->code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span id="missing-{{ $item->id }}" 
                                              class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-full font-bold text-sm">
                                            {{ $item->quantity_missing }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span id="picked-{{ $item->id }}" 
                                              class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-bold text-sm">
                                            {{ $item->quantity_picked }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-gray-600 font-medium">{{ $item->quantity_required }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($item->storageLocation)
                                            <span class="text-sm text-indigo-600 font-semibold">
                                                <i class="fas fa-map-marker-alt mr-1"></i> 
                                                {{ $item->storageLocation->code }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs italic">No definida</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($item->is_mandatory)
                                            <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold">
                                                SÍ
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs">No</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="empty-state">
                                    <td colspan="6" class="px-6 py-10 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
                                            <p class="text-gray-700 font-semibold text-lg">¡Excelente trabajo!</p>
                                            <p class="text-gray-500 text-sm mt-1">No hay productos pendientes por surtir</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Productos Completados (Colapsable) --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <button onclick="toggleCompleted()" 
                        class="w-full px-6 py-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Productos Completados (<span id="completed-products-count">{{ $summary['completed_items'] + $summary['in_package_items'] }}</span>)
                    </h3>
                    <i id="toggle-icon" class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                </button>
                
                <div id="completed-section" class="hidden px-6 pb-6 pt-2 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($preparation->items->whereIn('status', ['complete', 'in_package']) as $item)
                            <div class="bg-white p-3 rounded-lg border border-green-200 flex items-center shadow-sm hover:shadow-md transition-shadow">
                                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-800">{{ $item->product->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $item->quantity_required }} unidad(es)
                                        @if($item->status == 'in_package')
                                            <span class="text-blue-600">• Ya en paquete</span>
                                        @else
                                            <span class="text-green-600">• Surtido</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

    

    {{-- Modal de Cancelación --}}
    <div id="cancelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    Cancelar Preparación
                </h3>
                <form id="cancelForm">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Motivo de cancelación</label>
                        <textarea id="cancel_reason" 
                                  name="reason" 
                                  rows="3" 
                                  required
                                  class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Explica por qué se cancela esta preparación..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="closeCancelModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cerrar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Confirmar Cancelación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Estilos para Toggle de Modo */
        .mode-btn {
            background-color: #f3f4f6;
            color: #6b7280;
            border: 2px solid transparent;
        }
        
        .mode-btn.active {
            background-color: #4f46e5;
            color: white;
            border-color: #4338ca;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
        }
        
        .mode-btn:hover:not(.active) {
            background-color: #e5e7eb;
        }
    </style>
    @endpush

    @push('scripts')
        {{-- 🆕 Cargar el módulo RFID compilado por Vite --}}
        @vite('resources/js/pages/surgeries/picking-rfid.js')
        
        {{-- ⚠️ IMPORTANTE: Estas funciones pequeñas se quedan aquí (no vale la pena modularizarlas) --}}
        <script>
            // Modal de cancelación
            function openCancelModal() {
                document.getElementById('cancelModal').classList.remove('hidden');
            }
            
            function closeCancelModal() {
                document.getElementById('cancelModal').classList.add('hidden');
                document.getElementById('cancel_reason').value = '';
            }
            
            // Enviar cancelación
            document.getElementById('cancelForm')?.addEventListener('submit', async function(e) {
                e.preventDefault();
                const reason = document.getElementById('cancel_reason').value.trim();
                if (!reason) return;
                
                if (!confirm('¿Estás seguro de que deseas cancelar esta preparación?')) {
                    return;
                }
                
                try {
                    const response = await fetch('{{ route("surgeries.preparations.cancel", $surgery) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ reason: reason })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Preparación cancelada correctamente');
                        window.location.href = '{{ route("surgeries.show", $surgery) }}';
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al cancelar la preparación');
                }
            });
            
            // Toggle productos completados
            function toggleCompleted() {
                const section = document.getElementById('completed-section');
                const icon = document.getElementById('toggle-icon');
                section.classList.toggle('hidden');
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            }
            
            // Refrescar página
            function refreshPage() {
                window.location.reload();
            }
        </script>
    @endpush
</x-app-layout>