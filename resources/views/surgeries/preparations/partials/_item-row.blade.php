@php
    $condCount = $item->conditionals ? $item->conditionals->count() : 0;
@endphp

{{-- ═══════════ DESKTOP: fila de tabla normal ═══════════ --}}
<tr class="hover:bg-red-50 transition-colors hidden md:table-row" id="item-row-{{ $item->id }}">    
    {{-- Producto --}}
    <td class="px-6 py-4">
        <div class="flex items-center">
            @if ($item->is_mandatory)
                <i class="fas fa-star text-yellow-500 mr-2" title="Obligatorio"></i>
            @endif
            <div>
                <div class="font-medium text-gray-900">{{ $item->product->code }}</div>
                <div class="text-xs text-gray-500 font-mono">{{ $item->product->name }}</div>
                @if ($item->notes && str_starts_with($item->notes, 'Dependencia de:'))
                    <span class="inline-flex items-center gap-0.5 mt-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-blue-100 text-blue-700">
                        <i class="fas fa-link"></i> {{ $item->notes }}
                    </span>
                @elseif($item->notes && str_starts_with($item->notes, 'Reemplazo de:'))
                    <span class="inline-flex items-center gap-0.5 mt-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-orange-100 text-orange-700">
                        <i class="fas fa-exchange-alt"></i> {{ $item->notes }}
                    </span>
                @endif
            </div>
        </div>
    </td>

    {{-- Ubicación --}}
    <td class="px-6 py-4 text-center">
        @if ($item->storageLocation)
            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                <i class="fas fa-map-marker-alt mr-1"></i>
                {{ $item->storageLocation->code }}
            </span>
        @else
            <span class="text-xs text-gray-300 italic">— Sin ubicación —</span>
        @endif
    </td>

    {{-- En Paquete --}}
    <td class="px-6 py-4 text-center">
        <span class="inline-flex items-center px-3 py-1 rounded-full font-bold text-sm {{ $item->quantity_in_package > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-50 text-gray-400' }}">
            {{ $item->quantity_in_package }}
        </span>
    </td>

    {{-- Surtidos --}}
    <td class="px-6 py-4 text-center">
        <span id="picked-{{ $item->id }}" class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full font-bold text-sm">
            {{ $item->quantity_picked }}
        </span>
    </td>

    {{-- Faltan --}}
    <td class="px-6 py-4 text-center">
        <span id="missing-{{ $item->id }}" class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-full font-bold text-sm animate-pulse">
            {{ $item->quantity_missing }}
        </span>
    </td>

    {{-- Requeridos --}}
    <td class="px-6 py-4 text-center">
        <span class="text-gray-600 font-medium">{{ $item->quantity_required }}</span>
    </td>

    {{-- Condicionales --}}
    <td class="px-6 py-4 text-center">
        @if ($condCount > 0)
            <button type="button"
                onclick="openConditionalsModal('{{ addslashes($item->product->name) }}', 'conditionals-data-{{ $item->id }}')"
                class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-700 hover:bg-purple-200 rounded-full font-bold text-xs transition-colors cursor-pointer">
                <i class="fas fa-list-ul mr-1"></i>
                Ver {{ $condCount }}
            </button>
        @else
            <span class="text-gray-400 text-xs italic">Sin Condicional</span>
        @endif
    </td>
</tr>

