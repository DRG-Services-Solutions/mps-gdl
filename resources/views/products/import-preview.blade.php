<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.import.form') }}"
                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="text-sm font-medium">Volver</span>
                </a>
                <div class="border-l border-gray-300 pl-4">
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        <i class="fas fa-clipboard-check mr-2 text-indigo-600"></i>
                        Revisión de Importación
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Verifique los datos antes de confirmar
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Barra de acciones --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        @if (count($validRows) > 0)
                            <i class="fas fa-info-circle text-indigo-500 mr-1"></i>
                            Se importarán <strong>{{ count($validRows) }}</strong> producto(s).
                            @if (count($invalidRows) > 0)
                                <span class="text-red-600">{{ count($invalidRows) }} fila(s) con errores serán omitidas.</span>
                            @endif
                        @else
                            <i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>
                            No hay productos válidos para importar. Corrija los errores y suba el archivo nuevamente.
                        @endif
                    </div>

                    <div class="flex items-center space-x-3">
                        <a href="{{ route('products.import.form') }}"
                            class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg font-medium text-sm text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                            <i class="fas fa-redo mr-2"></i>
                            Subir otro archivo
                        </a>

                        @if (count($validRows) > 0)
                            <form action="{{ route('products.import.confirm') }}" method="POST" id="confirmForm">
                                @csrf
                                <button type="submit" id="btnConfirm"
                                    class="inline-flex items-center px-6 py-2.5 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-green-700 transition-all duration-200">
                                    <i class="fas fa-check mr-2"></i>
                                    <span id="confirmText">Confirmar Importación ({{ count($validRows) }})</span>
                                    <span id="confirmLoading" class="hidden">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                        Importando...
                                    </span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <br>

            {{-- Resumen --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                {{-- Total --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <i class="fas fa-file-alt text-indigo-600 text-lg"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total de filas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ count($validRows) + count($invalidRows) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Válidos --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-lg"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Listos para importar</p>
                            <p class="text-2xl font-bold text-green-600">{{ count($validRows) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Con errores --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg {{ count($invalidRows) > 0 ? 'bg-red-100' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-times-circle {{ count($invalidRows) > 0 ? 'text-red-600' : 'text-gray-400' }} text-lg"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Con errores</p>
                            <p class="text-2xl font-bold {{ count($invalidRows) > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ count($invalidRows) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filas con errores --}}
            @if (count($invalidRows) > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-red-200 overflow-hidden mb-6">
                    <div class="bg-red-50 px-6 py-4 border-b border-red-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                                <div>
                                    <h3 class="text-base font-semibold text-red-800">
                                        Filas con errores ({{ count($invalidRows) }})
                                    </h3>
                                    <p class="text-sm text-red-600">Estas filas no se importarán</p>
                                </div>
                            </div>
                            <button onclick="document.getElementById('errorTable').classList.toggle('hidden')"
                                class="text-sm text-red-600 hover:text-red-800 font-medium">
                                <i class="fas fa-chevron-down mr-1"></i>Mostrar/Ocultar
                            </button>
                        </div>
                    </div>
                    <div id="errorTable" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-red-200">
                            <thead class="bg-red-50/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-red-700 uppercase">Fila</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-red-700 uppercase">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-red-700 uppercase">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-red-700 uppercase">Errores</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-100">
                                @foreach ($invalidRows as $row)
                                    <tr class="hover:bg-red-50/30">
                                        <td class="px-4 py-3 text-sm text-red-800 font-mono">{{ $row['row'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['data']['code'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['data']['name'] ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <ul class="text-xs text-red-600 space-y-0.5">
                                                @foreach ($row['errors'] as $error)
                                                    <li><i class="fas fa-times mr-1"></i>{{ $error }}</li>
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

            {{-- Filas válidas --}}
            @if (count($validRows) > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-green-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-3"></i>
                            <div>
                                <h3 class="text-base font-semibold text-green-800">
                                    Productos válidos ({{ count($validRows) }})
                                </h3>
                                <p class="text-sm text-green-600">Estos productos se importarán al confirmar</p>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fila</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Rastreo</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Proveedor</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Categoría</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Marca</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Precio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($validRows as $row)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $row['row'] }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['processed']['code'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate">{{ $row['processed']['name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row['relations']['product_type_name'] ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            @php
                                                $trackingColors = [
                                                    'rfid' => 'bg-purple-100 text-purple-700',
                                                    'serial' => 'bg-blue-100 text-blue-700',
                                                    'code' => 'bg-gray-100 text-gray-700',
                                                ];
                                                $color = $trackingColors[$row['processed']['tracking_type']] ?? 'bg-gray-100 text-gray-700';
                                            @endphp
                                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full {{ $color }}">
                                                {{ strtoupper($row['processed']['tracking_type']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            @if ($row['relations']['supplier_name'])
                                                @if (str_contains($row['relations']['supplier_name'], '(nuevo)'))
                                                    <span class="text-amber-600">
                                                        <i class="fas fa-plus-circle text-xs mr-1"></i>{{ $row['relations']['supplier_name'] }}
                                                    </span>
                                                @else
                                                    {{ $row['relations']['supplier_name'] }}
                                                @endif
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row['relations']['category_name'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            @if ($row['relations']['brand_name'])
                                                @if (str_contains($row['relations']['brand_name'], '(nuevo)'))
                                                    <span class="text-amber-600">
                                                        <i class="fas fa-plus-circle text-xs mr-1"></i>{{ $row['relations']['brand_name'] }}
                                                    </span>
                                                @else
                                                    {{ $row['relations']['brand_name'] }}
                                                @endif
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 text-right font-mono">
                                            ${{ number_format($row['processed']['list_price'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            

        </div>
    </div>

    @push('scripts')
        <script>
            const confirmForm = document.getElementById('confirmForm');
            if (confirmForm) {
                confirmForm.addEventListener('submit', function (e) {
                    if (!confirm('¿Confirma la importación de {{ count($validRows) }} producto(s)?')) {
                        e.preventDefault();
                        return;
                    }

                    const btn = document.getElementById('btnConfirm');
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                    document.getElementById('confirmText').classList.add('hidden');
                    document.getElementById('confirmLoading').classList.remove('hidden');
                });
            }
        </script>
    @endpush
</x-app-layout>