<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    <i class="fas fa-undo mr-2 text-purple-600"></i>Registrar Retorno
                </h2>
                <p class="mt-1 text-sm text-gray-600">{{ $shippingNote->shipping_number }} — {{ $shippingNote->surgery_type }}</p>
            </div>
            <a href="{{ route('shipping-notes.show', $shippingNote) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="returnForm()">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Info de la remisión --}}
            <div class="bg-purple-50 rounded-xl border border-purple-200 p-5">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-purple-600 font-medium block">Hospital</span>
                        <span class="text-gray-900">{{ $shippingNote->hospital->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-purple-600 font-medium block">Doctor</span>
                        <span class="text-gray-900">{{ $shippingNote->doctor->full_name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-purple-600 font-medium block">Fecha Cirugía</span>
                        <span class="text-gray-900">{{ $shippingNote->surgery_date?->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-purple-600 font-medium block">Total Items</span>
                        <span class="text-gray-900 font-bold">{{ $shippingNote->items->count() }} productos</span>
                    </div>
                </div>
            </div>

            {{-- Acciones rápidas --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">
                        <i class="fas fa-magic mr-1 text-indigo-500"></i>Acciones rápidas:
                    </span>
                    <div class="flex gap-2">
                        <button type="button" @click="markAllReturned()"
                            class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-xs font-medium hover:bg-green-200 transition">
                            <i class="fas fa-check-double mr-1"></i>Marcar todo como retornado
                        </button>
                        <button type="button" @click="markAllUsed()"
                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-xs font-medium hover:bg-red-200 transition">
                            <i class="fas fa-times-circle mr-1"></i>Marcar todo como usado
                        </button>
                        <button type="button" @click="resetAll()"
                            class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-xs font-medium hover:bg-gray-200 transition">
                            <i class="fas fa-undo mr-1"></i>Resetear
                        </button>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('shipping-notes.register-return', $shippingNote) }}">
                @csrf

                {{-- Items del paquete --}}
                @foreach (['package' => 'Paquetes Pre-Armados', 'kit' => 'Kits Quirúrgicos', 'standalone' => 'Productos Individuales', 'conditional' => 'Productos Condicionales'] as $origin => $title)
                    @php
                        $originItems = $shippingNote->items->where('item_origin', $origin)->whereIn('status', ['sent', 'in_surgery']);
                        $iconMap = ['package' => 'box-open text-blue-500', 'kit' => 'briefcase-medical text-teal-500', 'standalone' => 'cube text-gray-600', 'conditional' => 'sliders-h text-amber-500'];
                    @endphp

                    @if ($originItems->count() > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <i class="fas fa-{{ $iconMap[$origin] ?? 'cube text-gray-500' }} mr-2"></i>
                                    {{ $title }}
                                    <span class="text-sm text-gray-500 font-normal">({{ $originItems->count() }})</span>
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Producto</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">EPC</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Enviado</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Modo</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-48">¿Regresó?</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-28">Cant. Retornada</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach ($originItems as $index => $item)
                                            @php $itemKey = "item_{$item->id}"; @endphp
                                            <tr :class="items['{{ $itemKey }}']?.returned === false ? 'bg-red-50' : (items['{{ $itemKey }}']?.returned === true ? 'bg-green-50' : '')">
                                                <td class="px-6 py-3">
                                                    <div class="text-sm text-gray-900 font-medium">{{ $item->product->name ?? 'N/A' }}</div>
                                                    @if ($item->shippingNotePackage)
                                                        <div class="text-xs text-blue-600">
                                                            <i class="fas fa-box mr-1"></i>{{ $item->shippingNotePackage->preAssembledPackage->code ?? '' }}
                                                        </div>
                                                    @endif
                                                    @if ($item->shippingNoteKit)
                                                        <div class="text-xs text-teal-600">
                                                            <i class="fas fa-briefcase-medical mr-1"></i>{{ $item->shippingNoteKit->surgicalKit->code ?? '' }}
                                                        </div>
                                                    @endif
                                                    @if ($item->conditional_description)
                                                        <div class="text-xs text-amber-600">
                                                            <i class="fas fa-sliders-h mr-1"></i>{{ $item->conditional_description }}
                                                        </div>
                                                    @endif

                                                    <input type="hidden" name="items[{{ $item->id }}][item_id]" value="{{ $item->id }}">
                                                </td>
                                                <td class="px-6 py-3 text-xs text-gray-500 text-center font-mono">
                                                    {{ $item->productUnit->epc ?? '—' }}
                                                </td>
                                                <td class="px-6 py-3 text-sm text-gray-700 text-center font-medium">
                                                    {{ $item->quantity_sent }}
                                                </td>
                                                <td class="px-6 py-3 text-center">
                                                    @php
                                                        $modeColors = ['sale' => 'green', 'rental' => 'blue', 'no_charge' => 'gray'];
                                                        $modeLabels = ['sale' => 'Venta', 'rental' => 'Renta', 'no_charge' => 'Sin cargo'];
                                                    @endphp
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $modeColors[$item->billing_mode] ?? 'gray' }}-100 text-{{ $modeColors[$item->billing_mode] ?? 'gray' }}-700">
                                                        {{ $modeLabels[$item->billing_mode] ?? $item->billing_mode }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-3 text-center">
                                                    <div class="flex items-center justify-center gap-2">
                                                        <label class="inline-flex items-center gap-1 cursor-pointer px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                                            :class="items['{{ $itemKey }}']?.returned === true ? 'bg-green-200 text-green-800 ring-2 ring-green-400' : 'bg-gray-100 text-gray-600 hover:bg-green-100'">
                                                            <input type="radio"
                                                                name="items[{{ $item->id }}][returned]"
                                                                value="1"
                                                                x-model="items['{{ $itemKey }}'].returned"
                                                                x-bind:value="true"
                                                                @change="items['{{ $itemKey }}'].returned = true"
                                                                class="sr-only">
                                                            <i class="fas fa-check-circle"></i> Sí
                                                        </label>
                                                        <label class="inline-flex items-center gap-1 cursor-pointer px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                                            :class="items['{{ $itemKey }}']?.returned === false ? 'bg-red-200 text-red-800 ring-2 ring-red-400' : 'bg-gray-100 text-gray-600 hover:bg-red-100'">
                                                            <input type="radio"
                                                                name="items[{{ $item->id }}][returned]"
                                                                value="0"
                                                                x-model="items['{{ $itemKey }}'].returned"
                                                                x-bind:value="false"
                                                                @change="items['{{ $itemKey }}'].returned = false"
                                                                class="sr-only">
                                                            <i class="fas fa-times-circle"></i> No (Usado)
                                                        </label>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-3 text-center">
                                                    <input type="number"
                                                        name="items[{{ $item->id }}][quantity_returned]"
                                                        min="0" max="{{ $item->quantity_sent }}"
                                                        x-model="items['{{ $itemKey }}'].quantity_returned"
                                                        :disabled="items['{{ $itemKey }}']?.returned !== true"
                                                        class="w-20 rounded-lg border-gray-300 text-sm text-center disabled:bg-gray-100 disabled:text-gray-400">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endforeach

                {{-- Resumen y botón de enviar --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-clipboard-check mr-2 text-purple-500"></i>Resumen del Retorno
                        </h3>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <span class="block text-xs text-gray-500 font-medium uppercase">Total Items</span>
                            <span class="text-2xl font-bold text-gray-800" x-text="totalItems"></span>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <span class="block text-xs text-green-600 font-medium uppercase">Retornados</span>
                            <span class="text-2xl font-bold text-green-800" x-text="returnedCount"></span>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 text-center">
                            <span class="block text-xs text-red-600 font-medium uppercase">Usados (No regresó)</span>
                            <span class="text-2xl font-bold text-red-800" x-text="usedCount"></span>
                        </div>
                    </div>

                    <div x-show="pendingCount > 0" class="mb-4 bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <p class="text-sm text-amber-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Hay <strong x-text="pendingCount"></strong> items sin marcar. Debes indicar si cada item regresó o fue usado.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('shipping-notes.show', $shippingNote) }}"
                            class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                            Cancelar
                        </a>
                        <button type="submit"
                            :disabled="pendingCount > 0"
                            class="px-6 py-2.5 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            onclick="return confirm('¿Registrar el retorno? Los items marcados como usados se facturarán como venta.')">
                            <i class="fas fa-save mr-2"></i>Registrar Retorno
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function returnForm() {
            // Inicializar items desde el server
            const itemsData = {};
            @foreach ($shippingNote->items->whereIn('status', ['sent', 'in_surgery']) as $item)
                itemsData['item_{{ $item->id }}'] = {
                    id: {{ $item->id }},
                    returned: null,
                    quantity_returned: {{ $item->quantity_sent }},
                    quantity_sent: {{ $item->quantity_sent }},
                    billing_mode: '{{ $item->billing_mode }}',
                };
            @endforeach

            return {
                items: itemsData,

                get totalItems() {
                    return Object.keys(this.items).length;
                },

                get returnedCount() {
                    return Object.values(this.items).filter(i => i.returned === true).length;
                },

                get usedCount() {
                    return Object.values(this.items).filter(i => i.returned === false).length;
                },

                get pendingCount() {
                    return Object.values(this.items).filter(i => i.returned === null).length;
                },

                markAllReturned() {
                    Object.keys(this.items).forEach(key => {
                        this.items[key].returned = true;
                        this.items[key].quantity_returned = this.items[key].quantity_sent;
                    });
                },

                markAllUsed() {
                    Object.keys(this.items).forEach(key => {
                        this.items[key].returned = false;
                        this.items[key].quantity_returned = 0;
                    });
                },

                resetAll() {
                    Object.keys(this.items).forEach(key => {
                        this.items[key].returned = null;
                        this.items[key].quantity_returned = this.items[key].quantity_sent;
                    });
                },
            };
        }
    </script>
    @endpush
</x-app-layout>