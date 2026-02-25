<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="text-sm font-medium">Volver a productos</span>
                </a>
                <div class="border-l border-gray-300 pl-4">
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        <i class="fas fa-file-upload mr-2 text-indigo-600"></i>
                        Importación Masiva de Productos
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Cargue productos desde Excel para agregarlos al catálogo
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Alertas --}}
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-400 mr-3 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                            @if (session('import_errors') && count(session('import_errors')) > 0)
                                <details class="mt-2">
                                    <summary class="text-xs text-green-600 cursor-pointer hover:text-green-800">
                                        Ver detalles de filas omitidas ({{ count(session('import_errors')) }})
                                    </summary>
                                    <ul class="mt-2 text-xs text-green-700 list-disc list-inside space-y-1">
                                        @foreach (session('import_errors') as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-0.5"></i>
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-0.5"></i>
                        <div>
                            <h3 class="text-sm font-semibold text-red-800">Errores de validación:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Paso 1: Descargar Template --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                            1
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Descargue el Template</h3>
                            <p class="text-sm text-gray-600">Archivo Excel con formato correcto y ejemplos</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-excel text-6xl text-green-600"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900 mb-2">Template de Importación</h4>
                            <p class="text-sm text-gray-600 mb-4">
                                Descargue el template, llénelo con sus productos y súbalo en el siguiente paso.
                                Incluye una hoja de instrucciones detalladas.
                            </p>
                            <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600 mb-4">
                                <span><i class="fas fa-check text-green-600 mr-1"></i>Columnas pre-configuradas</span>
                                <span><i class="fas fa-check text-green-600 mr-1"></i>Ejemplos incluidos</span>
                                <span><i class="fas fa-check text-green-600 mr-1"></i>Instrucciones detalladas</span>
                            </div>
                            <a href="{{ route('products.import.template') }}"
                                class="inline-flex items-center px-5 py-2.5 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-green-700 transition-all duration-200">
                                <i class="fas fa-download mr-2"></i>
                                Descargar Template Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 2: Subir archivo --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-600 flex items-center justify-center text-white font-bold text-sm">
                            2
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Suba el Archivo</h3>
                            <p class="text-sm text-gray-600">Se validarán los datos antes de importar</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('products.import.preview') }}" method="POST" enctype="multipart/form-data"
                        id="importForm">
                        @csrf

                        {{-- Drag & Drop Zone --}}
                        <div id="dropZone"
                            class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-400 transition-colors duration-200 cursor-pointer mb-4"
                            onclick="document.getElementById('fileInput').click()">

                            {{-- Estado: Sin archivo --}}
                            <div id="dropEmpty">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                <p class="text-sm font-medium text-gray-700 mb-1">
                                    Arrastre su archivo aquí o haga clic para seleccionar
                                </p>
                                <p class="text-xs text-gray-500">
                                    Formatos aceptados: .xlsx, .xls, .csv &middot; Máximo 10MB
                                </p>
                            </div>

                            {{-- Estado: Archivo seleccionado --}}
                            <div id="dropSelected" class="hidden">
                                <i class="fas fa-file-excel text-4xl text-green-600 mb-3"></i>
                                <p class="text-sm font-medium text-gray-900 mb-1" id="fileName"></p>
                                <p class="text-xs text-gray-500" id="fileSize"></p>
                                <button type="button" onclick="event.stopPropagation(); clearFile()"
                                    class="mt-2 text-xs text-red-600 hover:text-red-800 font-medium">
                                    <i class="fas fa-times mr-1"></i>Quitar archivo
                                </button>
                            </div>

                            <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" required
                                class="hidden" onchange="handleFileSelect(this)">
                        </div>

                        {{-- Info --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-5">
                            <div class="flex">
                                <i class="fas fa-info-circle text-blue-500 mr-3 mt-0.5"></i>
                                <div class="text-sm text-blue-700">
                                    <p class="font-medium mb-1">¿Qué sucederá?</p>
                                    <ul class="space-y-0.5 text-blue-600">
                                        <li><i class="fas fa-search text-xs mr-1.5"></i>Se validará cada fila del archivo</li>
                                        <li><i class="fas fa-eye text-xs mr-1.5"></i>Verá un resumen antes de confirmar la importación</li>
                                        <li><i class="fas fa-shield-alt text-xs mr-1.5"></i>Nada se guarda hasta que usted confirme</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Botón submit --}}
                        <button type="submit" id="btnSubmit" disabled
                            class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm transition-all duration-200 bg-gray-400 cursor-not-allowed"
                            >
                            <i class="fas fa-arrow-right mr-2"></i>
                            <span id="btnText">Validar y Continuar</span>
                            <span id="btnLoading" class="hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Procesando archivo...
                            </span>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            const btnSubmit = document.getElementById('btnSubmit');

            // Drag & Drop
            ['dragenter', 'dragover'].forEach(evt => {
                dropZone.addEventListener(evt, e => {
                    e.preventDefault();
                    dropZone.classList.add('border-indigo-400', 'bg-indigo-50');
                });
            });

            ['dragleave', 'drop'].forEach(evt => {
                dropZone.addEventListener(evt, e => {
                    e.preventDefault();
                    dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
                });
            });

            dropZone.addEventListener('drop', e => {
                const files = e.dataTransfer.files;
                if (files.length) {
                    fileInput.files = files;
                    handleFileSelect(fileInput);
                }
            });

            function handleFileSelect(input) {
                const file = input.files[0];
                if (!file) return;

                const validExts = ['.xlsx', '.xls', '.csv'];
                const ext = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();

                if (!validExts.includes(ext)) {
                    alert('Formato no válido. Use archivos .xlsx, .xls o .csv');
                    clearFile();
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    alert('El archivo excede el límite de 10MB');
                    clearFile();
                    return;
                }

                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = formatSize(file.size);
                document.getElementById('dropEmpty').classList.add('hidden');
                document.getElementById('dropSelected').classList.remove('hidden');

                btnSubmit.disabled = false;
                btnSubmit.classList.remove('bg-gray-400', 'cursor-not-allowed');
                btnSubmit.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            }

            function clearFile() {
                fileInput.value = '';
                document.getElementById('dropEmpty').classList.remove('hidden');
                document.getElementById('dropSelected').classList.add('hidden');

                btnSubmit.disabled = true;
                btnSubmit.classList.add('bg-gray-400', 'cursor-not-allowed');
                btnSubmit.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            }

            function formatSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / 1048576).toFixed(1) + ' MB';
            }

            // Loading state al enviar
            document.getElementById('importForm').addEventListener('submit', function () {
                btnSubmit.disabled = true;
                btnSubmit.classList.add('bg-gray-400', 'cursor-not-allowed');
                btnSubmit.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                document.getElementById('btnText').classList.add('hidden');
                document.getElementById('btnLoading').classList.remove('hidden');
            });
        </script>
    @endpush
</x-app-layout>