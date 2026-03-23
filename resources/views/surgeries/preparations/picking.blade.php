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
                        <div class="flex flex-wrap items-center gap-2 mt-3 md:mt-0">
                @if($summary['mandatory_pending'] == 0)
                    <a href="{{ route('surgeries.preparations.verify', $surgery) }}" 
                    class="inline-flex items-center px-3 py-2 md:px-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 text-sm">
                        <i class="fas fa-check-double mr-1 md:mr-2"></i>
                        <span class="hidden sm:inline">Verificar y Completar</span>
                        <span class="sm:hidden">Verificar</span>
                    </a>
                @else
                    <button disabled
                            class="inline-flex items-center px-3 py-2 md:px-4 bg-gray-400 text-white font-semibold rounded-lg shadow-md cursor-not-allowed opacity-60 text-sm">
                        <i class="fas fa-check-double mr-1 md:mr-2"></i>
                        <span class="hidden sm:inline">Verificar ({{ $summary['mandatory_pending'] }} pendientes)</span>
                        <span class="sm:hidden">{{ $summary['mandatory_pending'] }} pend.</span>
                    </button>
                @endif
                
                <a href="{{ route('surgeries.preparations.compare', $surgery) }}" 
                class="inline-flex items-center px-3 py-2 md:px-4 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>
                    <span class="hidden sm:inline">Volver</span>
                </a>

                <button onclick="openCancelModal()" 
                        class="inline-flex items-center px-3 py-2 md:px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 text-sm">
                    <i class="fas fa-times mr-1"></i>
                    <span class="hidden sm:inline">Cancelar</span>
                </button>
            </div>
        </div>
    </x-slot>

    {{-- 🆕 IMPORTANTE: data-surgery-id para JavaScript --}}
    <div class="py-6" data-surgery-id="{{ $surgery->id }}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Barra de Progreso Global --}}
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-lg shadow-lg p-4 md:p-6 text-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-1">Progreso Total de Surtido</h3>
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
                    <div class="text-left sm:text-right">
                        <p id="progress-percentage" class="text-3xl md:text-4xl font-bold">{{ number_format($summary['completion_percentage'], 0) }}%</p>
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
            <div class="flex items-center justify-center gap-2 md:gap-4">
                <button id="manualModeBtn" 
                        onclick="switchMode('manual')"
                        class="mode-btn active flex items-center px-4 py-2.5 md:px-6 md:py-3 rounded-lg font-semibold transition-all duration-200 text-sm md:text-base">
                    <i class="fas fa-barcode mr-1.5 md:mr-2"></i>
                    <span class="hidden sm:inline">Modo Manual (Barcode)</span>
                    <span class="sm:hidden">Manual</span>
                </button>
                <button id="rfidModeBtn" 
                        onclick="switchMode('rfid')"
                        class="mode-btn flex items-center px-4 py-2.5 md:px-6 md:py-3 rounded-lg font-semibold transition-all duration-200 text-sm md:text-base">
                    <i class="fas fa-broadcast-tower mr-1.5 md:mr-2"></i>
                    <span class="hidden sm:inline">Modo RFID</span>
                    <span class="sm:hidden">RFID</span>
                </button>
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
                    <form id="barcodeForm" class="flex flex-col sm:flex-row sm:items-end gap-3 sm:gap-4">

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
                                class="w-full sm:w-auto bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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
                        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
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

            {{-- Productos Excluidos/Modificados por Condicionales --}}
            @if(!empty($excludedByConditionals))
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <button onclick="toggleExcluded()" 
                        class="w-full px-6 py-4 flex justify-between items-center hover:bg-gray-50 transition-colors bg-gray-50 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center">
                        <i class="fas fa-ban text-red-400 mr-2"></i>
                        Productos No Incluidos por Condicional ({{ count($excludedByConditionals) }})
                    </h3>
                    <i id="toggle-excluded-icon" class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                </button>
                
                <div id="excluded-section" class="hidden">
                    <div class="p-4 bg-amber-50 border-b border-amber-100">
                        <p class="text-xs text-amber-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            Estos productos están en el checklist base pero fueron excluidos o reemplazados por condicionales específicos de esta cirugía. No es necesario surtirlos.
                        </p>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($excludedByConditionals as $excluded)
                            <div class="px-6 py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 {{ $excluded['action_type'] === 'replace' ? 'bg-orange-50' : 'bg-red-50' }}">
                                {{-- Producto --}}
                                <div class="flex items-center flex-1 min-w-0">
                                    @if($excluded['action_type'] === 'exclude')
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-ban text-red-500 text-sm"></i>
                                        </span>
                                    @elseif($excluded['action_type'] === 'replace')
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-exchange-alt text-orange-500 text-sm"></i>
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-900 truncate line-through decoration-red-400">
                                            {{ $excluded['product_name'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 font-mono">{{ $excluded['product_code'] }}</p>
                                    </div>
                                </div>

                                {{-- Cantidad original --}}
                                <div class="flex-shrink-0 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-gray-200 text-gray-500 text-xs font-bold line-through">
                                        {{ $excluded['base_quantity'] }} uds.
                                    </span>
                                </div>

                                {{-- Badge de acción --}}
                                <div class="flex-shrink-0">
                                    @if($excluded['action_type'] === 'exclude')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                            <i class="fas fa-ban"></i> EXCLUIDO
                                        </span>
                                    @elseif($excluded['action_type'] === 'replace')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700 border border-orange-200">
                                            <i class="fas fa-exchange-alt"></i> REEMPLAZADO
                                        </span>
                                    @endif
                                </div>

                                {{-- Razón / Target --}}
                                <div class="flex-1 min-w-0 text-right sm:text-left">
                                    @if($excluded['target_product'])
                                        <p class="text-xs text-orange-700 font-medium">
                                            <i class="fas fa-arrow-right mr-1"></i>
                                            Usar en su lugar: <strong>{{ $excluded['target_product'] }}</strong>
                                        </p>
                                    @endif
                                    <p class="text-[10px] text-gray-500 mt-0.5 truncate">
                                        {{ $excluded['criteria'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

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
                        <thead class="bg-gray-50 hidden md:table-header-group">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-indigo-500 uppercase tracking-wider">
                                    <i class="fas fa-map-marker-alt mr-1"></i>Ubicación
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-blue-500 uppercase tracking-wider">
                                    <i class="fas fa-box mr-1"></i>En Paquete
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-green-500 uppercase tracking-wider">Surtidos</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-red-500 uppercase tracking-wider">Faltan</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Requeridos</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-purple-500 uppercase tracking-wider">Condicionales</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($pendingItems as $item)
                                @php
                                    $condCount = $item->conditionals ? $item->conditionals->count() : 0;
                                @endphp
                                {{-- ═══════════ DESKTOP: fila de tabla normal ═══════════ --}}
                                <tr class="hover:bg-red-50 transition-colors hidden md:table-row" id="item-row-{{ $item->id }}">
                                    
                                    {{-- Producto --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            @if($item->is_mandatory)
                                                <i class="fas fa-star text-yellow-500 mr-2" title="Obligatorio"></i>
                                            @endif
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $item->product->name }}</div>
                                                <div class="text-xs text-gray-500 font-mono">{{ $item->product->code }}</div>
                                                @if($item->notes && str_starts_with($item->notes, 'Dependencia de:'))
                                                    <span class="inline-flex items-center gap-0.5 mt-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-blue-100 text-blue-700">
                                                        <i class="fas fa-link"></i> {{ $item->notes }}
                                                    </span>
                                                @elseif($item->notes && str_starts_with($item->notes, 'Reemplazo de:'))
                                                    <span class="inline-flex items-center gap-0.5 mt-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-orange-100 text-orange-700">
                                                        <i class="fas fa-exchange-alt"></i> {{ $item->notes }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Ubicación --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($item->storageLocation)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $item->storageLocation->code }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-300 italic">— Sin ubicación —</span>
                                        @endif
                                    </td>

                                    {{-- En Paquete --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full font-bold text-sm
                                            {{ $item->quantity_in_package > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-50 text-gray-400' }}">
                                            {{ $item->quantity_in_package }}
                                        </span>
                                    </td>

                                    {{-- Surtidos --}}
                                    <td class="px-6 py-4 text-center">
                                        <span id="picked-{{ $item->id }}" 
                                            class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full font-bold text-sm">
                                            {{ $item->quantity_picked }}
                                        </span>
                                    </td>

                                    {{-- Faltan --}}
                                    <td class="px-6 py-4 text-center">
                                        <span id="missing-{{ $item->id }}" 
                                            class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-full font-bold text-sm animate-pulse">
                                            {{ $item->quantity_missing }}
                                        </span>
                                    </td>

                                    {{-- Requeridos --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-gray-600 font-medium">{{ $item->quantity_required }}</span>
                                    </td>

                                    {{-- Condicionales --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($condCount > 0)
                                            <button type="button" 
                                                    onclick="openConditionalsModal('{{ $item->product->name }}', 'conditionals-data-{{ $item->id }}')" 
                                                    class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-700 hover:bg-purple-200 rounded-full font-bold text-xs transition-colors cursor-pointer">
                                                <i class="fas fa-list-ul mr-1"></i> 
                                                Ver {{ $condCount }}
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs italic">Sin Condicional</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- ═══════════ MÓVIL: card layout ═══════════ --}}
                                <tr class="md:hidden" id="item-row-mobile-{{ $item->id }}">
                                    <td colspan="7" class="px-4 py-3">
                                        <div class="bg-white border border-red-100 rounded-xl p-4 shadow-sm space-y-3">
                                            
                                            {{-- Header: Producto + Obligatorio --}}
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-center flex-1 min-w-0">
                                                    @if($item->is_mandatory)
                                                        <i class="fas fa-star text-yellow-500 mr-2 flex-shrink-0" title="Obligatorio"></i>
                                                    @endif
                                                    <div class="min-w-0">
                                                        <p class="font-bold text-gray-900 text-sm truncate">{{ $item->product->name }}</p>
                                                        <p class="text-xs text-gray-500 font-mono">{{ $item->product->code }}</p>
                                                        @if($item->notes && str_starts_with($item->notes, 'Dependencia de:'))
                                                            <span class="inline-flex items-center gap-0.5 mt-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-blue-100 text-blue-700">
                                                                <i class="fas fa-link"></i> {{ $item->notes }}
                                                            </span>
                                                        @elseif($item->notes && str_starts_with($item->notes, 'Reemplazo de:'))
                                                            <span class="inline-flex items-center gap-0.5 mt-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-orange-100 text-orange-700">
                                                                <i class="fas fa-exchange-alt"></i> {{ $item->notes }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($condCount > 0)
                                                    <button type="button" 
                                                            onclick="openConditionalsModal('{{ $item->product->name }}', 'conditionals-data-{{ $item->id }}')" 
                                                            class="ml-2 flex-shrink-0 inline-flex items-center px-2 py-1 bg-purple-100 text-purple-700 rounded-full font-bold text-[10px]">
                                                        <i class="fas fa-filter mr-1"></i> {{ $condCount }}
                                                    </button>
                                                @endif
                                            </div>

                                            {{-- Ubicación --}}
                                            @if($item->storageLocation)
                                                <div class="flex items-center">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                                        {{ $item->storageLocation->code }}
                                                    </span>
                                                </div>
                                            @endif

                                            {{-- Grid de cantidades --}}
                                            <div class="grid grid-cols-4 gap-2">
                                                <div class="text-center p-2 rounded-lg bg-blue-50">
                                                    <p class="text-lg font-black text-blue-600">{{ $item->quantity_in_package }}</p>
                                                    <p class="text-[9px] font-bold text-blue-400 uppercase">Paquete</p>
                                                </div>
                                                <div class="text-center p-2 rounded-lg bg-green-50">
                                                    <p id="picked-mobile-{{ $item->id }}" class="text-lg font-black text-green-600">{{ $item->quantity_picked }}</p>
                                                    <p class="text-[9px] font-bold text-green-400 uppercase">Surtidos</p>
                                                </div>
                                                <div class="text-center p-2 rounded-lg bg-red-50">
                                                    <p id="missing-mobile-{{ $item->id }}" class="text-lg font-black text-red-600 animate-pulse">{{ $item->quantity_missing }}</p>
                                                    <p class="text-[9px] font-bold text-red-400 uppercase">Faltan</p>
                                                </div>
                                                <div class="text-center p-2 rounded-lg bg-gray-50">
                                                    <p class="text-lg font-black text-gray-600">{{ $item->quantity_required }}</p>
                                                    <p class="text-[9px] font-bold text-gray-400 uppercase">Total</p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Datos ocultos de condicionales (compartido desktop/móvil) --}}
                                @if($condCount > 0)
                                    <tr class="hidden"><td colspan="7">
                                        <div id="conditionals-data-{{ $item->id }}">
                                            @foreach($item->conditionals as $conditional)
                                                <div class="mb-3 p-3 bg-purple-50 rounded-lg border border-purple-100 text-left">
                                                    <p class="text-sm font-bold text-purple-900 mb-1">
                                                        <i class="fas fa-filter text-purple-600 mr-1"></i> Cuándo aplica:
                                                    </p>
                                                    <p class="text-sm text-gray-700 mb-2">
                                                        {{ $conditional->getDescription() }}
                                                    </p>
                                                    <div class="mt-2 text-sm text-purple-800 bg-white inline-block px-3 py-1.5 rounded border border-purple-200 shadow-sm">
                                                        <strong><i class="fas fa-bolt text-yellow-500 mr-1"></i> Acción:</strong> 
                                                        {{ $conditional->getActionDescription() }}
                                                    </div>
                                                    @if($conditional->notes)
                                                        <p class="mt-3 text-xs text-gray-500 italic border-t border-purple-100 pt-2">
                                                            <i class="fas fa-comment-dots mr-1"></i> Nota: {{ $conditional->notes }}
                                                        </p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td></tr>
                                @endif

                            @empty
                                <tr id="empty-state">
                                    <td colspan="7" class="px-6 py-10 text-center">
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
                                    @if($item->notes && str_starts_with($item->notes, 'Dependencia de:'))
                                        <span class="inline-flex items-center gap-0.5 mt-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-blue-100 text-blue-700">
                                            <i class="fas fa-link"></i> {{ $item->notes }}
                                        </span>
                                    @elseif($item->notes && str_starts_with($item->notes, 'Reemplazo de:'))
                                        <span class="inline-flex items-center gap-0.5 mt-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-orange-100 text-orange-700">
                                            <i class="fas fa-exchange-alt"></i> {{ $item->notes }}
                                        </span>
                                    @endif
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
    <div id="conditionalsModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center transition-opacity">
    <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
        
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-clipboard-list text-purple-600 mr-2"></i>
                Condicionales
            </h3>
            <button onclick="closeConditionalsModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="py-3">
            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Producto:</p>
            <p id="modalProductName" class="text-md font-medium text-gray-800"></p>
        </div>

        <div id="modalConditionalsContent" class="mt-2 max-h-60 overflow-y-auto pr-1">
            </div>

        <div class="mt-5 pt-3 border-t border-gray-200 flex justify-end">
            <button onclick="closeConditionalsModal()" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors">
                Cerrar
            </button>
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
        @vite('resources/js/pages/surgeries/picking-rfid.js')
        
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

            // Toggle productos excluidos
            function toggleExcluded() {
                const section = document.getElementById('excluded-section');
                const icon = document.getElementById('toggle-excluded-icon');
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

    @push('scripts')
        <script>
            function openConditionalsModal(productName, dataContainerId) {
                // 1. Obtener el contenedor del modal y sus elementos
                const modal = document.getElementById('conditionalsModal');
                const nameContainer = document.getElementById('modalProductName');
                const contentContainer = document.getElementById('modalConditionalsContent');
                
                // 2. Extraer el HTML de los condicionales del contenedor oculto de la tabla
                const hiddenData = document.getElementById(dataContainerId).innerHTML;

                // 3. Llenar el modal con los datos
                nameContainer.innerText = productName;
                contentContainer.innerHTML = hiddenData;

                // 4. Mostrar el modal
                modal.classList.remove('hidden');
            }

            function closeConditionalsModal() {
                const modal = document.getElementById('conditionalsModal');
                modal.classList.add('hidden');
            }

            // Opcional: Cerrar el modal al hacer clic afuera de él
            document.getElementById('conditionalsModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeConditionalsModal();
                }
            });
        </script>
    @endpush
</x-app-layout>