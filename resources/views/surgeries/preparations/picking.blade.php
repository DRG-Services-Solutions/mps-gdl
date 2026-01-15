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
                         <p class="text-xs text-blue-200 mt-1">
                            Piezas Faltantes: <strong id="mandatory-pending">{{ $summary['total_quantity_missing'] }}</strong>
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

            {{-- Formulario de Escaneo --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <form id="scanForm" class="flex items-end space-x-4">
                        @csrf
                        <div class="flex-1">
                            <label for="epc_scan" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-barcode mr-1"></i>
                                Escanear EPC del Producto Faltante
                            </label>
                            <input type="text" 
                                   id="epc_scan" 
                                   name="epc" 
                                   class="w-full font-mono text-lg rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" 
                                   placeholder="Acerque el lector RFID al tag..." 
                                   autofocus>
                        </div>
                        <button type="submit" 
                                id="scanButton"
                                class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-plus mr-2"></i>
                            AGREGAR
                        </button>
                    </form>
                    
                    {{-- Área de Resultados --}}
                    <div id="scanResult" class="mt-4 hidden transition-all duration-300"></div>
                    
                    {{-- Loading Indicator --}}
                    <div id="loadingIndicator" class="mt-4 hidden">
                        <div class="flex items-center justify-center p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600 mr-3"></div>
                            <span class="text-indigo-700 font-medium">Procesando escaneo...</span>
                        </div>
                    </div>
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

            {{-- Debug Panel --}}
            <!--
            @if(config('app.debug'))
                <div class="mt-10 p-4 bg-gray-900 text-green-400 font-mono text-xs rounded-lg shadow-2xl opacity-80 hover:opacity-100 transition-opacity">
                    <h4 class="border-b border-gray-700 mb-3 pb-2 text-white uppercase font-bold flex items-center">
                        <i class="fas fa-bug mr-2"></i> Debug - Estado de Preparación
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-gray-400 text-[10px] mb-1">IDENTIFICADORES</p>
                            <p>> Prep ID: <span class="text-yellow-400">{{ $preparation->id }}</span></p>
                            <p>> Surgery ID: <span class="text-yellow-400">{{ $surgery->id }}</span></p>
                            <p>> Package ID: <span class="text-yellow-400">{{ $preparation->pre_assembled_package_id ?? 'N/A' }}</span></p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-[10px] mb-1">ESTADOS</p>
                            <p>> Estado: <span class="text-cyan-400">{{ $preparation->status }}</span></p>
                            <p>> Completitud: <span class="text-cyan-400">{{ $summary['completion_percentage'] }}%</span></p>
                            <p>> Iniciado: <span class="text-cyan-400">{{ $preparation->started_at?->diffForHumans() ?? 'N/A' }}</span></p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-[10px] mb-1">CONTADORES</p>
                            <p>> Total Items: <span class="text-blue-400">{{ $summary['total_items'] }}</span></p>
                            <p>> Pendientes: <span class="text-red-400">{{ $summary['pending_items'] }}</span></p>
                            <p>> En Paquete: <span class="text-purple-400">{{ $summary['in_package_items'] }}</span></p>
                            <p>> Completados: <span class="text-green-400">{{ $summary['completed_items'] }}</span></p>
                            <p>> Obligatorios Pendientes: <span class="text-orange-400">{{ $summary['mandatory_pending'] }}</span></p>
                        </div>
                    </div>
                </div>
            @endif
            -->

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

    @push('scripts')
    <script>
        // ==============================================
        // ESCANEO DE PRODUCTOS RFID
        // ==============================================
        document.getElementById('scanForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const epcInput = document.getElementById('epc_scan');
            const scanButton = document.getElementById('scanButton');
            const epc = epcInput.value.trim();
            
            if (!epc) return;
            
            // Deshabilitar input durante el proceso
            epcInput.disabled = true;
            scanButton.disabled = true;
            
            // Mostrar loading
            showLoading();
            
            try {
                const response = await fetch('{{ route("surgeries.preparations.scan", $surgery) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ epc: epc })
                });
                
                const data = await response.json();
                
                hideLoading();
                
                if (data.success) {
                    showSuccessMessage(data.message);
                    
                    // Actualizar UI con los datos devueltos
                    updateItemInTable(data.data);
                    
                    // Actualizar progreso global
                    await updateProgress();
                    
                    // Limpiar input
                    epcInput.value = '';
                    
                    // Si la preparación está completa
                    if (data.data.preparation_complete) {
                        showCompletionAlert();
                    }
                    
                    // Si el item se completó, eliminarlo de la tabla después de 1 segundo
                    if (data.data.quantity_missing <= 0) {
                        setTimeout(() => {
                            removeItemFromTable(data.data.item_id);
                        }, 1000);
                    }
                    
                } else {
                    showErrorMessage(data.message);
                }
                
            } catch (error) {
                hideLoading();
                console.error('Error de conexión:', error);
                showErrorMessage('Error de conexión. Por favor, intenta de nuevo.');
            } finally {
                // Rehabilitar input
                epcInput.disabled = false;
                scanButton.disabled = false;
                epcInput.focus();
            }
        });

        // ==============================================
        // FUNCIONES DE UI
        // ==============================================
        
        function showLoading() {
            document.getElementById('loadingIndicator').classList.remove('hidden');
            document.getElementById('scanResult').classList.add('hidden');
        }
        
        function hideLoading() {
            document.getElementById('loadingIndicator').classList.add('hidden');
        }
        
        function showSuccessMessage(message) {
            const resultDiv = document.getElementById('scanResult');
            resultDiv.className = 'mt-4 p-4 bg-green-50 border border-green-200 rounded-lg animate-pulse';
            resultDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                    <div>
                        <p class="font-semibold text-green-900">${message}</p>
                    </div>
                </div>
            `;
            resultDiv.classList.remove('hidden');
            
            // Auto-ocultar después de 3 segundos
            setTimeout(() => resultDiv.classList.add('hidden'), 3000);
        }
        
        function showErrorMessage(message) {
            const resultDiv = document.getElementById('scanResult');
            resultDiv.className = 'mt-4 p-4 bg-red-50 border border-red-300 rounded-lg';
            resultDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-600 text-2xl mr-3"></i>
                    <div>
                        <p class="font-semibold text-red-900">${message}</p>
                    </div>
                </div>
            `;
            resultDiv.classList.remove('hidden');
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => resultDiv.classList.add('hidden'), 5000);
        }
        
        function updateItemInTable(itemData) {
            const pickedSpan = document.getElementById(`picked-${itemData.item_id}`);
            const missingSpan = document.getElementById(`missing-${itemData.item_id}`);
            const row = document.getElementById(`item-row-${itemData.item_id}`);
            
            if (pickedSpan && missingSpan && row) {
                // ✅ CORRECCIÓN: SIEMPRE actualizar quantity_picked
                // La respuesta debe incluir la cantidad actual de picked
                const currentPicked = parseInt(pickedSpan.textContent) || 0;
                pickedSpan.textContent = currentPicked + 1; // Incrementar en 1
                
                // Actualizar faltantes
                missingSpan.textContent = itemData.quantity_missing;
                
                // Si se completó el item, cambiar estilos
                if (itemData.quantity_missing <= 0) {
                    row.classList.add('bg-green-50', 'opacity-75');
                    missingSpan.classList.remove('bg-red-100', 'text-red-700');
                    missingSpan.classList.add('bg-gray-100', 'text-gray-400');
                    
                    // Animación de check
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
                    
                    // Actualizar contador de pendientes
                    const pendingCount = document.querySelectorAll('tbody tr[id^="item-row-"]').length;
                    document.getElementById('pending-count').textContent = pendingCount;
                    
                    // Si no quedan items, mostrar mensaje vacío
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
                    
                    // Actualizar barra de progreso
                    const progressBar = document.getElementById('progress-bar');
                    const progressText = document.getElementById('progress-percentage');
                    
                    progressBar.style.width = summary.completion_percentage + '%';
                    progressText.textContent = Math.round(summary.completion_percentage) + '%';
                    
                    // ✅ ACTUALIZAR CANTIDADES
                    document.getElementById('satisfied-quantity').textContent = summary.total_quantity_satisfied;
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
            // Animación de confeti si tienes la librería
            if (typeof confetti !== 'undefined') {
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { y: 0.6 }
                });
            }
            
            // Mostrar alerta de completado
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
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
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
        // CANCELACIÓN DE PREPARACIÓN
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
            
            if (!confirm('¿Estás seguro de que deseas cancelar esta preparación? Esta acción no se puede deshacer.')) {
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
        
        // Auto-focus en el input de escaneo
        document.getElementById('epc_scan').focus();
        
        // Actualizar progreso cada 30 segundos
        setInterval(updateProgress, 30000);
    </script>
    @endpush
</x-app-layout>