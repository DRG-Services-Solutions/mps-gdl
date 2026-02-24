<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>Nueva Remisión
                </h2>
                <p class="mt-1 text-sm text-gray-600">Selecciona una cirugía programada para generar la remisión</p>
            </div>
            <a href="{{ route('shipping-notes.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="shippingNoteCreate()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Alertas --}}
            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-0.5"></i>
                        <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('shipping-notes.store') }}">
                @csrf

                {{-- Paso 1: Selección de cirugía --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-calendar-check mr-2 text-indigo-500"></i>Cirugía Programada
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Selección de cirugía --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Cirugía <span class="text-red-500">*</span>
                            </label>
                            <select name="scheduled_surgery_id"
                                x-model="selectedSurgeryId"
                                @change="loadChecklistPreview()"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">— Seleccionar cirugía —</option>
                                @foreach ($surgeries as $surgery)
                                    <option value="{{ $surgery->id }}"
                                        data-hospital="{{ $surgery->hospital->name ?? 'N/A' }}"
                                        data-doctor="{{ $surgery->doctor->full_name ?? 'N/A' }}"
                                        data-checklist="{{ $surgery->checklist->surgery_type ?? 'N/A' }}"
                                        data-date="{{ $surgery->surgery_datetime?->format('d/m/Y H:i') }}"
                                        {{ ($selectedSurgery?->id == $surgery->id || old('scheduled_surgery_id') == $surgery->id) ? 'selected' : '' }}>
                                        {{ $surgery->code }} — {{ $surgery->checklist->surgery_type ?? 'Sin tipo' }}
                                        ({{ $surgery->surgery_datetime?->format('d/m/Y') }})
                                        — {{ $surgery->hospital->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('scheduled_surgery_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            @if ($surgeries->isEmpty())
                                <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-sm text-yellow-800">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        No hay cirugías disponibles para generar remisión. Todas ya tienen una remisión activa
                                        o no están en estado válido.
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Razón social --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Razón Social (Facturación) <span class="text-red-500">*</span>
                            </label>
                            <select name="billing_legal_entity_id"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">— Seleccionar —</option>
                                @foreach ($legalEntities as $entity)
                                    <option value="{{ $entity->id }}" {{ old('billing_legal_entity_id') == $entity->id ? 'selected' : '' }}>
                                        {{ $entity->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('billing_legal_entity_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Notas --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                            <textarea name="notes" rows="2"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Observaciones adicionales...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Info de la cirugía seleccionada --}}
                <div x-show="surgeryInfo" x-cloak
                    class="mt-6 bg-indigo-50 rounded-xl border border-indigo-200 p-6">
                    <h3 class="text-lg font-semibold text-indigo-900 mb-4">
                        <i class="fas fa-stethoscope mr-2"></i>Datos de la Cirugía
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-indigo-600 font-medium block">Hospital</span>
                            <span class="text-gray-800" x-text="surgeryInfo?.hospital"></span>
                        </div>
                        <div>
                            <span class="text-indigo-600 font-medium block">Doctor</span>
                            <span class="text-gray-800" x-text="surgeryInfo?.doctor"></span>
                        </div>
                        <div>
                            <span class="text-indigo-600 font-medium block">Tipo de Cirugía</span>
                            <span class="text-gray-800" x-text="surgeryInfo?.checklist"></span>
                        </div>
                        <div>
                            <span class="text-indigo-600 font-medium block">Fecha</span>
                            <span class="text-gray-800" x-text="surgeryInfo?.date"></span>
                        </div>
                    </div>
                </div>

                {{-- Preview del checklist evaluado --}}
                <div x-show="checklistItems.length > 0" x-cloak class="mt-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-clipboard-list mr-2 text-green-500"></i>
                                Preview del Checklist Evaluado
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Productos que se incluirán en la remisión según condicionales aplicados
                            </p>
                        </div>

                        {{-- Resumen --}}
                        <div x-show="checklistSummary" class="px-6 py-3 bg-green-50 border-b border-green-100">
                            <div class="flex gap-6 text-sm">
                                <span class="text-green-800">
                                    <i class="fas fa-boxes mr-1"></i>
                                    Total: <strong x-text="checklistSummary?.total_items"></strong> productos
                                </span>
                                <span class="text-green-800">
                                    <i class="fas fa-calculator mr-1"></i>
                                    Cantidad total: <strong x-text="checklistSummary?.total_quantity"></strong> unidades
                                </span>
                                <span class="text-amber-700" x-show="checklistSummary?.items_with_conditionals > 0">
                                    <i class="fas fa-sliders-h mr-1"></i>
                                    Con condicionales: <strong x-text="checklistSummary?.items_with_conditionals"></strong>
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Producto</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cant. Base</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cant. Ajustada</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Condicional</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Obligatorio</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="(item, index) in checklistItems" :key="index">
                                        <tr :class="item.has_conditional ? 'bg-amber-50' : ''">
                                            <td class="px-6 py-3 text-sm text-gray-900" x-text="item.product_name"></td>
                                            <td class="px-6 py-3 text-sm text-gray-600 text-center" x-text="item.base_quantity"></td>
                                            <td class="px-6 py-3 text-center">
                                                <span class="inline-flex px-2 py-0.5 text-sm font-bold rounded"
                                                    :class="item.has_conditional ? 'bg-amber-200 text-amber-800' : 'text-gray-900'"
                                                    x-text="item.adjusted_quantity">
                                                </span>
                                            </td>
                                            <td class="px-6 py-3 text-xs text-amber-700" x-text="item.conditional_description || '—'"></td>
                                            <td class="px-6 py-3 text-center">
                                                <template x-if="item.is_mandatory">
                                                    <i class="fas fa-check-circle text-green-500"></i>
                                                </template>
                                                <template x-if="!item.is_mandatory">
                                                    <i class="fas fa-minus-circle text-gray-400"></i>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Loading --}}
                <div x-show="loading" x-cloak class="mt-6 text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-indigo-500"></i>
                    <p class="text-sm text-gray-600 mt-2">Evaluando checklist con condicionales...</p>
                </div>

                {{-- Botón de enviar --}}
                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('shipping-notes.index') }}"
                        class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                        :disabled="!selectedSurgeryId"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-file-invoice mr-2"></i>Crear Remisión
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function shippingNoteCreate() {
            return {
                selectedSurgeryId: '{{ $selectedSurgery?->id ?? old('scheduled_surgery_id', '') }}',
                surgeryInfo: null,
                checklistItems: [],
                checklistSummary: null,
                loading: false,

                init() {
                    if (this.selectedSurgeryId) {
                        this.loadChecklistPreview();
                    }
                },

                async loadChecklistPreview() {
                    if (!this.selectedSurgeryId) {
                        this.surgeryInfo = null;
                        this.checklistItems = [];
                        this.checklistSummary = null;
                        return;
                    }

                    // Leer datos del option seleccionado
                    const select = document.querySelector('select[name="scheduled_surgery_id"]');
                    const option = select.options[select.selectedIndex];
                    this.surgeryInfo = {
                        hospital: option.dataset.hospital,
                        doctor: option.dataset.doctor,
                        checklist: option.dataset.checklist,
                        date: option.dataset.date,
                    };

                    // Cargar preview del checklist via AJAX
                    this.loading = true;
                    this.checklistItems = [];
                    this.checklistSummary = null;

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
                            this.checklistItems = data.items || [];
                            this.checklistSummary = data.summary || null;
                        }
                    } catch (error) {
                        console.error('Error cargando preview:', error);
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
    @endpush
</x-app-layout>