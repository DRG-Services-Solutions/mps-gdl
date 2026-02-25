<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-eye mr-2 text-indigo-600"></i>
                    {{ __('Previsualización de Importación') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Revisa los datos antes de confirmar · <span class="font-medium">{{ $fileName }}</span></p>
            </div>
            <a href="{{ route('checklists.import.form') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Subir otro archivo
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Resumen Estadístico --}}
            @if(!empty($result['stats']))
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                        <p class="text-2xl font-bold text-indigo-600">{{ $result['stats']['total_rows'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Filas leídas</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $result['stats']['new_checklists'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Checklists nuevos</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $result['stats']['valid_items'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Items válidos</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                        <p class="text-2xl font-bold {{ $result['stats']['skipped_checklists'] > 0 ? 'text-yellow-600' : 'text-gray-400' }}">
                            {{ $result['stats']['skipped_checklists'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Ya existentes (se omiten)</p>
                    </div>
                </div>
            @endif

            {{-- Errores --}}
            @if(!empty($result['errors']))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-red-900 mb-2">
                        <i class="fas fa-times-circle mr-1"></i> Errores ({{ count($result['errors']) }})
                    </h4>
                    <ul class="text-sm text-red-700 space-y-1 max-h-48 overflow-y-auto">
                        @foreach($result['errors'] as $error)
                            <li class="flex items-start">
                                <i class="fas fa-circle text-[4px] mt-2 mr-2 flex-shrink-0"></i>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Warnings --}}
            @if(!empty($result['warnings']))
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-yellow-900 mb-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Advertencias ({{ count($result['warnings']) }})
                    </h4>
                    <ul class="text-sm text-yellow-700 space-y-1 max-h-48 overflow-y-auto">
                        @foreach($result['warnings'] as $warning)
                            <li class="flex items-start">
                                <i class="fas fa-circle text-[4px] mt-2 mr-2 flex-shrink-0"></i>
                                {{ $warning }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Preview de Checklists --}}
            @if(!empty($result['checklists']))
                @foreach($result['checklists'] as $checklist)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden {{ $checklist['already_exists'] ? 'opacity-60' : '' }}">
                        {{-- Header del Checklist --}}
                        <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between {{ $checklist['already_exists'] ? 'bg-yellow-50' : 'bg-gray-50' }}">
                            <div>
                                <h4 class="font-semibold text-gray-900">
                                    <i class="fas fa-clipboard-list mr-1 text-indigo-500"></i>
                                    {{ $checklist['code'] }}
                                </h4>
                                <p class="text-xs text-gray-500">{{ $checklist['surgery_type'] }} · {{ $checklist['items_count'] }} items</p>
                            </div>
                            @if($checklist['already_exists'])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Ya existe - Se omitirá
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-plus mr-1"></i> Nuevo
                                </span>
                            @endif
                        </div>

                        {{-- Tabla de Items --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Fila</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">SKU Producto</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Cantidad</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Obligatorio</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Notas</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($checklist['items'] as $item)
                                        <tr class="{{ !$item['product_exists'] ? 'bg-red-50' : '' }}">
                                            <td class="px-4 py-2 text-gray-400 text-xs">{{ $item['row_number'] }}</td>
                                            <td class="px-4 py-2 font-mono text-xs">{{ $item['product_sku'] }}</td>
                                            <td class="px-4 py-2 text-center">{{ $item['quantity'] }}</td>
                                            <td class="px-4 py-2 text-center">
                                                @if($item['is_mandatory'])
                                                    <i class="fas fa-check-circle text-green-500"></i>
                                                @else
                                                    <i class="fas fa-minus-circle text-gray-300"></i>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-xs text-gray-500">{{ $item['notes'] ?: '-' }}</td>
                                            <td class="px-4 py-2 text-center">
                                                @if($item['product_exists'])
                                                    <span class="text-xs text-green-600">
                                                        <i class="fas fa-check"></i> OK
                                                    </span>
                                                @else
                                                    <span class="text-xs text-red-600 font-medium">
                                                        <i class="fas fa-times"></i> SKU no encontrado
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Botón de Confirmación --}}
            <div class="bg-white rounded-lg shadow-sm p-6 flex items-center justify-between">
                <div>
                    @if($result['success'])
                        <p class="text-sm text-green-700 font-medium">
                            <i class="fas fa-check-circle mr-1"></i>
                            Todo listo para importar
                        </p>
                    @else
                        <p class="text-sm text-red-700 font-medium">
                            <i class="fas fa-times-circle mr-1"></i>
                            Hay errores que impiden la importación. Corrige el archivo y sube de nuevo.
                        </p>
                    @endif
                </div>

                <div class="flex space-x-3">
                    <a href="{{ route('checklists.import.form') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>

                    @if($result['success'])
                        <form action="{{ route('checklists.import.confirm') }}" method="POST" id="confirmForm">
                            @csrf
                            <button type="submit" 
                                    id="confirmBtn"
                                    onclick="return confirm('¿Estás seguro de importar {{ $result['stats']['new_checklists'] }} checklists con {{ $result['stats']['valid_items'] }} items?')"
                                    class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-check mr-1"></i>
                                Confirmar Importación
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('confirmForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('confirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Importando...';
        });
    </script>
    @endpush
</x-app-layout>
