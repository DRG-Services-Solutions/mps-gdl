<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-file-upload mr-2 text-indigo-600"></i>
                    {{ __('Importar Check Lists') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Carga masiva de check lists quirúrgicos desde Excel</p>
            </div>
            <a href="{{ route('checklists.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Mensajes Flash --}}
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle text-red-600 mt-0.5 mr-3"></i>
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-600 mt-0.5 mr-3"></i>
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- Paso 1: Descargar Plantilla --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    <span class="inline-flex items-center justify-center w-7 h-7 bg-indigo-100 text-indigo-700 rounded-full text-sm font-bold mr-2">1</span>
                    Descarga la plantilla
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Usa esta plantilla Excel para llenar los datos de tus check lists. Incluye instrucciones y datos de ejemplo.
                </p>
                <a href="{{ route('checklists.import.template') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200">
                    <i class="fas fa-download mr-2"></i>
                    Descargar Plantilla Excel
                </a>
            </div>

            {{-- Paso 2: Subir Archivo --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    <span class="inline-flex items-center justify-center w-7 h-7 bg-indigo-100 text-indigo-700 rounded-full text-sm font-bold mr-2">2</span>
                    Sube tu archivo
                </h3>

                <form action="{{ route('checklists.import.preview') }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      id="importForm">
                    @csrf

                    {{-- Drop Zone --}}
                    <div id="dropZone" 
                         class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-400 transition-colors cursor-pointer"
                         onclick="document.getElementById('fileInput').click()">
                        
                        <div id="dropContent">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-600 font-medium">Arrastra tu archivo aquí o haz clic para seleccionar</p>
                            <p class="text-sm text-gray-400 mt-1">Solo archivos .xlsx o .xls (máx. 5MB)</p>
                        </div>

                        <div id="fileInfo" class="hidden">
                            <i class="fas fa-file-excel text-4xl text-green-500 mb-3"></i>
                            <p class="text-gray-800 font-medium" id="fileName"></p>
                            <p class="text-sm text-gray-500 mt-1" id="fileSize"></p>
                        </div>

                        <input type="file" 
                               name="file" 
                               id="fileInput" 
                               accept=".xlsx,.xls"
                               class="hidden"
                               required>
                    </div>

                    @error('file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-4 flex justify-end">
                        <button type="submit" 
                                id="submitBtn"
                                disabled
                                class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-lg shadow-md transition-all duration-200">
                            <i class="fas fa-search mr-2"></i>
                            Validar y Previsualizar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Formato Esperado --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                <h4 class="text-sm font-semibold text-blue-900 mb-3">
                    <i class="fas fa-info-circle mr-1"></i> Formato del Excel
                </h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="text-blue-800">
                                <th class="px-2 py-1 text-left font-semibold">checklist_code</th>
                                <th class="px-2 py-1 text-left font-semibold">surgery_type</th>
                                <th class="px-2 py-1 text-left font-semibold">product_sku</th>
                                <th class="px-2 py-1 text-left font-semibold">quantity</th>
                                <th class="px-2 py-1 text-left font-semibold">is_mandatory</th>
                                <th class="px-2 py-1 text-left font-semibold">notes</th>
                            </tr>
                        </thead>
                        <tbody class="text-blue-700">
                            <tr>
                                <td class="px-2 py-1">CHK-ORTO-001</td>
                                <td class="px-2 py-1">Ortopedia</td>
                                <td class="px-2 py-1">PROD-001</td>
                                <td class="px-2 py-1">2</td>
                                <td class="px-2 py-1">Sí</td>
                                <td class="px-2 py-1">Nota...</td>
                            </tr>
                            <tr class="bg-blue-100/50">
                                <td class="px-2 py-1">CHK-ORTO-001</td>
                                <td class="px-2 py-1">Ortopedia</td>
                                <td class="px-2 py-1">PROD-002</td>
                                <td class="px-2 py-1">5</td>
                                <td class="px-2 py-1">No</td>
                                <td class="px-2 py-1"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-blue-600 mt-2">
                    Las filas con el mismo <strong>checklist_code</strong> se agrupan automáticamente en un solo check list.
                </p>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const submitBtn = document.getElementById('submitBtn');
        const dropContent = document.getElementById('dropContent');
        const fileInfo = document.getElementById('fileInfo');
        const fileNameEl = document.getElementById('fileName');
        const fileSizeEl = document.getElementById('fileSize');

        function showFile(file) {
            dropContent.classList.add('hidden');
            fileInfo.classList.remove('hidden');
            fileNameEl.textContent = file.name;
            fileSizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
            submitBtn.disabled = false;
            dropZone.classList.remove('border-gray-300');
            dropZone.classList.add('border-green-400', 'bg-green-50');
        }

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) showFile(e.target.files[0]);
        });

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-indigo-400', 'bg-indigo-50');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                showFile(files[0]);
            }
        });

        document.getElementById('importForm').addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';
        });
    </script>
    @endpush
</x-app-layout>
