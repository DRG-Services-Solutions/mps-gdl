<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-box-open mr-2 text-green-600"></i>
                    {{ $preAssembled->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $preAssembled->code }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="openBulkScanModal()" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-barcode mr-2"></i>
                    Escaneo Masivo
                </button>
                <a href="{{ route('pre-assembled.edit', $preAssembled) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
                <a href="{{ route('pre-assembled.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Cards de estadísticas (sin cambios) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Estado -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Estado</p>
                            <p class="text-xl font-bold text-600 mt-2">
                                @if ($preAssembled->status === 'available')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Disponible
                                    </span>
                                @elseif ($preAssembled->status === 'in_preparation')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-tools mr-1"></i>
                                        En Preparación
                                    </span>
                                @elseif ($preAssembled->status === 'in_surgery')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-procedures mr-1"></i>
                                        En Cirugía
                                    </span>
                                @elseif ($preAssembled->status === 'maintenance')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-wrench mr-1"></i>
                                        Mantenimiento
                                    </span>
                                @endif
                               
                        </div>
                        <div class="bg-100 rounded-full p-3">
                            <i class="fas fa-circle text-2xl text-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Items -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Items</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $preAssembled->contents->count() }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-cubes text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Completitud -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Completitud</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($preAssembled->getCompletenessPercentage(), 1) }}%</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <i class="fas fa-chart-pie text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Usos -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Veces Usado</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $preAssembled->times_used }}</p>
                        </div>
                        <div class="bg-indigo-100 rounded-full p-3">
                            <i class="fas fa-sync-alt text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles (sin cambios) -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle mr-2 text-green-600"></i>
                        Información Detallada
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Código</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $preAssembled->code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Nombre Pre Armado</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $preAssembled->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Check List Base</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="{{ route('checklists.show', $preAssembled->surgeryChecklist) }}" 
                                   class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $preAssembled->surgeryChecklist->surgery_type }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">EPC Contenedor</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $preAssembled->package_epc ?: 'No asignado' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Ubicación</dt>
                            <dd class="text-sm text-gray-900">{{ $preAssembled->storageLocation->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Último Uso</dt>
                            <dd class="text-sm text-gray-900">{{ $preAssembled->last_used_at ? $preAssembled->last_used_at->format('d/m/Y H:i') : 'Nunca' }}</dd>
                        </div>
                        @if($preAssembled->notes)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 mb-1">Notas</dt>
                            <dd class="text-sm text-gray-900">{{ $preAssembled->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            
            <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg shadow-sm overflow-hidden border border-green-200">
                <!-- 🆕 FORMULARIO MEJORADO: Escaneo Individual 
                <div class="px-6 py-4 bg-white bg-opacity-90 border-b border-green-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-barcode mr-2 text-green-600"></i>
                            Escaneo Individual
                        </h3>
                        <div class="flex items-center space-x-2 text-sm">
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded">
                                <i class="fas fa-qrcode mr-1"></i>
                                EPC (24 chars)
                            </span>
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded">
                                <i class="fas fa-barcode mr-1"></i>
                                Código de Barras
                            </span>
                        </div>
                    </div>
                </div>
                -->
                
                <form id="single-scan-form" action="{{ route('pre-assembled.add-product', $preAssembled) }}" method="POST" class="p-6">
                    @csrf
                    
                    <div class="space-y-4">
                        <!-- Campo de escaneo -->
                        <div>
                            <label for="search_input" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i>
                                Escanea o ingresa código
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="search_input" 
                                    id="search_input" 
                                    placeholder="Escanea EPC o código de barras aquí..."
                                    class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-lg font-mono pl-12 pr-4 py-3 @error('search_input') border-red-500 @enderror"
                                    autofocus
                                    autocomplete="off"
                                    required>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-barcode text-gray-400 text-xl"></i>
                                </div>
                                <!-- Indicador de tipo detectado -->
                                <div id="scan-type-indicator" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <!-- Se llenará con JavaScript -->
                                </div>
                            </div>
                            @error('search_input')
                                <p class="mt-2 text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Información de ayuda -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                                </div>
                                <div class="ml-3 flex-1">
                                    <h4 class="text-sm font-medium text-blue-900 mb-2">Tipos de escaneo aceptados:</h4>
                                    <ul class="text-xs text-blue-700 space-y-1">
                                        <li><strong>• EPC (RFID):</strong> 24 caracteres hexadecimales (ej: 303530384E383030303030303031)</li>
                                        <li><strong>• Código de Barras:</strong> Código del producto (ej: 0-102, PROD-001)</li>
                                        <li><strong>• Serial:</strong> Número de serie (ej: SN-12345)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de envío -->
                        <div class="flex justify-between items-center pt-2">
                            <button 
                                type="button"
                                onclick="document.getElementById('search_input').value = ''; document.getElementById('search_input').focus();"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-eraser mr-1"></i>
                                Limpiar
                            </button>
                            <button 
                                type="submit" 
                                class="px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-green-600 to-blue-600 rounded-lg hover:from-green-700 hover:to-blue-700 shadow-md hover:shadow-lg transition-all duration-200">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Agregar Producto
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @include('pre-assembled.partials.rfid-compare-section')

            


            <!-- Contenido del Paquete (sin cambios mayores) -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-box mr-2 text-green-600"></i>
                        Contenido del Paquete ({{ $preAssembled->contents->count() }} items)
                    </h3>
                </div>
                
                @if($preAssembled->contents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EPC / Código</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Caducidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Agregado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($preAssembled->contents->groupBy('product_id') as $productId => $items)
                            @php
                                $firstItem = $items->first();
                                $product = $firstItem->product;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                <i class="fas fa-tag mr-1"></i>
                                                {{ $product->code }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        @foreach($items as $item)
                                            @if($item->productUnit->epc)
                                                <div class="flex items-center space-x-2">
                                                    <i class="fas fa-qrcode text-xs text-gray-400"></i>
                                                    <span class="text-xs font-mono text-gray-600">
                                                        {{ Str::limit($item->productUnit->epc, 20, '...')  }}
                                                    </span>
                                                </div>
                                            @endif
                                            @endforeach
                                                <div class="text-xs text-gray-400">
                                                    <i class="fas fa-qrcode text-xs text-gray-400"></i>
                                                    {{ $item->product->code }}
                                                </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ $items->sum('quantity') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $hasExpired = $items->contains(fn($item) => $item->productUnit && $item->isExpired());
                                        $nearExpiry = $items->contains(fn($item) => $item->productUnit && $item->isExpiringSoon(30));
                                    @endphp
                                    @if($hasExpired)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Vencido
                                        </span>
                                    @elseif($nearExpiry)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Próximo
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500">
                                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                            OK
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-gray-500">
                                    {{ $firstItem->added_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <form action="{{ route('pre-assembled.remove-product', $preAssembled) }}" 
                                        method="POST" 
                                        class="inline"
                                        onsubmit="return confirm('¿Remover {{ $items->sum('quantity') }} unidad(es) de {{ $product->name }} del paquete?')">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $productId }}">
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 hover:bg-red-50 p-2 rounded transition-colors"
                                                title="Remover">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <i class="fas fa-box-open text-6xl mb-4 text-gray-300"></i>
                        <p class="text-base font-medium text-gray-900 mb-2">El paquete está vacío</p>
                        <p class="text-sm text-gray-600">Comienza escaneando productos arriba</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Acciones finales (sin cambios) -->
            <div class="flex items-center justify-end space-x-3">
                <form action="{{ route('pre-assembled.update-status', $preAssembled) }}" 
                      method="POST">
                    @csrf
                    <select name="status" 
                            onchange="this.form.submit()"
                            class="rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                        <option value="">Cambiar estado...</option>
                        <option value="available">Disponible</option>
                        <option value="in_preparation">En Preparación</option>
                        <option value="in_surgery">En Cirugía</option>
                        <option value="maintenance">Mantenimiento</option>
                    </select>
                </form>
                
                <form action="{{ route('pre-assembled.destroy', $preAssembled) }}" 
                      method="POST" 
                      onsubmit="return confirm('¿Estás seguro de eliminar este paquete? Esta acción no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-1"></i>
                        Eliminar Paquete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- 🆕 MODAL: Escaneo Masivo -->
    <div id="bulk-scan-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-2xl rounded-lg bg-white">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-barcode mr-2 text-indigo-600"></i>
                    Escaneo Masivo de Productos
                </h3>
                <button onclick="closeBulkScanModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <!-- Body -->
            <form id="bulk-scan-form" action="{{ route('pre-assembled.add-product', $preAssembled) }}" method="POST">
                @csrf
                <div class="mt-6 space-y-4">
                    <!-- Lista de productos escaneados -->
                    <div class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-y-auto" id="scanned-products-list">
                        <p class="text-sm text-gray-500 text-center py-8">
                            <i class="fas fa-barcode text-4xl text-gray-300 mb-2"></i><br>
                            Comienza a escanear productos...
                        </p>
                    </div>

                    <!-- Campo de escaneo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Escanea aquí (se agregará automáticamente)
                        </label>
                        <input 
                            type="text" 
                            id="bulk_scan_input" 
                            placeholder="Escanea EPC o código de barras..."
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-lg font-mono"
                            autocomplete="off">
                    </div>

                    <!-- Contador -->
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">
                            <i class="fas fa-box mr-1"></i>
                            Productos escaneados: <strong id="scanned-count">0</strong>
                        </span>
                        <button 
                            type="button"
                            onclick="clearScannedProducts()"
                            class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash mr-1"></i>
                            Limpiar lista
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 flex justify-end space-x-3 pt-4 border-t">
                    <button 
                        type="button"
                        onclick="closeBulkScanModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button 
                        type="submit"
                        class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-1"></i>
                        Agregar Todos (<span id="submit-count">0</span>)
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // ============================================
        // ESCANEO INDIVIDUAL - Detección de tipo
        // ============================================
        document.getElementById('search_input').addEventListener('input', function(e) {
            const value = e.target.value.trim();
            const indicator = document.getElementById('scan-type-indicator');
            
            if (value.length === 0) {
                indicator.innerHTML = '';
                return;
            }
            
            let type = '';
            let color = '';
            let icon = '';
            
            if (value.length === 24 && /^[0-9A-Fa-f]+$/.test(value)) {
                type = 'EPC';
                color = 'text-green-600';
                icon = 'fa-qrcode';
            } else if (value.includes('-') || value.match(/^[A-Z0-9\-]+$/i)) {
                type = 'Código';
                color = 'text-blue-600';
                icon = 'fa-barcode';
            } else {
                type = 'Serial';
                color = 'text-purple-600';
                icon = 'fa-fingerprint';
            }
            
            indicator.innerHTML = `
                <span class="text-xs font-medium ${color}">
                    <i class="fas ${icon} mr-1"></i>
                    ${type}
                </span>
            `;
        });

        // Auto-submit después de escaneo (opcional)
        let scanTimeout;
        document.getElementById('search_input').addEventListener('keypress', function(e) {
            clearTimeout(scanTimeout);
            
            // Si presiona Enter, enviar inmediatamente
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('single-scan-form').submit();
                return;
            }
            
            // Auto-submit después de 100ms de inactividad (para escáneres rápidos)
            scanTimeout = setTimeout(() => {
                if (this.value.trim().length > 0) {
                    // document.getElementById('single-scan-form').submit();
                }
            }, 100);
        });

        // ============================================
        // ESCANEO MASIVO - Modal
        // ============================================
        let scannedProducts = [];

        function openBulkScanModal() {
            document.getElementById('bulk-scan-modal').classList.remove('hidden');
            document.getElementById('bulk_scan_input').focus();
            scannedProducts = [];
            updateScannedList();
        }

        function closeBulkScanModal() {
            document.getElementById('bulk-scan-modal').classList.add('hidden');
            scannedProducts = [];
            updateScannedList();
        }

        // Escaneo en el modal
        document.getElementById('bulk_scan_input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const value = this.value.trim();
                
                if (value && !scannedProducts.includes(value)) {
                    scannedProducts.push(value);
                    updateScannedList();
                    
                    // Efecto visual
                    this.classList.add('ring-2', 'ring-green-500');
                    setTimeout(() => {
                        this.classList.remove('ring-2', 'ring-green-500');
                    }, 200);
                }
                
                this.value = '';
                this.focus();
            }
        });

        function updateScannedList() {
            const list = document.getElementById('scanned-products-list');
            const count = document.getElementById('scanned-count');
            const submitCount = document.getElementById('submit-count');
            
            count.textContent = scannedProducts.length;
            submitCount.textContent = scannedProducts.length;
            
            if (scannedProducts.length === 0) {
                list.innerHTML = `
                    <p class="text-sm text-gray-500 text-center py-8">
                        <i class="fas fa-barcode text-4xl text-gray-300 mb-2"></i><br>
                        Comienza a escanear productos...
                    </p>
                `;
                return;
            }
            
            list.innerHTML = scannedProducts.map((code, index) => `
                <div class="flex items-center justify-between py-2 px-3 bg-white rounded mb-2 border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <span class="text-xs font-semibold text-gray-500">#${index + 1}</span>
                        <code class="text-sm font-mono text-gray-800">${code}</code>
                    </div>
                    <button 
                        type="button"
                        onclick="removeScannedProduct(${index})"
                        class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('');
        }

        function removeScannedProduct(index) {
            scannedProducts.splice(index, 1);
            updateScannedList();
        }

        function clearScannedProducts() {
            if (confirm('¿Limpiar toda la lista?')) {
                scannedProducts = [];
                updateScannedList();
            }
        }

        // Enviar formulario masivo
        document.getElementById('bulk-scan-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (scannedProducts.length === 0) {
                alert('No hay productos para agregar');
                return;
            }
            
            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
            
            // Enviar cada producto
            let success = 0;
            let errors = 0;
            
            for (const code of scannedProducts) {
                try {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('search_input', code);
                    
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (response.ok) {
                        success++;
                    } else {
                        errors++;
                    }
                } catch (error) {
                    errors++;
                }
            }
            
            // Recargar página
            alert(`✅ Productos agregados: ${success}\n❌ Errores: ${errors}`);
            window.location.reload();
        });

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBulkScanModal();
            }
        });
    </script>
    @endpush
    
    @push('scripts')
        @vite('resources/js/pages/pre-assembled/rfid-compare.js')
    @endpush
</x-app-layout>