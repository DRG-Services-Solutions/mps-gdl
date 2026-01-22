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

    <div class="py-6">
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

            {{-- 🆕 TOGGLE DE MODO (Manual / RFID) --}}
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

            {{-- 🆕 MODO MANUAL: Escaneo de Barcode --}}
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

            {{-- 🆕 MODO RFID: Control del Lector RFD90 --}}
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
                                Iniciar Lectura
                            </button>
                            <button type="button" 
                                    id="rfid-stop-btn"
                                    onclick="stopRFIDReading()"
                                    disabled
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-stop mr-1"></i>
                                Detener Lectura
                            </button>
                        </div>

                        {{-- Feedback --}}
                        <div id="rfid-feedback" class="mt-3 text-sm text-gray-600"></div>
                    </div>

                    {{-- Consola de Eventos RFID --}}
                    <div class="mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <h5 class="text-sm font-medium text-gray-700">
                                <i class="fas fa-terminal mr-1"></i>
                                Consola de Eventos RFID
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
                    
                    {{-- Área de Resultados RFID --}}
                    <div id="rfidResult" class="mt-4 hidden transition-all duration-300"></div>
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

    {{-- 🆕 MODAL DE CONFIRMACIÓN RFID --}}
    <div id="rfidConfirmModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-broadcast-tower text-purple-500 mr-2"></i>
                        Tag RFID Detectado
                    </h3>
                    <button onclick="closeRfidModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div id="rfidModalContent" class="space-y-3">
                    {{-- Contenido dinámico --}}
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" 
                            onclick="closeRfidModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 font-medium">
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </button>
                    <button type="button" 
                            id="confirmRfidBtn"
                            onclick="confirmRfidUnit()"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                        <i class="fas fa-check mr-1"></i>
                        Confirmar y Agregar
                    </button>
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
    <script>
        // ==============================================
        // ESTADO GLOBAL DEL PICKING
        // ==============================================
        const pickingState = {
            currentMode: 'manual', // 'manual' | 'rfid'
            pendingRfidEPC: null,  // EPC esperando confirmación
        };

        // ==============================================
        // ESTADO GLOBAL DEL LECTOR RFID
        // ==============================================
        const rfidState = {
            // Conexión
            readerID: null,
            isConnected: false,
            isReading: false,
            
            // Control de tags
            scannedTags: new Set(),
            lastScannedTags: new Map(), // Para cooldown por EPC
            cooldownTime: 2000, // 2 segundos
            
            // Transportes
            transports: ['bluetooth', 'serial'],
            currentTransportIndex: 0,
            
            // Referencias DOM
            connectBtn: null,
            disconnectBtn: null,
            startBtn: null,
            stopBtn: null,
            statusDiv: null,
            feedbackDiv: null,
            consoleDiv: null,
            tagsCountDiv: null,
        };

        // ==============================================
        // CALLBACKS GLOBALES PARA ENTERPRISE BROWSER
        // ==============================================
        
        window.handleRfidEnumGlobal = function(rfidArray) {
            appendToRfidConsole(`📡 Lectores encontrados: ${rfidArray ? rfidArray.length : 0}`, "info");
            
            if (!rfidArray || rfidArray.length === 0) {
                // Corrección: Evita bucles infinitos si falla la enumeración
                if (rfidState.currentTransportIndex < rfidState.transports.length - 1) {
                    rfidState.currentTransportIndex++;
                    tryNextTransport();
                } else {
                    appendToRfidConsole(`⚠️ No se encontraron lectores en ningún transporte.`, "error");
                    updateRFIDStatus(false);
                    if(rfidState.connectBtn) rfidState.connectBtn.disabled = false;
                }
                return;
            }
            
            rfidState.readerID = rfidArray[0][0];
            appendToRfidConsole(`🔌 Lector encontrado: ${rfidState.readerID}. Configurando hardware...`, "success");
            
            try {
                rfid.readerID = rfidState.readerID;
                
                // ============================================================
                // 🔧 CONFIGURACIÓN CRÍTICA PARA RFD90 + ENTERPRISE BROWSER
                // ============================================================
                
                // 1. Vincular Gatillo Físico (Pistola)
                // Esto hace que al apretar el gatillo inicie la lectura y al soltar pare.
                rfid.startTriggerType = "triggerPress"; 
                rfid.stopTriggerType = "triggerRelease";
                
                // 2. Feedback Auditivo (Beep)
                rfid.beepOnRead = "true"; 
                
                // 3. Definición de Eventos
                rfid.tagEvent = "handleTagDataGlobal(%json)";
                rfid.statusEvent = "handleStatusUpdateGlobal(%json)";
                
                rfid.connect();
            } catch(e) {
                appendToRfidConsole(`❌ Error al configurar: ${e.message}`, "error");
                updateRFIDStatus(false);
                if(rfidState.connectBtn) rfidState.connectBtn.disabled = false;
            }
        };

        window.handleStatusUpdateGlobal = function(eventInfo) {
            const statusMsg = eventInfo?.status?.toLowerCase() || eventInfo?.vendorMessage?.toLowerCase() || "";
            
            if (statusMsg.includes("connect")) {
                updateRFIDStatus(true, `Lector ${rfidState.readerID} conectado.`);
            } else if (statusMsg.includes("disconnect")) {
                updateRFIDStatus(false, `Lector ${rfidState.readerID} desconectado.`);
            } else if (statusMsg.includes("error")) {
                appendToRfidConsole(`❌ Error de estado: ${statusMsg}`, "error");
            }
        };

        window.handleTagDataGlobal = function(tagArray) {
            if (rfidState.isReading && tagArray && Array.isArray(tagArray.TagData)) {
                tagArray.TagData.forEach(tag => {
                    const detectedEpc = tag.tagID;
                    
                    if (detectedEpc && !isInCooldown(detectedEpc)) {
                        rfidState.scannedTags.add(detectedEpc);
                        addToCooldown(detectedEpc);
                        
                        appendToRfidConsole(`✓ Tag detectado: ${detectedEpc}`, "success");
                        
                        // Actualizar contador
                        if(rfidState.tagsCountDiv) {
                            rfidState.tagsCountDiv.querySelector('p.text-2xl').textContent = rfidState.scannedTags.size;
                        }
                        
                        // 🆕 Buscar automáticamente el tag
                        handleRFIDTag(detectedEpc);
                    }
                });
            }
        };

        // ==============================================
        // FUNCIONES DE CONTROL RFID
        // ==============================================
        
        function connectRFIDReader() {
            appendToRfidConsole('🔄 Iniciando conexión con lector RFID...', 'info');
            updateRFIDStatus(false);
            rfidState.connectBtn.disabled = true;
            rfidState.currentTransportIndex = 0;
            tryNextTransport();
        }

        function disconnectRFIDReader() {
            if (rfidState.isConnected) {
                try {
                    if (rfidState.isReading) {
                        rfid.stop();
                        rfidState.isReading = false;
                    }
                    rfid.disconnect();
                    appendToRfidConsole('🔌 Desconexión solicitada.', 'info');
                    updateRFIDStatus(false);
                } catch (e) {
                    appendToRfidConsole('❌ Error al desconectar: ' + e.message, "error");
                }
            }
        }

        function startRFIDReading() {
            rfidState.scannedTags.clear();
            rfidState.isReading = true;
            rfidState.startBtn.disabled = true;
            rfidState.stopBtn.disabled = false;
            
            appendToRfidConsole('📡 Iniciando lectura continua de tags...', 'success');
            rfidState.feedbackDiv.textContent = 'Leyendo tags... Acerque los productos al lector.';
            
            try {
                rfid.performInventory();
            } catch(e) {
                appendToRfidConsole('❌ Error al iniciar lectura: ' + e.message, "error");
                rfidState.isReading = false;
                rfidState.startBtn.disabled = false;
                rfidState.stopBtn.disabled = true;
            }
        }

        function stopRFIDReading() {
            try {
                rfid.stop();
                rfidState.isReading = false;
                rfidState.startBtn.disabled = false;
                rfidState.stopBtn.disabled = true;
                
                appendToRfidConsole('⏸️ Lectura detenida.', 'warning');
                rfidState.feedbackDiv.textContent = `Lectura detenida. ${rfidState.scannedTags.size} tags únicos detectados.`;
            } catch(e) {
                appendToRfidConsole('❌ Error al detener: ' + e.message, "error");
            }
        }

        function tryNextTransport() {
            if (rfidState.currentTransportIndex >= rfidState.transports.length) {
                appendToRfidConsole("❌ No se detectaron lectores RFID en ningún transporte.", "error");
                updateRFIDStatus(false);
                rfidState.connectBtn.disabled = false;
                return;
            }
            
            const transport = rfidState.transports[rfidState.currentTransportIndex];
            appendToRfidConsole(`🔍 Buscando lectores por ${transport.toUpperCase()}...`, 'info');
            
            try {
                rfid.transport = transport;
                rfid.enumRFIDEvent = "handleRfidEnumGlobal(%s)";
                rfid.enumerate();
            } catch(e) {
                appendToRfidConsole(`❌ Error al enumerar: ${e.message}`, "error");
                rfidState.currentTransportIndex++;
                tryNextTransport();
            }
        }

        function updateRFIDStatus(isConnected, message = "") {
            rfidState.isConnected = isConnected;
            
            rfidState.statusDiv.innerHTML = isConnected
                ? `<span class="text-green-600 font-semibold">Estado: ✓ Conectado (${rfidState.readerID})</span>`
                : `<span class="text-gray-600">Estado: Desconectado</span>`;
            
            rfidState.connectBtn.disabled = isConnected;
            rfidState.disconnectBtn.disabled = !isConnected;
            rfidState.startBtn.disabled = !isConnected || rfidState.isReading;
            rfidState.stopBtn.disabled = !isConnected || !rfidState.isReading;
            
            if (!isConnected && rfidState.isReading) {
                rfidState.isReading = false;
            }
            
            if(message) appendToRfidConsole(message, isConnected ? "success" : "info");
        }

        function appendToRfidConsole(message, type = "info") {
            if (!rfidState.consoleDiv) return;
            
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const logEntry = document.createElement('div');
            logEntry.textContent = `[${timeString}] ${message}`;
            
            const colors = {
                "error": "#ff7b7b",
                "success": "#7bff7b",
                "warning": "#ffff7b",
                "info": "#7bc0ff"
            };
            
            logEntry.style.color = colors[type] || "#f0f0f0";
            
            rfidState.consoleDiv.appendChild(logEntry);
            rfidState.consoleDiv.scrollTop = rfidState.consoleDiv.scrollHeight;
        }

        function clearRFIDConsole() {
            if (rfidState.consoleDiv) {
                rfidState.consoleDiv.innerHTML = '';
                appendToRfidConsole('Consola limpiada.', 'info');
            }
        }

        // ==============================================
        // CONTROL DE COOLDOWN RFID
        // ==============================================
        
        function isInCooldown(epc) {
            if (!rfidState.lastScannedTags.has(epc)) return false;
            
            const lastTime = rfidState.lastScannedTags.get(epc);
            const now = Date.now();
            
            return (now - lastTime) < rfidState.cooldownTime;
        }

        function addToCooldown(epc) {
            rfidState.lastScannedTags.set(epc, Date.now());
            
            setTimeout(() => {
                rfidState.lastScannedTags.delete(epc);
            }, rfidState.cooldownTime);
        }

        // ==============================================
        // MANEJO DE TAG RFID DETECTADO
        // ==============================================
        
        async function handleRFIDTag(epc) {
            appendToRfidConsole(`🔍 Buscando información del tag: ${epc}...`, 'info');
            
            try {
                const response = await fetch('{{ route("surgeries.preparations.searchEPC", $surgery) }}?epc=' + encodeURIComponent(epc), {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    appendToRfidConsole(`✓ Unidad encontrada: ${data.data.product_name}`, 'success');
                    showRfidConfirmModal(data.data);
                } else {
                    appendToRfidConsole(`⚠️ ${data.message}`, 'warning');
                    showErrorMessage(data.message, 'rfidResult');
                }
                
            } catch (error) {
                appendToRfidConsole(`❌ Error al buscar tag: ${error}`, 'error');
            }
        }

        // ==============================================
        // TOGGLE DE MODO
        // ==============================================
        
        function switchMode(mode) {
            pickingState.currentMode = mode;
            
            const manualBtn = document.getElementById('manualModeBtn');
            const rfidBtn = document.getElementById('rfidModeBtn');
            const manualSection = document.getElementById('manualModeSection');
            const rfidSection = document.getElementById('rfidModeSection');
            
            if (mode === 'manual') {
                manualBtn.classList.add('active');
                rfidBtn.classList.remove('active');
                manualSection.classList.remove('hidden');
                rfidSection.classList.add('hidden');
                
                // Detener lectura RFID si está activa
                if (rfidState.isReading) {
                    stopRFIDReading();
                }
                
                document.getElementById('barcode_scan').focus();
                console.log('📦 Modo Manual activado');
            } else {
                rfidBtn.classList.add('active');
                manualBtn.classList.remove('active');
                rfidSection.classList.remove('hidden');
                manualSection.classList.add('hidden');
                
                console.log('📡 Modo RFID activado');
            }
        }

        // ==============================================
        // MODO MANUAL: ESCANEO DE BARCODE
        // ==============================================
        
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar referencias DOM RFID
            rfidState.connectBtn = document.getElementById('rfid-connect-btn');
            rfidState.disconnectBtn = document.getElementById('rfid-disconnect-btn');
            rfidState.startBtn = document.getElementById('rfid-start-btn');
            rfidState.stopBtn = document.getElementById('rfid-stop-btn');
            rfidState.statusDiv = document.getElementById('rfid-status');
            rfidState.feedbackDiv = document.getElementById('rfid-feedback');
            rfidState.consoleDiv = document.getElementById('rfid-console');
            rfidState.tagsCountDiv = document.getElementById('rfid-tags-count');
            
            appendToRfidConsole('Sistema RFID iniciado. Presione "Conectar Lector" para comenzar.', 'info');
        });

        document.getElementById('barcodeForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const barcodeInput = document.getElementById('barcode_scan');
            const barcodeButton = document.getElementById('barcodeButton');
            const barcode = barcodeInput.value.trim();
            
            if (!barcode) return;
            
            barcodeInput.disabled = true;
            barcodeButton.disabled = true;
            showLoading();
            
            try {
                const response = await fetch('{{ route("surgeries.preparations.scanBarcode", $surgery) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ barcode: barcode })
                });
                
                const data = await response.json();
                hideLoading();
                
                if (data.success) {
                    showSuccessMessage(data.message, 'barcodeResult');
                    
                    if (data.data.unit_info) {
                        showUnitInfo(data.data.unit_info, 'barcodeResult');
                    }
                    
                    updateItemInTable(data.data);
                    await updateProgress();
                    barcodeInput.value = '';
                    
                    if (data.data.quantity_missing <= 0) {
                        setTimeout(() => removeItemFromTable(data.data.item_id), 1000);
                    }
                    
                    if (data.data.preparation_complete) {
                        showCompletionAlert();
                    }
                } else {
                    showErrorMessage(data.message, 'barcodeResult');
                    
                    if (data.other_units) {
                        showOtherUnitsInfo(data.other_units, 'barcodeResult');
                    }
                }
            } catch (error) {
                hideLoading();
                console.error('Error:', error);
                showErrorMessage('Error de conexión. Intenta de nuevo.', 'barcodeResult');
            } finally {
                barcodeInput.disabled = false;
                barcodeButton.disabled = false;
                barcodeInput.focus();
            }
        });

        // ==============================================
        // CONFIRMACIÓN RFID
        // ==============================================
        
        function showRfidConfirmModal(unitData) {
            pickingState.pendingRfidEPC = unitData.epc;
            
            const modal = document.getElementById('rfidConfirmModal');
            const content = document.getElementById('rfidModalContent');
            
            let html = `
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Producto</p>
                            <p class="font-bold text-gray-900">${unitData.product_name}</p>
                            <p class="text-xs text-gray-600 font-mono">${unitData.product_code}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase">EPC</p>
                            <p class="font-mono text-xs text-gray-700">${unitData.epc}</p>
                        </div>
            `;
            
            if (unitData.serial_number) {
                html += `
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Serial</p>
                            <p class="font-semibold">${unitData.serial_number}</p>
                        </div>
                `;
            }
            
            if (unitData.batch_number) {
                html += `
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Lote</p>
                            <p class="font-semibold">${unitData.batch_number}</p>
                        </div>
                `;
            }
            
            if (unitData.expiration_date) {
                const daysText = unitData.days_until_expiration 
                    ? `(${unitData.days_until_expiration} días)` 
                    : '';
                const isExpiringSoon = unitData.is_expiring_soon;
                const expiryClass = isExpiringSoon ? 'text-red-600' : 'text-gray-900';
                
                html += `
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Caducidad</p>
                            <p class="font-semibold ${expiryClass}">
                                ${unitData.expiration_date}
                                ${isExpiringSoon ? '<i class="fas fa-exclamation-triangle ml-1"></i>' : ''}
                            </p>
                            <p class="text-xs text-gray-600">${daysText}</p>
                        </div>
                `;
            }
            
            if (unitData.location_code) {
                html += `
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Ubicación</p>
                            <p class="font-semibold text-indigo-600">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                ${unitData.location_code}
                            </p>
                        </div>
                `;
            }
            
            html += `
                    </div>
                </div>
            `;
            
            if (unitData.is_expiring_soon) {
                html += `
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-2"></i>
                        <p class="text-sm text-yellow-800">
                            Esta unidad está próxima a caducar (${unitData.days_until_expiration} días)
                        </p>
                    </div>
                `;
            }
            
            content.innerHTML = html;
            modal.classList.remove('hidden');
        }

        async function confirmRfidUnit() {
            const epc = pickingState.pendingRfidEPC;
            if (!epc) return;
            
            const confirmBtn = document.getElementById('confirmRfidBtn');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...';
            
            try {
                const response = await fetch('{{ route("surgeries.preparations.confirmRFID", $surgery) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ epc: epc })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeRfidModal();
                    showSuccessMessage(data.message, 'rfidResult');
                    appendToRfidConsole(`✓ Unidad confirmada y agregada: ${data.data.product_name}`, 'success');
                    
                    updateItemInTable(data.data);
                    await updateProgress();
                    
                    if (data.data.quantity_missing <= 0) {
                        setTimeout(() => removeItemFromTable(data.data.item_id), 1000);
                    }
                    
                    if (data.data.preparation_complete) {
                        showCompletionAlert();
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al confirmar la unidad');
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Confirmar y Agregar';
            }
        }

        function closeRfidModal() {
            document.getElementById('rfidConfirmModal').classList.add('hidden');
            pickingState.pendingRfidEPC = null;
        }

        // ==============================================
        // FUNCIONES DE UI
        // ==============================================
        
        function showLoading() {
            document.getElementById('loadingIndicator').classList.remove('hidden');
        }
        
        function hideLoading() {
            document.getElementById('loadingIndicator').classList.add('hidden');
        }
        
        function showSuccessMessage(message, targetId) {
            const resultDiv = document.getElementById(targetId);
            resultDiv.className = 'mt-4 p-4 bg-green-50 border border-green-200 rounded-lg';
            resultDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                    <p class="font-semibold text-green-900">${message}</p>
                </div>
            `;
            resultDiv.classList.remove('hidden');
            setTimeout(() => resultDiv.classList.add('hidden'), 3000);
        }
        
        function showErrorMessage(message, targetId) {
            const resultDiv = document.getElementById(targetId);
            resultDiv.className = 'mt-4 p-4 bg-red-50 border border-red-300 rounded-lg';
            resultDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-600 text-2xl mr-3"></i>
                    <p class="font-semibold text-red-900">${message}</p>
                </div>
            `;
            resultDiv.classList.remove('hidden');
            setTimeout(() => resultDiv.classList.add('hidden'), 5000);
        }

        function showUnitInfo(unitInfo, targetId) {
            const resultDiv = document.getElementById(targetId);
            let infoHtml = '<div class="mt-2 text-xs text-gray-600 space-y-1">';
            if (unitInfo.batch) infoHtml += `<p>📦 Lote: <strong>${unitInfo.batch}</strong></p>`;
            if (unitInfo.expiration) {
                const daysText = unitInfo.days_until_expiration ? ` (${unitInfo.days_until_expiration} días)` : '';
                infoHtml += `<p>📅 Caducidad: <strong>${unitInfo.expiration}</strong>${daysText}</p>`;
            }
            if (unitInfo.location) infoHtml += `<p>📍 Ubicación: <strong>${unitInfo.location}</strong></p>`;
            infoHtml += '</div>';
            resultDiv.innerHTML += infoHtml;
        }

        function showOtherUnitsInfo(otherUnits, targetId) {
            const resultDiv = document.getElementById(targetId);
            let html = '<div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded">';
            html += '<p class="text-sm font-semibold text-yellow-800 mb-2">Unidades en otros estados:</p>';
            html += '<ul class="text-xs text-yellow-700 space-y-1">';
            for (const [status, info] of Object.entries(otherUnits)) {
                html += `<li>• ${info.status_label}: ${info.count} unidad(es)</li>`;
            }
            html += '</ul></div>';
            resultDiv.innerHTML += html;
        }
        
        function updateItemInTable(itemData) {
            const pickedSpan = document.getElementById(`picked-${itemData.item_id}`);
            const missingSpan = document.getElementById(`missing-${itemData.item_id}`);
            const row = document.getElementById(`item-row-${itemData.item_id}`);
            
            if (pickedSpan && missingSpan && row) {
                pickedSpan.textContent = itemData.quantity_picked;
                missingSpan.textContent = itemData.quantity_missing;
                
                if (itemData.quantity_missing <= 0) {
                    row.classList.add('bg-green-50', 'opacity-75');
                    missingSpan.classList.remove('bg-red-100', 'text-red-700');
                    missingSpan.classList.add('bg-gray-100', 'text-gray-400');
                    
                    const checkIcon = document.createElement('i');
                    checkIcon.className = 'fas fa-check-circle text-green-500 ml-2 animate-bounce';
                    missingSpan.appendChild(checkIcon);
                }
            }
        }
        
        function removeItemFromTable(itemId) {
            const row = document.getElementById(`item-row-${itemId}`);
            if (row) {
                row.style.transition = 'all 0.5s ease-out';
                row.style.opacity = '0';
                row.style.transform = 'translateX(100%)';
                
                setTimeout(() => {
                    row.remove();
                    const pendingCount = document.querySelectorAll('tbody tr[id^="item-row-"]').length;
                    document.getElementById('pending-count').textContent = pendingCount;
                    
                    if (pendingCount === 0) {
                        const tbody = document.querySelector('#pendingItemsTable tbody');
                        tbody.innerHTML = `
                            <tr id="empty-state">
                                <td colspan="6" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
                                        <p class="text-gray-700 font-semibold text-lg">¡Excelente trabajo!</p>
                                        <p class="text-gray-500 text-sm mt-1">No hay productos pendientes por surtir</p>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                }, 500);
            }
        }
        
        async function updateProgress() {
            try {
                const response = await fetch('{{ route("surgeries.preparations.status", $surgery) }}');
                const result = await response.json();
                
                if (result.success) {
                    const summary = result.data;
                    document.getElementById('progress-bar').style.width = summary.completion_percentage + '%';
                    document.getElementById('progress-percentage').textContent = Math.round(summary.completion_percentage) + '%';
                    document.getElementById('required-quantity').textContent = summary.total_quantity_required;
                    document.getElementById('picked-quantity').textContent = summary.total_quantity_picked;
                    document.getElementById('missing-quantity').textContent = summary.total_quantity_missing;
                    document.getElementById('mandatory-pending').textContent = summary.mandatory_pending;
                }
            } catch (error) {
                console.error('Error al actualizar progreso:', error);
            }
        }
        
        function showCompletionAlert() {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-8 py-4 rounded-lg shadow-2xl z-50 animate-bounce';
            alertDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-3xl mr-4"></i>
                    <div>
                        <p class="font-bold text-xl">¡Preparación Completa!</p>
                        <p class="text-sm">Todos los productos han sido surtidos</p>
                    </div>
                </div>
            `;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 5000);
        }
        
        function toggleCompleted() {
            const section = document.getElementById('completed-section');
            const icon = document.getElementById('toggle-icon');
            section.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        }
        
        function refreshPage() {
            window.location.reload();
        }
        
        // ==============================================
        // CANCELACIÓN
        // ==============================================
        
        function openCancelModal() {
            document.getElementById('cancelModal').classList.remove('hidden');
        }
        
        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
            document.getElementById('cancel_reason').value = '';
        }
        
        document.getElementById('cancelForm').addEventListener('submit', async function(e) {
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
                    window.locastion.href = '{{ route("surgeries.show", $surgery) }}';
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cancelar la preparación');
            }
        });
        
        // Auto-focus inicial
        document.getElementById('barcode_scan').focus();
        
        // Actualizar progreso cada 30 segundos
        setInterval(updateProgress, 30000);
        
        console.log('✅ Sistema de picking dual inicializado');
    </script>
    @endpush
</x-app-layout>