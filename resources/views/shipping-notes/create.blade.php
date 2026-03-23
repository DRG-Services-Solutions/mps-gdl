<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>Nueva Remisión
                </h2>
                <p class="mt-1 text-sm text-gray-600">Selecciona cirugía, revisa y edita los items antes de crear</p>
            </div>
            <a href="{{ route('shipping-notes.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="remisionCreate()" x-cloak>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <p class="text-sm text-red-700 font-medium"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('shipping-notes.store') }}" @submit="prepareSubmit()">
                @csrf

                {{-- PASO 1: DATOS DE LA REMISIÓN --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-calendar-check mr-2 text-indigo-500"></i>Datos de la Remisión
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Cirugía <span class="text-red-500">*</span>
                            </label>
                            <select name="scheduled_surgery_id" x-model="selectedSurgeryId" @change="loadItems()"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">— Seleccionar cirugía —</option>
                                @foreach ($surgeries as $surgery)
                                    <option value="{{ $surgery->id }}"
                                        data-hospital="{{ $surgery->hospital->name ?? 'N/A' }}"
                                        data-doctor="{{ $surgery->doctor->full_name ?? 'N/A' }}"
                                        data-checklist="{{ $surgery->checklist->surgery_type ?? 'N/A' }}"
                                        data-date="{{ $surgery->surgery_datetime?->format('d/m/Y H:i') }}">
                                        {{ $surgery->code }} — {{ $surgery->checklist->surgery_type ?? 'Sin tipo' }}
                                        ({{ $surgery->surgery_datetime?->format('d/m/Y') }})
                                        — {{ $surgery->hospital->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Razón Social <span class="text-red-500">*</span>
                            </label>
                            <select name="billing_legal_entity_id"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">— Seleccionar —</option>
                                @foreach ($legalEntities as $entity)
                                    <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Info cirugía --}}
                    <div x-show="surgeryInfo" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                        <div>
                            <span class="text-xs text-indigo-600 font-medium block">Hospital</span>
                            <span class="text-sm text-gray-900 font-semibold" x-text="surgeryInfo?.hospital"></span>
                        </div>
                        <div>
                            <span class="text-xs text-indigo-600 font-medium block">Doctor</span>
                            <span class="text-sm text-gray-900" x-text="surgeryInfo?.doctor"></span>
                        </div>
                        <div>
                            <span class="text-xs text-indigo-600 font-medium block">Cirugía</span>
                            <span class="text-sm text-gray-900" x-text="surgeryInfo?.checklist"></span>
                        </div>
                        <div>
                            <span class="text-xs text-indigo-600 font-medium block">Fecha</span>
                            <span class="text-sm text-gray-900" x-text="surgeryInfo?.date"></span>
                        </div>
                    </div>
                </div>

                {{-- Loading --}}
                <div x-show="loading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-indigo-500"></i>
                    <p class="text-sm text-gray-600 mt-2">Cargando checklist evaluado...</p>
                </div>

                {{-- PASO 2: TABLA DE ITEMS EDITABLE --}}
                <div x-show="items.length > 0" class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-list-alt mr-2 text-cyan-600"></i>Productos de la Remisión
                        </h3>
                        <span class="text-xs text-gray-500">
                            <i class="fas fa-pencil-alt mr-1"></i>Edita cantidades, precios y modo antes de crear
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-cyan-600 text-white">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-bold uppercase w-24">Cód.</th>
                                    <th class="px-3 py-3 text-left text-xs font-bold uppercase">Concepto</th>
                                    <th class="px-3 py-3 text-center text-xs font-bold uppercase w-20">Cant.</th>
                                    <th class="px-3 py-3 text-center text-xs font-bold uppercase w-28">Modo</th>
                                    <th class="px-3 py-3 text-right text-xs font-bold uppercase w-28">Precio</th>
                                    <th class="px-3 py-3 text-right text-xs font-bold uppercase w-32">Importe</th>
                                    <th class="px-3 py-3 text-center text-xs font-bold uppercase w-16"><i class="fas fa-gift" title="Cortesía"></i></th>
                                    <th class="px-3 py-3 text-center text-xs font-bold uppercase w-12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(item, idx) in items" :key="idx">
                                    <tr :class="{
                                        'bg-amber-50': item.has_conditional && item.quantity > 0,
                                        'bg-red-50 opacity-40': item.quantity <= 0,
                                        'bg-blue-50': item.source === 'additional',
                                    }">
                                        <td class="px-3 py-2">
                                            <span class="text-xs font-mono text-gray-500" x-text="item.product_code || '-'"></span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="text-sm text-gray-900 font-medium" x-text="item.product_name"></div>
                                            <div x-show="item.conditional_description" class="text-[10px] text-amber-600 mt-0.5">
                                                <i class="fas fa-sliders-h mr-0.5"></i><span x-text="item.conditional_description"></span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number" x-model.number="item.quantity" min="0"
                                                class="w-16 text-center text-sm rounded border-gray-300 py-1 focus:ring-cyan-500 focus:border-cyan-500"
                                                @input="recalculate()">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <select x-model="item.billing_mode"
                                                class="text-xs rounded border-gray-300 py-1 focus:ring-cyan-500" @change="recalculate()">
                                                <option value="sale">Venta</option>
                                                <option value="rental">Renta</option>
                                                <option value="no_charge">Sin Cargo</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <input type="number" x-model.number="item.unit_price" min="0" step="0.01"
                                                class="w-24 text-right text-sm rounded border-gray-300 py-1 focus:ring-cyan-500 focus:border-cyan-500"
                                                @input="recalculate()">
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <span class="text-sm font-semibold"
                                                :class="item.exclude_from_invoice ? 'text-gray-400 line-through' : 'text-gray-900'"
                                                x-text="'$' + (item.quantity * item.unit_price).toFixed(2)">
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="checkbox" x-model="item.exclude_from_invoice"
                                                class="rounded border-gray-300 text-amber-500 focus:ring-amber-500" @change="recalculate()">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" @click="removeItem(idx)" class="text-red-400 hover:text-red-600 transition" title="Quitar">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- TOTALES --}}
                    <div class="border-t border-gray-200 p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                                <textarea name="notes" rows="3"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Observaciones, instrucciones especiales..."></textarea>
                            </div>
                            <div class="w-full md:w-80 space-y-2">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Subtotal:</span>
                                    <span class="text-lg font-semibold text-gray-900" x-text="'$' + subtotal.toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-600">I.V.A.</span>
                                        <input type="number" x-model.number="taxRatePct" min="0" max="100" step="0.01"
                                            class="w-16 text-center text-xs rounded border-gray-300 py-1 focus:ring-cyan-500" @input="recalculate()">
                                        <span class="text-xs text-gray-500">%</span>
                                    </div>
                                    <span class="text-lg font-semibold text-gray-900" x-text="'$' + taxAmount.toFixed(2)"></span>
                                </div>
                                <input type="hidden" name="tax_rate" :value="taxRatePct / 100">
                                <div class="flex justify-between items-center py-3 px-4 bg-cyan-50 rounded-lg border-2 border-cyan-200">
                                    <span class="text-sm font-bold text-cyan-800 uppercase">Total:</span>
                                    <span class="text-2xl font-bold text-cyan-800" x-text="'$' + grandTotal.toFixed(2)"></span>
                                </div>
                                <p class="text-[10px] text-gray-500 italic">
                                    <span x-text="items.filter(i => i.quantity > 0).length"></span> productos
                                    · <span x-text="items.filter(i => i.exclude_from_invoice).length"></span> cortesías
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Items excluidos --}}
                <div x-show="excludedItems.length > 0" class="mt-6 bg-red-50 rounded-xl border border-red-200 p-4">
                    <h4 class="text-sm font-bold text-red-800 mb-2"><i class="fas fa-ban mr-1"></i>Excluidos por Condicionales</h4>
                    <template x-for="(ex, idx) in excludedItems" :key="'ex-'+idx">
                        <div class="flex items-center gap-3 py-1 text-sm text-red-700">
                            <span class="line-through" x-text="ex.product_name"></span>
                            <span class="text-xs bg-red-100 px-2 py-0.5 rounded" x-text="ex.conditional_description"></span>
                        </div>
                    </template>
                </div>

                <div id="hidden-items"></div>

                {{-- Botones --}}
                <div x-show="items.length > 0" class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('shipping-notes.index') }}"
                        class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                        Cancelar
                    </a>
                    <button type="submit" :disabled="items.filter(i => i.quantity > 0).length === 0"
                        class="px-6 py-3 bg-cyan-600 text-white rounded-lg text-sm font-bold hover:bg-cyan-700 transition disabled:opacity-50 shadow-lg">
                        <i class="fas fa-file-invoice mr-2"></i>Crear Remisión
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function remisionCreate() {
            return {
                selectedSurgeryId: '',
                surgeryInfo: null,
                items: [],
                excludedItems: [],
                loading: false,
                taxRatePct: 16,
                subtotal: 0,
                taxAmount: 0,
                grandTotal: 0,

                async loadItems() {
                    if (!this.selectedSurgeryId) {
                        this.surgeryInfo = null;
                        this.items = [];
                        this.excludedItems = [];
                        this.recalculate();
                        return;
                    }

                    const select = document.querySelector('select[name="scheduled_surgery_id"]');
                    const opt = select.options[select.selectedIndex];
                    this.surgeryInfo = {
                        hospital: opt.dataset.hospital,
                        doctor: opt.dataset.doctor,
                        checklist: opt.dataset.checklist,
                        date: opt.dataset.date,
                    };

                    this.loading = true;

                    try {
                        const response = await fetch('{{ route("shipping-notes.api.preview-checklist") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ surgery_id: this.selectedSurgeryId }),
                        });

                        if (response.ok) {
                            const data = await response.json();

                            this.items = (data.items || [])
                                .filter(i => i.adjusted_quantity > 0)
                                .map(i => ({
                                    product_id: i.product_id,
                                    product_name: i.product_name,
                                    product_code: i.product_code || '',
                                    quantity: i.adjusted_quantity,
                                    unit_price: i.list_price || 0,
                                    billing_mode: 'sale',
                                    exclude_from_invoice: i.exclude_from_invoice || false,
                                    has_conditional: i.has_conditional,
                                    conditional_description: i.conditional_description,
                                    checklist_item_id: i.checklist_item_id || null,
                                    conditional_id: i.conditional_id || null,
                                    source: i.source || 'base',
                                }));

                            this.excludedItems = (data.items || [])
                                .filter(i => i.adjusted_quantity === 0 && i.has_conditional)
                                .map(i => ({
                                    product_name: i.product_name,
                                    conditional_description: i.conditional_description || 'Excluido',
                                }));

                            this.recalculate();
                        }
                    } catch (err) {
                        console.error('Error cargando items:', err);
                    } finally {
                        this.loading = false;
                    }
                },

                removeItem(idx) {
                    this.items.splice(idx, 1);
                    this.recalculate();
                },

                recalculate() {
                    this.subtotal = this.items
                        .filter(i => i.quantity > 0 && !i.exclude_from_invoice && i.billing_mode !== 'no_charge')
                        .reduce((sum, i) => sum + (i.quantity * i.unit_price), 0);
                    const rate = (this.taxRatePct || 0) / 100;
                    this.taxAmount = Math.round(this.subtotal * rate * 100) / 100;
                    this.grandTotal = Math.round((this.subtotal + this.taxAmount) * 100) / 100;
                },

                prepareSubmit() {
                    const container = document.getElementById('hidden-items');
                    container.innerHTML = '';
                    this.items.filter(i => i.quantity > 0).forEach((item, idx) => {
                        const fields = {
                            product_id: item.product_id,
                            quantity: item.quantity,
                            unit_price: item.unit_price,
                            billing_mode: item.billing_mode,
                            exclude_from_invoice: item.exclude_from_invoice ? 1 : 0,
                            checklist_item_id: item.checklist_item_id || '',
                            conditional_id: item.conditional_id || '',
                            conditional_description: item.conditional_description || '',
                            source: item.source || 'base',
                        };
                        for (const [key, value] of Object.entries(fields)) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `items[${idx}][${key}]`;
                            input.value = value;
                            container.appendChild(input);
                        }
                    });
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
