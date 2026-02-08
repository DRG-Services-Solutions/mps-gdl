
<!-- Modal de Importación Masiva -->
<div x-data="bulkImportModal()" 
    const supplierSelect = document.querySelector('select[name="supplier_id"]');
    const suppliersWithMapping = [2]; // Medartis
    return supplierSelect && suppliersWithMapping.includes(parseInt(supplierSelect.value));
     x-show="isOpen" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @open-bulk-import.window="openModal()">
    
    <!-- Backdrop -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
         @click="closeModal()">
    </div>

    <!-- Modal Content -->
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div x-show="isOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
             @click.away="closeModal()">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Importar Productos desde CSV
                    </h3>
                    <button @click="closeModal()" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="px-6 py-4">
                <!-- Paso 1: Descargar Template -->
                <div class="mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 rounded-full font-bold text-sm">1</span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-sm font-semibold text-gray-900">Descarga el template</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                Elige una opción según tu preferencia:
                            </p>
                            <div class="mt-3 space-y-2">
                                <!-- Template básico -->
                                <a href="{{ route('purchase-orders.bulk-import.template') }}" 
                                   class="flex items-center px-4 py-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div class="text-left">
                                        <span class="block font-semibold">Template Vacío</span>
                                        <span class="block text-xs text-blue-600">Solo columnas, tú agregas los códigos</span>
                                    </div>
                                </a>
                                
                                <!-- Template con catálogo -->
                                <a href="{{ route('purchase-orders.bulk-import.template-catalog') }}" 
                                   class="flex items-center px-4 py-2 bg-green-50 hover:bg-green-100 border border-green-200 text-green-700 text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <div class="text-left">
                                        <span class="block font-semibold">Template con Catálogo</span>
                                        <span class="block text-xs text-green-600">Incluye todos los productos, solo llena cantidades</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paso 2: Subir Archivo -->
                <div class="mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="flex items-center justify-center w-8 h-8 bg-green-100 text-green-600 rounded-full font-bold text-sm">2</span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-sm font-semibold text-gray-900">Sube tu archivo</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                Una vez que hayas llenado el template, súbelo aquí.
                            </p>
                            
                            <!-- Dropzone -->
                                <div class="mt-3">
                                    <!-- Input oculto FUERA del label -->
                                    <input type="file" 
                                        id="csv-file-input"
                                        class="hidden" 
                                        accept=".csv"
                                        @change="handleFileSelect($event)">
                                    
                                    <!-- Label clickeable -->
                                    <div class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                                        :class="isDragging ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-gray-400 bg-gray-50 hover:bg-gray-100'"
                                        @click="$refs.fileInput.click()"
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="handleDrop($event)">
                                        
                                        <!-- Estado: Sin archivo -->
                                        <template x-if="!selectedFile">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                </svg>
                                                <p class="mb-2 text-sm text-gray-500">
                                                    <span class="font-semibold">Haz clic para seleccionar</span> o arrastra el archivo
                                                </p>
                                                <p class="text-xs text-gray-400">Solo archivos .csv (máx. 5MB)</p>
                                            </div>
                                        </template>

                                        <!-- Estado: Archivo seleccionado -->
                                        <template x-if="selectedFile">
                                            <div class="flex items-center justify-center">
                                                <svg class="w-8 h-8 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <div class="text-left">
                                                    <p class="text-sm font-medium text-gray-900" x-text="selectedFile?.name"></p>
                                                    <p class="text-xs text-gray-500" x-text="formatFileSize(selectedFile?.size)"></p>
                                                </div>
                                                <button type="button" 
                                                        @click.stop="clearFile()" 
                                                        class="ml-3 text-red-500 hover:text-red-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <!-- Input con x-ref para acceso directo -->
                                    <input type="file" 
                                        x-ref="fileInput"
                                        class="hidden" 
                                        accept=".csv"
                                        @change="handleFileSelect($event)">
                                </div>
                            
                            <!-- Nota sobre Excel -->
                            <p class="mt-2 text-xs text-gray-500">
                                <svg class="w-4 h-4 inline mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <strong>Tip:</strong> Puedes abrir el CSV en Excel, editarlo y guardarlo como CSV.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Mensajes de Error -->
                <div x-show="errorMessage" x-cloak class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-red-800" x-text="errorMessage"></p>
                            <ul x-show="importErrors.length > 0" class="mt-2 text-sm text-red-700 list-disc list-inside max-h-32 overflow-y-auto">
                                <template x-for="error in importErrors.slice(0, 10)" :key="error">
                                    <li x-text="error"></li>
                                </template>
                                <li x-show="importErrors.length > 10" class="text-red-500 font-medium">
                                    ... y <span x-text="importErrors.length - 10"></span> errores más
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Importación -->
                <div x-show="importSummary" x-cloak class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-green-800">Importación completada</p>
                            <p class="text-sm text-green-700 mt-1">
                                <span x-text="importSummary?.imported"></span> productos importados
                                <span x-show="importSummary?.failed > 0" class="text-orange-600">
                                    (<span x-text="importSummary?.failed"></span> con errores)
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Loading -->
                <div x-show="isLoading" x-cloak class="flex items-center justify-center py-4">
                    <svg class="animate-spin h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-3 text-gray-600">Procesando archivo...</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                <button type="button"
                        @click="closeModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="button"
                        @click="processFile()"
                        :disabled="!selectedFile || isLoading"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors">
                    <span x-show="!isLoading">Importar Productos</span>
                    <span x-show="isLoading">Procesando...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function bulkImportModal() {
    return {
        isOpen: false,
        isDragging: false,
        isLoading: false,
        selectedFile: null,
        errorMessage: '',
        importErrors: [],
        importSummary: null,

        openModal() {
            this.isOpen = true;
            this.resetState();
        },

        closeModal() {
            this.isOpen = false;
            this.resetState();
        },

        resetState() {
            this.selectedFile = null;
            this.errorMessage = '';
            this.importErrors = [];
            this.importSummary = null;
            this.isDragging = false;
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            this.validateAndSetFile(file);
        },

        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            this.validateAndSetFile(file);
        },

        validateAndSetFile(file) {
            this.errorMessage = '';
            this.importErrors = [];
            this.importSummary = null;

            if (!file) return;

            // Validar tipo
            if (!file.name.match(/\.csv$/i)) {
                this.errorMessage = 'Solo se permiten archivos CSV (.csv)';
                return;
            }

            // Validar tamaño (5MB)
            if (file.size > 5 * 1024 * 1024) {
                this.errorMessage = 'El archivo no debe superar los 5MB';
                return;
            }

            this.selectedFile = file;
        },

        clearFile() {
            this.selectedFile = null;
            this.errorMessage = '';
            this.importErrors = [];
            this.importSummary = null;
        },

        formatFileSize(bytes) {
            if (!bytes) return '';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },

        async processFile() {
            if (!this.selectedFile) return;

            this.isLoading = true;
            this.errorMessage = '';
            this.importErrors = [];
            this.importSummary = null;

            const formData = new FormData();
            formData.append('file', this.selectedFile);

            const supplierSelect = document.querySelector('select[name="supplier_id"]');
            if (supplierSelect && supplierSelect.value) {
                formData.append('supplier_id', supplierSelect.value);
            }

            try {
                const response = await fetch('{{ route("purchase-orders.bulk-import.process") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    this.errorMessage = data.message || 'Error al procesar el archivo';
                    this.importErrors = data.errors || [];
                    return;
                }

                // Éxito
                this.importSummary = data.summary;
                
                if (data.errors && data.errors.length > 0) {
                    this.importErrors = data.errors;
                }

                // Emitir evento para que el formulario principal reciba los productos
                window.dispatchEvent(new CustomEvent('bulk-import-complete', {
                    detail: { items: data.items }
                }));

                // Cerrar modal después de un momento si todo fue bien
                if (data.items.length > 0) {
                    setTimeout(() => {
                        this.closeModal();
                    }, 1500);
                }

            } catch (error) {
                console.error('Error:', error);
                this.errorMessage = 'Error de conexión. Intenta de nuevo.';
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
