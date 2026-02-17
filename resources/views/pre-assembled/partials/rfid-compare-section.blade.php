{{-- ============================================================== --}}
{{-- PARTIAL: RFID Comparativa con Checklist                       --}}
{{-- @ubicación resources/views/pre-assembled/partials/rfid-compare-section.blade.php --}}
{{--                                                                --}}
{{-- INSERTAR en show.blade.php DESPUÉS de "Escaneo Individual"    --}}
{{-- y ANTES de "Contenido del Paquete":                           --}}
{{--   @include('pre-assembled.partials.rfid-compare-section')     --}}
{{-- ============================================================== --}}

{{-- Config para JavaScript (data attributes) --}}
<div id="rfid-compare-config" class="hidden"
    data-package-id="{{ $preAssembled->id }}"
    data-csrf="{{ csrf_token() }}"
    data-route-compare="{{ route('pre-assembled.rfid-compare', $preAssembled) }}"
    data-route-add="{{ route('pre-assembled.rfid-add', $preAssembled) }}"
    data-route-search="{{ route('pre-assembled.search-epc', $preAssembled) }}"
></div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECCIÓN PRINCIPAL                                              --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden border-2 border-indigo-200">

    {{-- Header --}}
    <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold">
                    <i class="fas fa-satellite-dish mr-2"></i>
                    Comparativa RFID vs Checklist
                </h3>
                <p class="text-sm text-indigo-200 mt-1">
                    Checklist: {{ $preAssembled->surgeryChecklist->surgery_type ?? 'N/A' }}
                    ({{ $preAssembled->surgeryChecklist->items->count() ?? 0 }} items)
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span id="rfid-tags-count" class="text-2xl font-bold">0</span>
                <span class="text-sm text-indigo-200">tags</span>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">

        {{-- ─── Control del Lector RFID ─── --}}
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-sliders-h mr-1"></i>
                    Control del Lector RFID
                </h4>
                <div id="rfid-status">
                    <span class="text-gray-500 text-sm">
                        <i class="fas fa-circle text-xs mr-1"></i>Desconectado
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 mb-3">
                <button id="rfid-connect-btn"
                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-plug mr-1"></i> Conectar
                </button>
                <button id="rfid-disconnect-btn" disabled
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-500 hover:bg-gray-600 rounded-lg transition-colors shadow-sm disabled:opacity-50">
                    <i class="fas fa-unlink mr-1"></i> Desconectar
                </button>
                <button id="rfid-start-btn" disabled
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm disabled:opacity-50">
                    <i class="fas fa-play mr-1"></i> Iniciar Lectura
                </button>
                <button id="rfid-stop-btn" disabled
                    class="px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors shadow-sm disabled:opacity-50">
                    <i class="fas fa-stop mr-1"></i> Detener
                </button>
                <button id="rfid-clear-btn"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">
                    <i class="fas fa-eraser mr-1"></i> Limpiar Tags
                </button>
            </div>

            <div id="rfid-feedback" class="text-sm text-gray-600 bg-white px-3 py-2 rounded border border-gray-200">
                Presione "Conectar" para iniciar el lector RFID.
            </div>
        </div>

        {{-- ─── Consola de Eventos RFID ─── --}}
        <div class="bg-gray-900 rounded-lg overflow-hidden">
            <div class="flex items-center justify-between px-4 py-2 bg-gray-800">
                <span class="text-xs font-medium text-gray-400">
                    <i class="fas fa-terminal mr-1"></i>
                    Consola RFID
                </span>
                <button onclick="document.getElementById('rfid-console').innerHTML = ''"
                    class="text-xs text-gray-500 hover:text-gray-300 transition-colors">
                    <i class="fas fa-trash-alt mr-1"></i>Limpiar
                </button>
            </div>
            <div id="rfid-console"
                class="px-4 py-3 h-40 overflow-y-auto font-mono text-xs leading-5"
                style="color: #f0f0f0;">
            </div>
        </div>

        {{-- ─── Tags Escaneados ─── --}}
        <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-indigo-800">
                    <i class="fas fa-tags mr-1"></i>
                    Tags Escaneados
                </h4>
                <label class="flex items-center space-x-2 text-xs text-indigo-700 cursor-pointer">
                    <input type="checkbox" id="auto-add-toggle"
                        class="rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500">
                    <span>Auto-agregar al paquete</span>
                </label>
            </div>
            <div id="scanned-tags-list" class="max-h-48 overflow-y-auto">
                <div class="text-center text-gray-400 py-4">
                    <i class="fas fa-satellite-dish text-3xl mb-2"></i>
                    <p class="text-sm">Esperando tags RFID...</p>
                </div>
            </div>
        </div>

        {{-- ─── Botones de Acción ─── --}}
        <div class="flex flex-wrap gap-3">
            <button id="rfid-compare-btn"
                class="flex-1 px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-balance-scale mr-2"></i>
                Comparar con Checklist
            </button>
            <button id="rfid-add-all-btn"
                class="px-6 py-3 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-plus-circle mr-2"></i>
                Agregar Todos al Paquete
            </button>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- RESULTADOS (hidden por defecto, se muestra con JS) --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div id="comparison-section" class="hidden space-y-6">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="bg-white rounded-lg p-3 border border-gray-200 text-center">
                    <p class="text-xs text-gray-500">Completitud</p>
                    <p id="stat-percentage" class="text-xl font-bold text-indigo-600">0%</p>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div id="stat-progress-bar" class="h-full bg-indigo-500 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>
                <div class="bg-green-50 rounded-lg p-3 border border-green-200 text-center">
                    <p class="text-xs text-green-600">Completos</p>
                    <p id="stat-complete" class="text-xl font-bold text-green-700">0</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-200 text-center">
                    <p class="text-xs text-yellow-600">Parciales</p>
                    <p id="stat-partial" class="text-xl font-bold text-yellow-700">0</p>
                </div>
                <div class="bg-red-50 rounded-lg p-3 border border-red-200 text-center">
                    <p class="text-xs text-red-600">Faltantes</p>
                    <p id="stat-missing" class="text-xl font-bold text-red-700">0</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-3 border border-orange-200 text-center">
                    <p class="text-xs text-orange-600">Extras</p>
                    <p id="stat-extra" class="text-xl font-bold text-orange-700">0</p>
                </div>
            </div>

            {{-- Tabla Comparativa: Items del Checklist --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-clipboard-check mr-1 text-indigo-600"></i>
                        Items del Checklist
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-500 uppercase">Requerido</th>
                                <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-500 uppercase">En Paquete</th>
                                <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-500 uppercase">Escaneado</th>
                                <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-500 uppercase">Faltante</th>
                                <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">EPCs</th>
                            </tr>
                        </thead>
                        <tbody id="comparison-table-body" class="bg-white divide-y divide-gray-200">
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Items Extra --}}
            <div id="extras-section" class="hidden">
                <div class="bg-white rounded-lg border-2 border-yellow-300 overflow-hidden">
                    <div class="px-4 py-3 bg-yellow-50 border-b border-yellow-200">
                        <h4 class="text-sm font-semibold text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-1 text-yellow-600"></i>
                            Productos EXTRA (no están en el Checklist)
                        </h4>
                        <p class="text-xs text-yellow-600 mt-1">
                            Estos productos fueron escaneados pero no forman parte del checklist.
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-yellow-50">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-yellow-700 uppercase">Producto</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-medium text-yellow-700 uppercase">Cantidad</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-medium text-yellow-700 uppercase">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="extras-table-body" class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- EPCs Desconocidos --}}
            <div id="unknown-section" class="hidden">
                <div class="bg-red-50 rounded-lg border border-red-200 p-4">
                    <h4 class="text-sm font-semibold text-red-800 mb-2">
                        <i class="fas fa-question-circle mr-1"></i>
                        EPCs No Registrados en el Sistema
                    </h4>
                    <div id="unknown-epcs-list"></div>
                </div>
            </div>
        </div>
    </div>
</div>