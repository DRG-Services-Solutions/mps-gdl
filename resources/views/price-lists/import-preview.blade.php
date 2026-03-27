{{-- resources/views/price-lists/import-preview.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-search mr-2 text-indigo-600"></i>
                    Vista Previa de Importación
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-indigo-600">{{ $priceList->code }}</span>
                    <span class="mx-2">|</span> {{ $priceList->name }}
                </p>
            </div>
            <a href="{{ route('price-lists.import', $priceList) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Subir otro archivo
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Resumen -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Total en archivo</p>
                    <p class="text-3xl font-black text-gray-900">{{ $result['total'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <p class="text-xs font-bold text-green-600 uppercase mb-1">Encontrados</p>
                    <p class="text-3xl font-black text-green-600">{{ count($result['found']) }}</p>
                    <p class="text-xs text-green-600 mt-1">Listos para importar</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                    <p class="text-xs font-bold text-yellow-600 uppercase mb-1">No encontrados</p>
                    <p class="text-3xl font-black text-yellow-600">{{ count($result['not_found']) }}</p>
                    <p class="text-xs text-yellow-600 mt-1">Requieren decisión</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                    <p class="text-xs font-bold text-red-600 uppercase mb-1">Errores</p>
                    <p class="text-3xl font-black text-red-600">{{ count($result['errors']) }}</p>
                    <p class="text-xs text-red-600 mt-1">Filas con problemas</p>
                </div>
            </div>

            <form action="{{ route('price-lists.import.execute', $priceList) }}" method="POST">
                @csrf

                <!-- Productos Encontrados -->
                @if(count($result['found']) > 0)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                        <h3 class="text-lg font-semibold text-green-900 flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-600"></i>
                            Productos Encontrados ({{ count($result['found']) }})
                        </h3>
                        <p class="text-sm text-green-700 mt-1">Estos productos se encontraron en tu catálogo y se importarán con el precio indicado.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase w-16">Incluir</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fila</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($result['found'] as $index => $item)
                                <tr class="hover:bg-green-50 transition-colors">
                                    <td class="px-6 py-3 text-center">
                                        <input type="checkbox" name="found_items[{{ $index }}]" value="1" checked
                                               class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $item['row'] }}</td>
                                    <td class="px-6 py-3 text-sm font-mono font-bold text-gray-900">{{ $item['product_code'] }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ $item['product_name'] }}</td>
                                    <td class="px-6 py-3 text-sm font-bold text-gray-900 text-right">${{ number_format($item['unit_price'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Productos NO Encontrados -->
                @if(count($result['not_found']) > 0)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden" x-data="{ showAll: true }">
                    <div class="px-6 py-4 border-b border-gray-200 bg-yellow-50">
                        <h3 class="text-lg font-semibold text-yellow-900 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2 text-yellow-600"></i>
                            Productos No Encontrados ({{ count($result['not_found']) }})
                        </h3>
                        <p class="text-sm text-yellow-700 mt-1">
                            Estos códigos no existen en tu catálogo. Puedes crear cada producto o descartarlo.
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fila</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre del Producto (si se crea)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($result['not_found'] as $index => $item)
                                <tr class="hover:bg-yellow-50 transition-colors"
                                    x-data="{ createThis: false }">
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $item['row'] }}</td>
                                    <td class="px-6 py-3 text-sm font-mono font-bold text-yellow-700">{{ $item['code'] }}</td>
                                    <td class="px-6 py-3 text-sm font-bold text-gray-900 text-right">${{ number_format($item['unit_price'], 2) }}</td>
                                    <td class="px-6 py-3">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" x-model="createThis"
                                                   class="h-4 w-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
                                            <span class="ml-2 text-sm font-medium" :class="createThis ? 'text-yellow-700' : 'text-gray-400'">
                                                <span x-show="createThis"><i class="fas fa-plus-circle mr-1"></i> Crear producto</span>
                                                <span x-show="!createThis"><i class="fas fa-times mr-1"></i> Descartar</span>
                                            </span>
                                        </label>
                                    </td>
                                    <td class="px-6 py-3">
                                        <template x-if="createThis">
                                            <div>
                                                <input type="hidden" name="create_items[{{ $index }}][code]" value="{{ $item['code'] }}">
                                                <input type="hidden" name="create_items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] }}">
                                                <input type="hidden" name="create_items[{{ $index }}][notes]" value="{{ $item['notes'] ?? '' }}">
                                                <input type="text" name="create_items[{{ $index }}][name]" required
                                                       placeholder="Nombre del producto..."
                                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-yellow-500 focus:ring-yellow-500">
                                            </div>
                                        </template>
                                        <span x-show="!createThis" class="text-xs text-gray-400 italic">Se descartará</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Errores -->
                @if(count($result['errors']) > 0)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                        <h3 class="text-lg font-semibold text-red-900 flex items-center">
                            <i class="fas fa-times-circle mr-2 text-red-600"></i>
                            Errores ({{ count($result['errors']) }})
                        </h3>
                        <p class="text-sm text-red-700 mt-1">Estas filas tienen errores y serán ignoradas.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fila</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contenido</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($result['errors'] as $error)
                                <tr class="bg-red-50">
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $error['row'] }}</td>
                                    <td class="px-6 py-3 text-sm text-red-700">{{ $error['message'] }}</td>
                                    <td class="px-6 py-3 text-xs font-mono text-gray-500">{{ $error['raw'] ?? '' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Botones de acción -->
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-info-circle text-indigo-600 mr-1"></i>
                        Los productos que ya existen en la lista se actualizarán con el nuevo precio.
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('price-lists.import', $priceList) }}"
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                        <button type="submit"
                                class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors shadow-md hover:shadow-lg">
                            <i class="fas fa-check mr-1"></i> Confirmar Importación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
