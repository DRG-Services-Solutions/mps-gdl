{{-- resources/views/price-lists/import.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-file-csv mr-2 text-green-600"></i>
                    Importar Productos a Lista de Precios
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-indigo-600">{{ $priceList->code }}</span>
                    <span class="mx-2">|</span> {{ $priceList->name }}
                </p>
            </div>
            <a href="{{ route('price-lists.show', $priceList) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Instrucciones -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-info-circle mr-2 text-indigo-600"></i> Formato Esperado del CSV
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    El archivo debe ser CSV separado por comas con mínimo 2 columnas. La tercera columna (notas) es opcional.
                </p>

                <div class="bg-gray-50 rounded-lg p-4 font-mono text-sm border border-gray-200 overflow-x-auto">
                    <p class="text-gray-500 mb-2">codigo,precio,notas</p>
                    <p>0-102,350.00,Bota quirúrgica</p>
                    <p>20541,125.50,</p>
                    <p>43,890.00,Inmovilizador</p>
                    <p>7210559,45.00,Mascarilla</p>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                        <span class="text-gray-600">El header es opcional, se detecta automáticamente</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                        <span class="text-gray-600">Precios con o sin signo <code class="bg-gray-100 px-1 rounded">$</code></span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                        <span class="text-gray-600">Productos duplicados actualizan el precio</span>
                    </div>
                </div>
            </div>

            <!-- Formulario de subida -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form action="{{ route('price-lists.import.preview', $priceList) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-400 transition-colors">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <p class="text-sm font-medium text-gray-700 mb-2">Selecciona tu archivo CSV</p>
                            <p class="text-xs text-gray-500 mb-4">Tamaño máximo: 5 MB</p>
                            <input type="file" name="csv_file" accept=".csv,.txt" required
                                   class="block mx-auto text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('csv_file')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('price-lists.show', $priceList) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-upload mr-1"></i> Subir y Previsualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