{{-- ═══════════ MÓVIL: card layout ═══════════ --}}
<tr class="md:hidden" id="item-row-mobile-{{ $item->id }}">
        <td colspan="7" class="px-4 py-3">
        <div class="bg-white border border-red-100 rounded-xl p-4 shadow-sm space-y-3">
            {{-- Header: Producto + Obligatorio --}}
            <div class="flex items-start justify-between">
                <div class="flex items-center flex-1 min-w-0">
                    @if ($item->is_mandatory)
                        <i class="fas fa-star text-yellow-500 mr-2 flex-shrink-0" title="Obligatorio"></i>
                    @endif
                    <div class="min-w-0">
                        <p class="font-bold text-gray-900 text-sm truncate">{{ $item->product->name }}</p>
                        <p class="text-xs text-gray-500 font-mono">{{ $item->product->code }}</p>
                        @if ($item->notes && str_starts_with($item->notes, 'Dependencia de:'))
                            <span class="inline-flex items-center gap-0.5 mt-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-blue-100 text-blue-700">
                                <i class="fas fa-link"></i> {{ $item->notes }}
                            </span>
                        @elseif($item->notes && str_starts_with($item->notes, 'Reemplazo de:'))
                            <span class="inline-flex items-center gap-0.5 mt-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-orange-100 text-orange-700">
                                <i class="fas fa-exchange-alt"></i> {{ $item->notes }}
                            </span>
                        @endif
                    </div>
                </div>

                @if ($condCount > 0)
                    <button type="button"
                        onclick="openConditionalsModal('{{ addslashes($item->product->name) }}', 'conditionals-data-{{ $item->id }}')"
                        class="ml-2 flex-shrink-0 inline-flex items-center px-2 py-1 bg-purple-100 text-purple-700 rounded-full font-bold text-[10px]">
                        <i class="fas fa-filter mr-1"></i> {{ $condCount }}
                    </button>
                @endif
            </div>

            {{-- Ubicación --}}
            @if ($item->storageLocation)
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        {{ $item->storageLocation->code }}
                    </span>
                </div>
            @endif

            {{-- Grid de cantidades --}}
            <div class="grid grid-cols-4 gap-2">
                <div class="text-center p-2 rounded-lg bg-blue-50">
                    <p class="text-lg font-black text-blue-600">{{ $item->quantity_in_package }}</p>
                    <p class="text-[9px] font-bold text-blue-400 uppercase">Paquete</p>
                </div>
                <div class="text-center p-2 rounded-lg bg-green-50">
                    <p id="picked-mobile-{{ $item->id }}" class="text-lg font-black text-green-600">{{ $item->quantity_picked }}</p>
                    <p class="text-[9px] font-bold text-green-400 uppercase">Surtidos</p>
                </div>
                <div class="text-center p-2 rounded-lg bg-red-50">
                    <p id="missing-mobile-{{ $item->id }}" class="text-lg font-black text-red-600 animate-pulse">{{ $item->quantity_missing }}</p>
                    <p class="text-[9px] font-bold text-red-400 uppercase">Faltan</p>
                </div>
                <div class="text-center p-2 rounded-lg bg-gray-50">
                    <p class="text-lg font-black text-gray-600">{{ $item->quantity_required }}</p>
                    <p class="text-[9px] font-bold text-gray-400 uppercase">Total</p>
                </div>
            </div>
        </div>
    </td>
</tr>

{{-- Datos ocultos de condicionales (compartido desktop/móvil) --}}
@if ($condCount > 0)
    <tr class="hidden">
        <td colspan="7">
            <div id="conditionals-data-{{ $item->id }}">
                @foreach ($item->conditionals as $conditional)
                    <div class="mb-3 p-3 bg-purple-50 rounded-lg border border-purple-100 text-left">
                        <p class="text-sm font-bold text-purple-900 mb-1">
                            <i class="fas fa-filter text-purple-600 mr-1"></i> Cuándo aplica:
                        </p>
                        <p class="text-sm text-gray-700 mb-2">
                            {{ $conditional->getDescription() }}
                        </p>
                        <div class="mt-2 text-sm text-purple-800 bg-white inline-block px-3 py-1.5 rounded border border-purple-200 shadow-sm">
                            <strong><i class="fas fa-bolt text-yellow-500 mr-1"></i> Acción:</strong>
                            {{ $conditional->getActionDescription() }}
                        </div>
                        @if ($conditional->notes)
                            <p class="mt-3 text-xs text-gray-500 italic border-t border-purple-100 pt-2">
                                <i class="fas fa-comment-dots mr-1"></i> Nota: {{ $conditional->notes }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </td>
    </tr>
@endif