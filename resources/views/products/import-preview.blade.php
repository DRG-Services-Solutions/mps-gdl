<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.import.form') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="text-sm font-medium">Volver a importación</span>
                </a>
                <div class="border-l border-gray-300 pl-4">
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        <i class="fas fa-eye mr-2 text-indigo-600"></i>
                        Vista Previa de Importación
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Revise los datos antes de guardar
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Resumen --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Filas Válidas</p>
                            <p class="text-3xl font-bold text-gray-900">{{ count($validRows) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                            <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Filas con Errores</p>
                            <p class="text-3xl font-bold text-gray-900">{{ count($invalidRows) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <i class="fas fa-file-alt text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Total Filas</p>
                            <p class="text-3xl font-bold text-gray-900">{{ count($validRows) + count($invalidRows) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filas Válidas --}}
            @if(count($validRows) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-green-50 px-6 py-4 border-b border-green-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            Productos Válidos ({{ count($validRows) }})
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Estos productos se importarán correctamente
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fila</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tracking</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marca</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($validRows as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $row['row'] }}</td>
                                        <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $row['processed']['code'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['processed']['name'] }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($row['processed']['tracking_type'] === 'rfid') bg-blue-100 text-blue-800
                                                @elseif($row['processed']['tracking_type'] === 'serial') bg-purple-100 text-purple-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ strtoupper($row['processed']['tracking_type']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($row['relations']['product_type_name'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    {{ $row['relations']['product_type_name'] === 'Consumible' ? 'bg-orange-100 text-orange-800' : 'bg-teal-100 text-teal-800' }}">
                                                    {{ $row['relations']['product_type_name'] }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $row['relations']['category_name'] ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $row['relations']['supplier_name'] ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $row['relations']['brand_name'] ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">
                                            ${{ number_format($row['processed']['list_price'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Filas con Errores --}}
            @if(count($invalidRows) > 0)
                {{-- Panel de Debug: Mostrar mapeo de columnas --}}
                @if(session('import_debug_headers'))
                    <div class="bg-yellow-50 rounded-xl shadow-sm border border-yellow-200 overflow-hidden mb-6">
                        <div class="bg-yellow-100 px-6 py-3 border-b border-yellow-200">
                            <button type="button" 
                                    onclick="document.getElementById('debug-panel').classList.toggle('hidden')"
                                    class="flex items-center text-sm font-semibold text-gray-900 cursor-pointer hover:text-yellow-800">
                                <i class="fas fa-bug text-yellow-600 mr-2"></i>
                                <span>🔍 Debug: Columnas Detectadas (click para ver)</span>
                            </button>
                        </div>
                        <div id="debug-panel" class="hidden px-6 py-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Encabezados en tu Excel:</h4>
                                    <ul class="text-xs text-gray-600 space-y-1">
                                        @foreach(session('import_debug_headers') as $index => $header)
                                            <li><strong>Columna {{ $index }}:</strong> "{{ $header }}"</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Columnas Reconocidas:</h4>
                                    <ul class="text-xs text-gray-600 space-y-1">
                                        @php
                                            $mapping = session('import_debug_mapping', []);
                                            $fields = ['code', 'name', 'tracking_type', 'supplier_name', 'category_name', 'brand_name', 'list_price'];
                                        @endphp
                                        @foreach($fields as $field)
                                            <li>
                                                <strong>{{ $field }}:</strong> 
                                                @if(isset($mapping[$field]))
                                                    <span class="text-green-600">✓ Columna {{ $mapping[$field] }}</span>
                                                @else
                                                    <span class="text-red-600">✗ No detectada</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-blue-50 rounded border border-blue-200">
                                <p class="text-xs text-blue-700">
                                    <strong>💡 Tip:</strong> Si alguna columna no se detectó, verifica que el nombre del encabezado sea similar a: 
                                    <code class="bg-white px-1">code</code>, 
                                    <code class="bg-white px-1">name</code>, 
                                    <code class="bg-white px-1">tracking_type</code>, 
                                    <code class="bg-white px-1">category_name</code>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden mb-6">
                    <div class="bg-red-50 px-6 py-4 border-b border-red-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                            Productos con Errores ({{ count($invalidRows) }})
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Estas filas no se importarán. Corrija los errores y vuelva a intentar.
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fila</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Errores</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invalidRows as $row)
                                    <tr class="hover:bg-red-50">
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $row['row'] }}</td>
                                        <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $row['data']['code'] ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['data']['name'] ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">
                                            <ul class="text-xs text-red-600 list-disc list-inside space-y-1">
                                                @foreach($row['errors'] as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Botones de Acción --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('products.import.form') }}" 
                   class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>

                @if(count($validRows) > 0)
                    <div class="text-right">
                        <p class="text-sm text-gray-600 mb-2">
                            Se importarán {{ count($validRows) }} producto(s)
                        </p>
                        <p class="text-xs text-gray-500 mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Los proveedores y marcas se crearán automáticamente si no existen
                        </p>
                        <form action="{{ route('products.import.confirm') }}" method="POST" class="inline" onsubmit="return confirm('¿Confirmar importación de {{ count($validRows) }} productos?')">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-green-700 transition-all duration-200">
                                <i class="fas fa-check mr-2"></i>
                                Confirmar e Importar
                            </button>
                        </form>
                    </div>
                @else
                    <div class="text-right">
                        <p class="text-sm text-red-600 font-medium">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            No hay productos válidos para importar
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Corrija los errores y vuelva a cargar el archivo
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>