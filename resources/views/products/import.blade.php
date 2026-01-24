<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
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
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-400 mr-3 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                            @if(session('import_errors'))
                                <details class="mt-2">
                                    <summary class="text-xs text-green-600 cursor-pointer hover:text-green-800">
                                        Ver detalles de filas omitidas
                                    </summary>
                                    <ul class="mt-2 text-xs text-green-700 list-disc list-inside space-y-1">
                                        @foreach(session('import_errors') as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
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
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold">
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
                                Descargue el template con el formato correcto, columnas necesarias y ejemplos de datos. 
                                Incluye una hoja de instrucciones detalladas.
                            </p>
                            <ul class="text-sm text-gray-600 space-y-1 mb-4">
                                <li><i class="fas fa-check text-green-600 mr-2"></i>Columnas pre-configuradas</li>
                                <li><i class="fas fa-check text-green-600 mr-2"></i>Ejemplos de productos</li>
                                <li><i class="fas fa-check text-green-600 mr-2"></i>Instrucciones incluidas</li>
                                <li><i class="fas fa-check text-green-600 mr-2"></i>Formato validado</li>
                            </ul>
                            <a href="{{ route('products.import.template') }}" 
                               class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-green-700 transition-all duration-200">
                                <i class="fas fa-download mr-2"></i>
                                Descargar Template Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 2: Vista Previa (Opcional) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-yellow-600 flex items-center justify-center text-white font-bold">
                            2
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Vista Previa (Opcional)</h3>
                            <p class="text-sm text-gray-600">Valide el archivo antes de importar</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <i class="fas fa-info-circle text-yellow-400 mr-3 mt-0.5"></i>
                            <div>
                                <p class="text-sm text-yellow-700">
                                    <strong>Recomendado:</strong> Use la vista previa para verificar que todos los datos sean correctos antes de importar.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('products.import.preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-file-upload mr-1"></i>
                                Seleccione archivo Excel
                            </label>
                            <input type="file" 
                                   name="file" 
                                   accept=".xlsx,.xls,.csv"
                                   required
                                   class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-yellow-500">
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Formatos aceptados: .xlsx, .xls, .csv (máximo 10MB)
                            </p>
                        </div>
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-yellow-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-yellow-700 transition-all duration-200">
                            <i class="fas fa-eye mr-2"></i>
                            Ver Vista Previa
                        </button>
                    </form>
                </div>
            </div>

            {{-- Paso 3: Importar Directamente --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-600 flex items-center justify-center text-white font-bold">
                            3
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Importar Productos</h3>
                            <p class="text-sm text-gray-600">Guardar productos en el catálogo</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                        <div class="flex">
                            <i class="fas fa-lightbulb text-blue-400 mr-3 mt-0.5"></i>
                            <div>
                                <p class="text-sm text-blue-700 font-medium mb-2">Importante:</p>
                                <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                                    <li>Los proveedores y marcas se crearán automáticamente si no existen</li>
                                    <li>Los códigos duplicados serán omitidos</li>
                                    <li>Las filas con errores no se importarán</li>
                                    <li>Recibirá un reporte detallado al finalizar</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('products.import') }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          onsubmit="return confirm('¿Está seguro de importar los productos? Esta acción guardará los datos en la base de datos.')">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-file-upload mr-1"></i>
                                Seleccione archivo Excel
                            </label>
                            <input type="file" 
                                   name="file" 
                                   accept=".xlsx,.xls,.csv"
                                   required
                                   class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-green-500">
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Formatos aceptados: .xlsx, .xls, .csv (máximo 10MB)
                            </p>
                        </div>
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-green-700 transition-all duration-200">
                            <i class="fas fa-upload mr-2"></i>
                            Importar Productos Ahora
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>