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
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                                        No hay cirugías disponibles para generar remisión.
                                    </p>
                                </div>
                            @endif
                        </div>

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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                            <textarea name="notes" rows="2"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Observaciones adicionales...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Info de la cirugía --}}
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

                {{-- Info del paquete pre-armado --}}
                <div x-show="packageInfo" x-cloak class="mt-6">
                    <div class="bg-green-50 rounded-xl border border-green-200 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-box-open text-green-600 mr-3 text-xl"></i>
                                <div>
                                    <h4 class="font-semibold text-green-900">Paquete Pre-Armado</h4>
                                    <p class="text-sm text-green-700">
                                        <span x-text="packageInfo?.code"></span> — <span x-text="packageInfo?.name"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm text-green-700">Unidades en paquete:</span>
                                <span class="text-lg font-bold text-green-800 ml-1" x-text="packageInfo?.total_units"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Alerta: Sin paquete --}}
                <div x-show="surgeryInfo && !packageInfo && !loading" x-cloak class="mt-6">
                    <div class="bg-yellow-50 rounded-xl border border-yellow-300 p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-0.5"></i>
                            <div>
                                <h4 class="font-semibold text-yellow-800">Sin paquete pre-armado</h4>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Esta cirugía no tiene una preparación con paquete asignado.
                                    La remisión se creará solo con la evaluación del checklist.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Leyenda de dependencias (si hay) --}}
                <div x-show="checklistSummary?.items_with_dependencies > 0" x-cloak class="mt-6">
                    <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
                        <div class="flex items-start">
                            <i class="fas fa-link text-blue-600 mr-3 mt-0.5"></i>
                            <div>
                                <h4 class="font-semibold text-blue-900">Dependencias detectadas</h4>
                                <p class="text-sm text-blue-700 mt-1">
                                    Algunos productos requieren otro producto para funcionar. 
                                    Estos se resaltan con <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-200 text-blue-800"><i class="fas fa-link mr-1"></i>azul</span> 
                                    en la tabla. Verifica que ambos productos estén incluidos.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabla de comparación --}}
                <div x-show="checklistItems.length > 0" x-cloak class="mt-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-clipboard-list mr-2 text-green-500"></i>
                                Verificación: Checklist vs Paquete
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Comparación del checklist evaluado contra el contenido real del paquete
                            </p>
                        </div>

                        {{-- Resumen --}}
                        <div x-show="checklistSummary" class="px-6 py-3 bg-gray-50 border-b border-gray-100">
                            <div class="flex flex-wrap gap-4 text-sm">
                                <span class="text-gray-800">
                                    <i class="fas fa-boxes mr-1"></i>
                                    Requeridos: <strong x-text="checklistSummary?.total_items"></strong>
                                </span>
                                <span class="text-green-700" x-show="checklistSummary?.complete_items > 0">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Completos: <strong x-text="checklistSummary?.complete_items"></strong>
                                </span>
                                <span class="text-red-700" x-show="checklistSummary?.incomplete_items > 0">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Faltantes: <strong x-text="checklistSummary?.incomplete_items"></strong>
                                </span>
                                <span class="text-amber-700" x-show="checklistSummary?.items_with_conditionals > 0">
                                    <i class="fas fa-sliders-h mr-1"></i>
                                    Con condicionales: <strong x-text="checklistSummary?.items_with_conditionals"></strong>
                                </span>
                                <span class="text-blue-700" x-show="checklistSummary?.items_with_dependencies > 0">
                                    <i class="fas fa-link mr-1"></i>
                                    Con dependencias: <strong x-text="checklistSummary?.items_with_dependencies"></strong>
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Producto</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Base</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Requerido</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">En Paquete</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Diferencia</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Condicional</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="(item, index) in checklistItems" :key="index">
                                        <tr :class="{
                                            'bg-red-50': item.status === 'incomplete',
                                            'bg-blue-50 border-l-4 border-l-blue-400': item.action_type === 'add_dependency',
                                            'bg-amber-50': item.has_conditional && item.action_type !== 'add_dependency' && item.status !== 'incomplete',
                                            'bg-orange-50 border-l-4 border-l-orange-400': item.action_type === 'replace',
                                            'bg-gray-100': item.status === 'extra',
                                        }">
                                            {{-- Producto con badges --}}
                                            <td class="px-4 py-3">
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-sm font-medium text-gray-900" x-text="item.product_name"></span>
                                                    
                                                    {{-- Badge: Dependencia --}}
                                                    <template x-if="item.action_type === 'add_dependency' && item.target_product_name">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                                                                <i class="fas fa-link mr-1"></i>
                                                                Requiere
                                                            </span>
                                                            <span class="text-xs font-semibold text-blue-700" x-text="item.dependency_quantity + 'x ' + item.target_product_name"></span>
                                                        </div>
                                                    </template>

                                                    {{-- Badge: Reemplazo --}}
                                                    <template x-if="item.action_type === 'replace' && item.target_product_name">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-orange-100 text-orange-800 border border-orange-300">
                                                                <i class="fas fa-exchange-alt mr-1"></i>
                                                                Reemplaza por
                                                            </span>
                                                            <span class="text-xs font-semibold text-orange-700" x-text="item.target_product_name"></span>
                                                        </div>
                                                    </template>

                                                    {{-- Badge: Excluido --}}
                                                    <template x-if="item.action_type === 'exclude'">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800 border border-red-300">
                                                            <i class="fas fa-ban mr-1"></i>
                                                            Excluido por condicional
                                                        </span>
                                                    </template>

                                                    {{-- Badge: Cantidad ajustada --}}
                                                    <template x-if="item.action_type === 'adjust_quantity' && item.has_conditional">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-800 border border-purple-300">
                                                            <i class="fas fa-edit mr-1"></i>
                                                            Cantidad ajustada
                                                        </span>
                                                    </template>

                                                    {{-- Badge: Producto adicional --}}
                                                    <template x-if="item.source === 'additional'">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 border border-green-300">
                                                            <i class="fas fa-plus-circle mr-1"></i>
                                                            Producto adicional
                                                        </span>
                                                    </template>

                                                    {{-- Badge: Extra en paquete (no en checklist) --}}
                                                    <template x-if="item.source === 'extra_in_package'">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-200 text-gray-700 border border-gray-300">
                                                            <i class="fas fa-question-circle mr-1"></i>
                                                            No está en checklist
                                                        </span>
                                                    </template>
                                                </div>
                                            </td>
                                            
                                            {{-- Cantidad base --}}
                                            <td class="px-4 py-3 text-sm text-gray-500 text-center" x-text="item.base_quantity"></td>
                                            
                                            {{-- Cantidad requerida --}}
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex px-2 py-0.5 text-sm font-bold rounded"
                                                    :class="item.has_conditional ? 'bg-amber-200 text-amber-800' : 'text-gray-900'"
                                                    x-text="item.adjusted_quantity">
                                                </span>
                                            </td>
                                            
                                            {{-- En paquete --}}
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex px-2 py-0.5 text-sm font-bold rounded"
                                                    :class="{
                                                        'bg-green-200 text-green-800': item.in_package >= item.adjusted_quantity && item.in_package > 0,
                                                        'bg-red-200 text-red-800': item.in_package < item.adjusted_quantity && item.adjusted_quantity > 0,
                                                        'text-gray-400': item.in_package === 0 && item.adjusted_quantity === 0
                                                    }"
                                                    x-text="item.in_package">
                                                </span>
                                            </td>
                                            
                                            {{-- Diferencia --}}
                                            <td class="px-4 py-3 text-center">
                                                <template x-if="item.missing > 0">
                                                    <span class="inline-flex px-2 py-0.5 text-sm font-bold rounded bg-red-200 text-red-800"
                                                        x-text="'-' + item.missing">
                                                    </span>
                                                </template>
                                                <template x-if="item.surplus > 0">
                                                    <span class="inline-flex px-2 py-0.5 text-sm font-bold rounded bg-blue-200 text-blue-800"
                                                        x-text="'+' + item.surplus">
                                                    </span>
                                                </template>
                                                <template x-if="item.missing === 0 && item.surplus === 0">
                                                    <span class="text-green-500"><i class="fas fa-check"></i></span>
                                                </template>
                                            </td>
                                            
                                            {{-- Condicional (descripción breve) --}}
                                            <td class="px-4 py-3 text-xs text-gray-600 max-w-xs">
                                                <span x-text="item.conditional_description || '—'"></span>
                                            </td>
                                            
                                            {{-- Estado --}}
                                            <td class="px-4 py-3 text-center">
                                                <template x-if="item.status === 'complete'">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check-circle mr-1"></i>OK
                                                    </span>
                                                </template>
                                                <template x-if="item.status === 'incomplete'">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-times-circle mr-1"></i>Falta
                                                    </span>
                                                </template>
                                                <template x-if="item.status === 'surplus'">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <i class="fas fa-plus-circle mr-1"></i>Sobra
                                                    </span>
                                                </template>
                                                <template x-if="item.status === 'extra'">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600">
                                                        <i class="fas fa-question-circle mr-1"></i>Extra
                                                    </span>
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
                    <p class="text-sm text-gray-600 mt-2">Evaluando checklist y comparando con paquete...</p>
                </div>

                {{-- Botones --}}
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
                packageInfo: null,
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
                        this.packageInfo = null;
                        this.checklistItems = [];
                        this.checklistSummary = null;
                        return;
                    }

                    const select = document.querySelector('select[name="scheduled_surgery_id"]');
                    const option = select.options[select.selectedIndex];
                    this.surgeryInfo = {
                        hospital: option.dataset.hospital,
                        doctor: option.dataset.doctor,
                        checklist: option.dataset.checklist,
                        date: option.dataset.date,
                    };

                    this.loading = true;
                    this.checklistItems = [];
                    this.checklistSummary = null;
                    this.packageInfo = null;

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
                            this.packageInfo = data.package || null;
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