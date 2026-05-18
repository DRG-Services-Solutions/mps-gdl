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
                            <p id="stat-total-items" class="text-2xl font-bold text-gray-900 mt-2">{{ $preAssembled->contents->count() }}</p>
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
                            <p id="stat-completeness" class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($preAssembled->getCompletenessPercentage(), 1) }}%</p>
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

            


            <!-- Contenido del Paquete -->
            <div id="contents-table-container" class="bg-white rounded-lg shadow-sm overflow-hidden">
                @include('pre-assembled.partials.contents-table')
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

        let isSubmitting = false;

        document.getElementById('single-scan-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (isSubmitting) return;
            isSubmitting = true;

            const inputField = document.getElementById('search_input');
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Agregando...';

            try {
                const formData = new FormData(this);
                
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Update table
                    document.getElementById('contents-table-container').innerHTML = data.html;
                    
                    // Update top stats if they exist in the response
                    if (data.stats) {
                        if (document.getElementById('stat-total-items')) {
                            document.getElementById('stat-total-items').textContent = data.stats.total_items;
                        }
                        if (document.getElementById('stat-completeness')) {
                            document.getElementById('stat-completeness').textContent = data.stats.completeness + '%';
                        }
                    } else {
                        // Fallback: extract count from the updated table header
                        const countEl = document.getElementById('total-items-count');
                        if (countEl && document.getElementById('stat-total-items')) {
                            document.getElementById('stat-total-items').textContent = countEl.textContent;
                        }
                        // We can't easily recalculate completeness without backend data, so we'll let it be for now or force a reload if they care deeply about that specific stat updating live, but this fallback is better than nothing.
                    }
                    
                    // Show success toast or alert
                    showToast(data.message, 'success');
                    
                    // Clear input
                    inputField.value = '';
                    document.getElementById('scan-type-indicator').innerHTML = '';
                    
                    // Re-bind remove forms
                    bindRemoveForms();
                } else {
                    // Show error
                    showToast(data.message || 'Error al agregar producto', 'error');
                }
            } catch (error) {
                console.error(error);
                showToast('Error de conexión', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                inputField.focus();
                isSubmitting = false;
            }
        });

        // Binding for dynamic remove forms
        function bindRemoveForms() {
            document.querySelectorAll('.remove-product-form').forEach(form => {
                // Prevent duplicate bindings
                if (form.dataset.bound) return;
                form.dataset.bound = 'true';
                
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    if (!confirm('¿Remover este producto del paquete?')) {
                        return;
                    }

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalContent = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    try {
                        const formData = new FormData(this);
                        const response = await fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok && data.success) {
                            document.getElementById('contents-table-container').innerHTML = data.html;
                            
                            // Update top stats if they exist in the response
                            if (data.stats) {
                                if (document.getElementById('stat-total-items')) {
                                    document.getElementById('stat-total-items').textContent = data.stats.total_items;
                                }
                                if (document.getElementById('stat-completeness')) {
                                    document.getElementById('stat-completeness').textContent = data.stats.completeness + '%';
                                }
                            } else {
                                // Fallback: extract count from the updated table header
                                const countEl = document.getElementById('total-items-count');
                                if (countEl && document.getElementById('stat-total-items')) {
                                    document.getElementById('stat-total-items').textContent = countEl.textContent;
                                }
                            }

                            showToast(data.message, 'success');
                            bindRemoveForms();
                        } else {
                            showToast(data.message || 'Error al remover producto', 'error');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalContent;
                        }
                    } catch (error) {
                        console.error(error);
                        showToast('Error de conexión', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalContent;
                    }
                });
            });
        }

        // Initialize bindings on load
        bindRemoveForms();

        // Simple toast notification system
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center z-50 transition-opacity duration-300`;
            toast.innerHTML = `<i class="fas ${icon} mr-3"></i> <span>${message}</span>`;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

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