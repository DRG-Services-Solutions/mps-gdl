<x-app-layout>
    <x-slot name="header">
        @php
            $colors = \App\Models\ShippingNote::getStatusColors();
            $labels = \App\Models\ShippingNote::getStatusLabels();
            $color = $colors[$shipping_note->status] ?? 'gray';
            $badgeClasses = [
                'gray' => 'bg-gray-100 text-gray-700 ring-gray-300',
                'blue' => 'bg-blue-100 text-blue-700 ring-blue-300',
                'yellow' => 'bg-yellow-100 text-yellow-700 ring-yellow-300',
                'orange' => 'bg-orange-100 text-orange-700 ring-orange-300',
                'purple' => 'bg-purple-100 text-purple-700 ring-purple-300',
                'green' => 'bg-green-100 text-green-700 ring-green-300',
                'red' => 'bg-red-100 text-red-700 ring-red-300',
            ];
        @endphp
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    <i class="fas fa-file-invoice mr-2 text-indigo-600"></i>{{ $shipping_note->shipping_number }}
                </h2>
                <div class="flex items-center gap-3 mt-1">
                    <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full ring-1 {{ $badgeClasses[$color] ?? '' }}">
                        {{ $labels[$shipping_note->status] ?? $shipping_note->status }}
                    </span>
                    <span class="text-sm text-gray-600">{{ $shipping_note->surgery_type }}</span>
                </div>
            </div>
            <div class="flex gap-2">
                {{-- PDF --}}
                <a href="{{ route('shipping-notes.pdf', $shipping_note) }}" target="_blank"
                    class="inline-flex items-center px-3 py-2 bg-red-600 rounded-lg text-xs font-medium text-white hover:bg-red-700 transition">
                    <i class="fas fa-file-pdf mr-1"></i>Ver PDF
                </a>
                <a href="{{ route('shipping-notes.pdf.download', $shipping_note) }}"
                    class="inline-flex items-center px-3 py-2 bg-red-100 rounded-lg text-xs font-medium text-red-700 hover:bg-red-200 transition">
                    <i class="fas fa-download mr-1"></i>Descargar
                </a>
                @if ($shipping_note->isPrinted())
                    <span class="inline-flex items-center px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">
                        <i class="fas fa-print mr-1"></i>Impresa {{ $shipping_note->printed_at->format('d/m H:i') }}
                    </span>
                @endif
                @if ($shipping_note->canBeEdited())
                    <a href="{{ route('shipping-notes.edit', $shipping_note) }}"
                        class="inline-flex items-center px-3 py-2 bg-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-300 transition">
                        <i class="fas fa-edit mr-1"></i>Editar
                    </a>
                @endif
                <a href="{{ route('shipping-notes.index') }}"
                    class="inline-flex items-center px-3 py-2 bg-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-300 transition">
                    <i class="fas fa-arrow-left mr-1"></i>Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ showCancelModal: false, showAddItem: false, showAddKit: false, showAddConcept: false, showAddUrgency: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Alertas --}}
            @foreach (['success' => 'green', 'warning' => 'yellow', 'error' => 'red'] as $type => $alertColor)
                @if (session($type))
                    <div class="bg-{{ $alertColor }}-50 border-l-4 border-{{ $alertColor }}-400 p-4 rounded-lg">
                        <div class="flex">
                            <i class="fas fa-{{ $type === 'success' ? 'check' : ($type === 'warning' ? 'exclamation' : 'times') }}-circle text-{{ $alertColor }}-400 mr-3 mt-0.5"></i>
                            <p class="text-sm text-{{ $alertColor }}-700 font-medium">{{ session($type) }}</p>
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- ═══════════════════════════════════════════════════════
                 INFORMACIÓN GENERAL
                 ═══════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-info-circle mr-2 text-indigo-500"></i>Información General
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                    <div>
                        <span class="block text-gray-500 font-medium">Hospital</span>
                        <span class="text-gray-900 font-semibold">{{ $shipping_note->hospital->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 font-medium">Doctor</span>
                        <span class="text-gray-900">{{ $shipping_note->doctor->full_name ?? 'Sin asignar' }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 font-medium">Fecha de Cirugía</span>
                        <span class="text-gray-900">{{ $shipping_note->surgery_date?->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 font-medium">Razón Social</span>
                        <span class="text-gray-900">{{ $shipping_note->billingLegalEntity->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 font-medium">Checklist</span>
                        <span class="text-gray-900">{{ $shipping_note->surgicalChecklist->surgery_type ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 font-medium">Cirugía Programada</span>
                        <span class="text-gray-900">{{ $shipping_note->scheduledSurgery->code ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 font-medium">Creada por</span>
                        <span class="text-gray-900">{{ $shipping_note->createdBy->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 font-medium">Creada el</span>
                        <span class="text-gray-900">{{ $shipping_note->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                @if ($shipping_note->notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <span class="text-xs text-gray-500 font-medium">Notas:</span>
                        <p class="text-sm text-gray-700 mt-1">{{ $shipping_note->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 BOTONES DE FLUJO
                 ═══════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Línea de tiempo del estado --}}
                    <div class="flex items-center gap-1 mr-auto text-xs text-gray-500">
                        @foreach (['draft' => 'Borrador', 'confirmed' => 'Confirmada', 'sent' => 'Enviada', 'in_surgery' => 'En Cirugía', 'returned' => 'Retornada', 'completed' => 'Completada'] as $step => $stepLabel)
                            @php
                                $statusOrder = ['draft' => 0, 'confirmed' => 1, 'sent' => 2, 'in_surgery' => 3, 'returned' => 4, 'completed' => 5];
                                $currentOrder = $statusOrder[$shipping_note->status] ?? -1;
                                $stepOrder = $statusOrder[$step] ?? 0;
                                $isActive = $shipping_note->status === $step;
                                $isPast = $currentOrder > $stepOrder;
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $isActive ? 'bg-indigo-100 text-indigo-700' : ($isPast ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400') }}">
                                @if ($isPast) <i class="fas fa-check mr-0.5"></i> @endif
                                {{ $stepLabel }}
                            </span>
                            @if (!$loop->last) <i class="fas fa-chevron-right text-gray-300"></i> @endif
                        @endforeach
                    </div>

                    {{-- Acciones según estado --}}
                    @if ($shipping_note->canBeConfirmed())
                        <form method="POST" action="{{ route('shipping-notes.confirm', $shipping_note) }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition"
                                onclick="return confirm('¿Confirmar esta remisión?')">
                                <i class="fas fa-check-circle mr-1"></i>Confirmar
                            </button>
                        </form>
                    @endif

                    @if ($shipping_note->canBeSent())
                        <form method="POST" action="{{ route('shipping-notes.send', $shipping_note) }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 bg-yellow-500 text-white rounded-lg text-xs font-semibold hover:bg-yellow-600 transition"
                                onclick="return confirm('¿Enviar material a cirugía? Esto actualizará el inventario.')">
                                <i class="fas fa-truck mr-1"></i>Enviar Material
                            </button>
                        </form>
                    @endif

                    @if ($shipping_note->status === 'sent')
                        <form method="POST" action="{{ route('shipping-notes.start-surgery', $shipping_note) }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 bg-orange-500 text-white rounded-lg text-xs font-semibold hover:bg-orange-600 transition">
                                <i class="fas fa-procedures mr-1"></i>Iniciar Cirugía
                            </button>
                        </form>
                    @endif

                    @if ($shipping_note->canRegisterReturn())
                        <a href="{{ route('shipping-notes.return-form', $shipping_note) }}"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg text-xs font-semibold hover:bg-purple-700 transition">
                            <i class="fas fa-undo mr-1"></i>Registrar Retorno
                        </a>
                    @endif

                    @if ($shipping_note->status === 'returned')
                        <form method="POST" action="{{ route('shipping-notes.complete', $shipping_note) }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700 transition"
                                onclick="return confirm('¿Completar esta remisión?')">
                                <i class="fas fa-flag-checkered mr-1"></i>Completar
                            </button>
                        </form>
                    @endif

                    @if ($shipping_note->canBeEdited())
                        <form method="POST" action="{{ route('shipping-notes.reevaluate', $shipping_note) }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-xs font-medium hover:bg-gray-200 transition"
                                onclick="return confirm('¿Re-evaluar el checklist con los datos actuales de la cirugía?')">
                                <i class="fas fa-sync-alt mr-1"></i>Re-evaluar
                            </button>
                        </form>
                    @endif

                    @if ($shipping_note->canBeCancelled())
                        <button @click="showCancelModal = true"
                            class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-xs font-medium hover:bg-red-200 transition">
                            <i class="fas fa-ban mr-1"></i>Cancelar
                        </button>
                    @endif
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 EVALUACIÓN DEL CHECKLIST
                 ═══════════════════════════════════════════════════════ --}}
            @if (!empty($checklist_evaluation))
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ open: true }">
                    <button @click="open = !open"
                        class="w-full px-6 py-4 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-clipboard-check mr-2 text-green-500"></i>
                            Evaluación del Checklist
                            <span class="text-sm text-gray-500 font-normal ml-2">({{ count($checklist_evaluation) }} productos)</span>
                            @php
                                $condCount = collect($checklist_evaluation)->where('has_conditional', true)->count();
                            @endphp
                            @if ($condCount > 0)
                                <span class="ml-2 inline-flex px-2 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-700">
                                    {{ $condCount }} condicional{{ $condCount > 1 ? 'es' : '' }}
                                </span>
                            @endif
                        </h3>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-collapse>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Producto</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-20">Base</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-20">Final</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-28">Acción</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Criterio / Descripción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($checklist_evaluation as $eval)
                                        @php
                                            $hasCond = $eval['has_conditional'] ?? false;
                                            $action = $eval['action_type'] ?? null;
                                            $rowClass = match($action) {
                                                'exclude' => 'bg-red-50',
                                                'replace' => 'bg-purple-50',
                                                'add_dependency' => 'bg-blue-50',
                                                'add_product' => 'bg-green-50',
                                                'adjust_quantity' => 'bg-amber-50',
                                                default => $hasCond ? 'bg-amber-50' : '',
                                            };
                                            $actionBadge = match($action) {
                                                'exclude' => ['bg-red-100 text-red-700', 'fa-ban', 'Excluido'],
                                                'replace' => ['bg-purple-100 text-purple-700', 'fa-exchange-alt', 'Reemplazo'],
                                                'add_dependency' => ['bg-blue-100 text-blue-700', 'fa-link', 'Dependencia'],
                                                'add_product' => ['bg-green-100 text-green-700', 'fa-plus-circle', 'Adicional'],
                                                'adjust_quantity' => ['bg-amber-100 text-amber-700', 'fa-sliders-h', 'Ajuste Cant.'],
                                                default => $hasCond ? ['bg-gray-100 text-gray-600', 'fa-cog', 'Modificado'] : null,
                                            };
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ $eval['product_name'] ?? 'N/A' }}
                                                @if ($eval['is_mandatory'] ?? true)
                                                    <i class="fas fa-asterisk text-xs text-red-400 ml-1" title="Obligatorio"></i>
                                                @endif
                                                @if (($eval['source'] ?? 'base') === 'additional')
                                                    <span class="ml-1 text-xs text-purple-600">(adicional)</span>
                                                @elseif (($eval['source'] ?? 'base') === 'excluded')
                                                    <span class="ml-1 text-xs text-red-500 line-through">(excluido)</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-center">{{ $eval['base_quantity'] ?? 0 }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($hasCond && ($eval['base_quantity'] ?? 0) !== ($eval['final_quantity'] ?? 0))
                                                    <span class="inline-flex items-center gap-1">
                                                        <span class="line-through text-gray-400 text-xs">{{ $eval['base_quantity'] ?? 0 }}</span>
                                                        <i class="fas fa-arrow-right text-xs text-gray-400"></i>
                                                        <span class="px-2 py-0.5 text-sm font-bold rounded bg-amber-200 text-amber-800">
                                                            {{ $eval['final_quantity'] ?? 0 }}
                                                        </span>
                                                    </span>
                                                @else
                                                    <span class="text-sm font-medium text-gray-900">{{ $eval['final_quantity'] ?? 0 }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($actionBadge)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full {{ $actionBadge[0] }}">
                                                        <i class="fas {{ $actionBadge[1] }} text-[10px]"></i>
                                                        {{ $actionBadge[2] }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-400">Base</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-xs text-gray-600">
                                                {{ $eval['conditional_description'] ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ═══════════════════════════════════════════════════════
                 PAQUETES PRE-ARMADOS
                 ═══════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-box-open mr-2 text-blue-500"></i>
                        Paquetes Pre-Armados
                        <span class="text-sm text-gray-500 font-normal">({{ $packages->count() }})</span>
                    </h3>
                    @if ($shipping_note->canBeEdited() && $availablePackages->isNotEmpty())
                        <form method="POST" action="{{ route('shipping-notes.assign-package', $shipping_note) }}"
                            class="flex items-center gap-2">
                            @csrf
                            <select name="pre_assembled_package_id" required
                                class="rounded-lg border-gray-300 text-sm">
                                <option value="">— Asignar paquete —</option>
                                @foreach ($availablePackages as $pkg)
                                    <option value="{{ $pkg->id }}">{{ $pkg->code }} — {{ $pkg->surgeryChecklist->surgery_type ?? '' }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                class="px-3 py-2 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition">
                                <i class="fas fa-plus mr-1"></i>Asignar
                            </button>
                        </form>
                    @endif
                </div>

                @if ($packages->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach ($packages as $pkg)
                            <div class="p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <span class="text-sm font-bold text-gray-900">
                                            <i class="fas fa-box mr-1 text-blue-500"></i>
                                            {{ $pkg['package']->code ?? 'N/A' }}
                                        </span>
                                        <span class="ml-3 text-xs px-2 py-0.5 rounded-full
                                            {{ $pkg['completeness'] >= 100 ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $pkg['completeness'] }}% completo
                                        </span>
                                        <span class="ml-2 text-xs text-gray-500">{{ $pkg['items_count'] }} items</span>
                                    </div>
                                    @if ($shipping_note->canBeEdited())
                                        <form method="POST"
                                            action="{{ route('shipping-notes.remove-package', [$shipping_note, $pkg['id']]) }}"
                                            onsubmit="return confirm('¿Remover este paquete?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs">
                                                <i class="fas fa-trash mr-1"></i>Remover
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                {{-- Items faltantes --}}
                                @if (!empty($pkg['missing_items']))
                                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mt-2">
                                        <p class="text-xs font-semibold text-amber-800 mb-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Faltantes:
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($pkg['missing_items'] as $missing)
                                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">
                                                    {{ $missing['product_name'] ?? 'N/A' }}
                                                    (faltan {{ $missing['missing'] ?? 0 }})
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-box-open text-3xl text-gray-300 mb-2 block"></i>
                        <p class="text-sm">No hay paquetes asignados</p>
                    </div>
                @endif
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 KITS QUIRÚRGICOS
                 ═══════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-briefcase-medical mr-2 text-teal-500"></i>
                        Kits Quirúrgicos (Instrumental)
                        <span class="text-sm text-gray-500 font-normal">({{ $kits->count() }})</span>
                    </h3>
                    @if ($shipping_note->canBeEdited() && $availableKits->isNotEmpty())
                        <button @click="showAddKit = true"
                            class="px-3 py-2 bg-teal-600 text-white rounded-lg text-xs font-semibold hover:bg-teal-700 transition">
                            <i class="fas fa-plus mr-1"></i>Asignar Kit
                        </button>
                    @endif
                </div>

                @if ($kits->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kit</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Items</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Precio Renta</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Facturar</th>
                                    @if ($shipping_note->canBeEdited())
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($kits as $kit)
                                    <tr>
                                        <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                            <i class="fas fa-briefcase-medical mr-1 text-teal-500"></i>
                                            {{ $kit['kit']->code ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-600 text-center">{{ $kit['items_count'] }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-900 text-right font-medium">${{ number_format($kit['rental_price'], 2) }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                                {{ \App\Models\ShippingNoteKit::getStatusLabels()[$kit['status']] ?? $kit['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            @if ($kit['exclude_from_invoice'])
                                                <span class="text-xs text-amber-600"><i class="fas fa-gift mr-1"></i>Cortesía</span>
                                            @else
                                                <i class="fas fa-check text-green-500"></i>
                                            @endif
                                        </td>
                                        @if ($shipping_note->canBeEdited())
                                            <td class="px-6 py-3 text-right">
                                                <form method="POST"
                                                    action="{{ route('shipping-notes.remove-kit', [$shipping_note, $kit['id']]) }}"
                                                    onsubmit="return confirm('¿Remover este kit?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-briefcase-medical text-3xl text-gray-300 mb-2 block"></i>
                        <p class="text-sm">No hay kits asignados</p>
                    </div>
                @endif
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 TODOS LOS ITEMS
                 ═══════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list-ul mr-2 text-gray-600"></i>
                        Productos
                        <span class="text-sm text-gray-500 font-normal">({{ $stats['total_items'] }})</span>
                    </h3>
                    @if ($shipping_note->canBeEdited())
                        <button @click="showAddItem = true"
                            class="px-3 py-2 bg-gray-700 text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition">
                            <i class="fas fa-plus mr-1"></i>Agregar Producto
                        </button>
                    @endif
                </div>

                {{-- Tabs por origen --}}
                <div x-data="{ tab: 'all' }">
                    <div class="px-6 pt-3 flex gap-2 border-b border-gray-200">
                        <button @click="tab = 'all'"
                            :class="tab === 'all' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-3 py-2 text-xs font-medium border-b-2 transition">
                            Todos ({{ $stats['total_items'] }})
                        </button>
                        <button @click="tab = 'package'"
                            :class="tab === 'package' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-3 py-2 text-xs font-medium border-b-2 transition">
                            <i class="fas fa-box mr-1"></i>Paquetes ({{ $stats['items_by_origin']['package'] ?? 0 }})
                        </button>
                        <button @click="tab = 'kit'"
                            :class="tab === 'kit' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-3 py-2 text-xs font-medium border-b-2 transition">
                            <i class="fas fa-briefcase-medical mr-1"></i>Kits ({{ $stats['items_by_origin']['kit'] ?? 0 }})
                        </button>
                        <button @click="tab = 'standalone'"
                            :class="tab === 'standalone' ? 'border-gray-500 text-gray-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-3 py-2 text-xs font-medium border-b-2 transition">
                            <i class="fas fa-cube mr-1"></i>Individuales ({{ $stats['items_by_origin']['standalone'] ?? 0 }})
                        </button>
                        <button @click="tab = 'conditional'"
                            :class="tab === 'conditional' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-3 py-2 text-xs font-medium border-b-2 transition">
                            <i class="fas fa-sliders-h mr-1"></i>Condicionales ({{ $stats['items_by_origin']['conditional'] ?? 0 }})
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Producto</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Origen</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Requerido</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Enviado</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Retornado</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Usado</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Modo</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Precio</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Estado</th>
                                    @if ($shipping_note->canBeEdited())
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($shipping_note->items as $item)
                                    @php
                                        $originLabels = \App\Models\ShippingNoteItem::getOriginLabels();
                                        $billingLabels = \App\Models\ShippingNoteItem::getBillingModeLabels();
                                        $statusItemLabels = \App\Models\ShippingNoteItem::getStatusLabels();
                                        $originColors = ['package' => 'blue', 'kit' => 'teal', 'standalone' => 'gray', 'conditional' => 'amber'];
                                        $originColor = $originColors[$item->item_origin] ?? 'gray';
                                    @endphp
                                    <tr x-show="tab === 'all' || tab === '{{ $item->item_origin }}'"
                                        class="{{ $item->is_urgency ? 'bg-orange-50 border-l-4 border-orange-400' : ($item->exclude_from_invoice ? 'bg-yellow-50' : '') }}">
                                        <td class="px-6 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="text-sm text-gray-900 font-medium">{{ $item->product->name ?? 'N/A' }}</div>
                                                @if ($item->is_urgency)
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-bold rounded bg-orange-200 text-orange-800 uppercase">
                                                        <i class="fas fa-bolt"></i> Urgencia
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($item->productUnit)
                                                <div class="text-xs text-gray-500">EPC: {{ $item->productUnit->epc ?? '—' }}</div>
                                            @elseif ($item->status === 'pending')
                                                <div class="text-xs text-amber-600"><i class="fas fa-exclamation-triangle mr-1"></i>Sin unidad asignada</div>
                                            @endif
                                            @if ($item->conditional_description)
                                                <div class="text-xs text-amber-600 mt-0.5">
                                                    <i class="fas fa-sliders-h mr-1"></i>{{ $item->conditional_description }}
                                                </div>
                                            @endif
                                            @if ($item->is_urgency && $item->urgency_reason)
                                                <div class="text-xs text-orange-600 mt-0.5">
                                                    <i class="fas fa-comment-medical mr-1"></i>{{ $item->urgency_reason }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $originColor }}-100 text-{{ $originColor }}-700">
                                                {{ $originLabels[$item->item_origin] ?? $item->item_origin }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-700 text-center">{{ $item->quantity_required }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700 text-center">{{ $item->quantity_sent }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700 text-center">{{ $item->quantity_returned }}</td>
                                        <td class="px-6 py-3 text-sm text-center font-medium {{ $item->quantity_used > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                            {{ $item->quantity_used }}
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            @php
                                                $modeColors = ['sale' => 'green', 'rental' => 'blue', 'no_charge' => 'gray'];
                                            @endphp
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $modeColors[$item->billing_mode] ?? 'gray' }}-100 text-{{ $modeColors[$item->billing_mode] ?? 'gray' }}-700">
                                                {{ $billingLabels[$item->billing_mode] ?? $item->billing_mode }}
                                            </span>
                                            @if ($item->exclude_from_invoice)
                                                <span class="text-xs text-amber-600 block mt-0.5"><i class="fas fa-gift"></i> Cortesía</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-900 text-right">${{ number_format($item->total_price, 2) }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                                {{ $statusItemLabels[$item->status] ?? $item->status }}
                                            </span>
                                        </td>
                                        @if ($shipping_note->canBeEdited())
                                            <td class="px-6 py-3 text-right">
                                                @if ($item->item_origin === 'standalone')
                                                    <form method="POST"
                                                        action="{{ route('shipping-notes.remove-item', [$shipping_note, $item]) }}"
                                                        onsubmit="return confirm('¿Remover este producto?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                                            <p class="text-sm">No hay productos en esta remisión</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 CONCEPTOS DE RENTA
                 ═══════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-hand-holding-usd mr-2 text-yellow-500"></i>
                        Conceptos de Renta
                        <span class="text-sm text-gray-500 font-normal">({{ $rental_concepts->count() }})</span>
                    </h3>
                    @if ($shipping_note->canBeEdited())
                        <button @click="showAddConcept = true"
                            class="px-3 py-2 bg-yellow-500 text-white rounded-lg text-xs font-semibold hover:bg-yellow-600 transition">
                            <i class="fas fa-plus mr-1"></i>Agregar Concepto
                        </button>
                    @endif
                </div>

                @if ($rental_concepts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Concepto</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cantidad</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Precio Unit.</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Facturar</th>
                                    @if ($shipping_note->canBeEdited())
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($rental_concepts as $concept)
                                    <tr>
                                        <td class="px-6 py-3 text-sm text-gray-900">{{ $concept->concept }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700 text-center">{{ $concept->quantity }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-900 text-right">${{ number_format($concept->unit_price, 2) }}</td>
                                        <td class="px-6 py-3 text-sm font-medium text-gray-900 text-right">${{ number_format($concept->total_price, 2) }}</td>
                                        <td class="px-6 py-3 text-center">
                                            @if ($concept->exclude_from_invoice)
                                                <span class="text-xs text-amber-600"><i class="fas fa-gift"></i></span>
                                            @else
                                                <i class="fas fa-check text-green-500 text-sm"></i>
                                            @endif
                                        </td>
                                        @if ($shipping_note->canBeEdited())
                                            <td class="px-6 py-3 text-right">
                                                <form method="POST"
                                                    action="{{ route('shipping-notes.remove-rental-concept', [$shipping_note, $concept]) }}"
                                                    onsubmit="return confirm('¿Remover este concepto?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-hand-holding-usd text-3xl text-gray-300 mb-2 block"></i>
                        <p class="text-sm">No hay conceptos de renta</p>
                    </div>
                @endif
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 TOTALES FINANCIEROS
                 ═══════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-calculator mr-2 text-green-500"></i>Totales Financieros
                    </h3>
                    <div class="flex gap-2">
                        @if ($shipping_note->canBeEdited())
                            <form method="POST" action="{{ route('shipping-notes.recalculate', $shipping_note) }}" class="inline">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-xs font-medium hover:bg-gray-200 transition">
                                    <i class="fas fa-sync-alt mr-1"></i>Recalcular
                                </button>
                            </form>
                            <button @click="showAddUrgency = true"
                                class="px-3 py-1.5 bg-orange-100 text-orange-700 rounded-lg text-xs font-medium hover:bg-orange-200 transition">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Agregar Urgencia
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Desglose por tipo --}}
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <span class="block text-xs text-green-600 font-medium uppercase">Ventas</span>
                        <span class="text-xl font-bold text-green-800">${{ number_format($totals['sales'], 2) }}</span>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <span class="block text-xs text-blue-600 font-medium uppercase">Rentas Items</span>
                        <span class="text-xl font-bold text-blue-800">${{ number_format($totals['item_rentals'], 2) }}</span>
                    </div>
                    <div class="bg-teal-50 rounded-lg p-4 text-center">
                        <span class="block text-xs text-teal-600 font-medium uppercase">Rentas Kits</span>
                        <span class="text-xl font-bold text-teal-800">${{ number_format($totals['kit_rentals'], 2) }}</span>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 text-center">
                        <span class="block text-xs text-yellow-600 font-medium uppercase">Conceptos Renta</span>
                        <span class="text-xl font-bold text-yellow-800">${{ number_format($totals['rental_concepts'], 2) }}</span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <span class="block text-xs text-gray-600 font-medium uppercase">Items Urgencia</span>
                        <span class="text-xl font-bold text-orange-700">{{ $shipping_note->items()->urgency()->count() }}</span>
                    </div>
                </div>

                {{-- Subtotal + IVA + Total --}}
                <div class="flex justify-end">
                    <div class="w-full max-w-sm space-y-2">
                        {{-- Subtotal --}}
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Subtotal</span>
                            <span class="text-lg font-semibold text-gray-900">${{ number_format((float) $shipping_note->subtotal, 2) }}</span>
                        </div>

                        {{-- IVA editable --}}
                        <div class="flex justify-between items-center py-2 border-b border-gray-200"
                             x-data="{ editing: false, rate: {{ (float) $shipping_note->tax_rate * 100 }} }">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-600">I.V.A.</span>
                                @if ($shipping_note->canBeEdited())
                                    <template x-if="!editing">
                                        <button @click="editing = true" class="text-xs text-cyan-600 hover:underline">
                                            (<span x-text="rate"></span>%) <i class="fas fa-pencil-alt ml-0.5"></i>
                                        </button>
                                    </template>
                                    <template x-if="editing">
                                        <form method="POST" action="{{ route('shipping-notes.update-tax-rate', $shipping_note) }}"
                                              class="flex items-center gap-1">
                                            @csrf @method('PUT')
                                            <input type="number" name="tax_rate_pct" x-model="rate"
                                                   class="w-16 text-xs rounded border-gray-300 py-1 px-2" step="0.01" min="0" max="100">
                                            <span class="text-xs text-gray-500">%</span>
                                            <input type="hidden" name="tax_rate" :value="rate / 100">
                                            <button type="submit" class="text-xs text-green-600 hover:text-green-800">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" @click="editing = false" class="text-xs text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </template>
                                @else
                                    <span class="text-xs text-gray-500">({{ round((float) $shipping_note->tax_rate * 100) }}%)</span>
                                @endif
                            </div>
                            <span class="text-lg font-semibold text-gray-900">${{ number_format((float) $shipping_note->tax_amount, 2) }}</span>
                        </div>

                        {{-- Total --}}
                        <div class="flex justify-between items-center py-3 bg-cyan-50 rounded-lg px-4 border-2 border-cyan-200">
                            <span class="text-sm font-bold text-cyan-800 uppercase">Total</span>
                            <span class="text-2xl font-bold text-cyan-800">${{ number_format((float) $shipping_note->grand_total, 2) }}</span>
                        </div>

                        {{-- Importe con letra --}}
                        <p class="text-xs text-gray-500 italic pt-1">
                            {{ $shipping_note->amount_in_words }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════
             MODALES
             ═══════════════════════════════════════════════════════ --}}

        {{-- Modal: Cancelar remisión --}}
        <div x-show="showCancelModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape="showCancelModal = false">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6" @click.outside="showCancelModal = false">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-ban text-red-500 mr-2"></i>Cancelar Remisión
                </h3>
                <form method="POST" action="{{ route('shipping-notes.cancel', $shipping_note) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Razón de cancelación</label>
                        <textarea name="cancellation_reason" rows="3"
                            class="w-full rounded-lg border-gray-300 text-sm"
                            placeholder="Describe la razón..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showCancelModal = false"
                            class="px-4 py-2 bg-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-300 transition">
                            No, volver
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
                            Sí, cancelar remisión
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Agregar Kit --}}
        <div x-show="showAddKit" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape="showAddKit = false">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6" @click.outside="showAddKit = false">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-briefcase-medical text-teal-500 mr-2"></i>Asignar Kit Quirúrgico
                </h3>
                <form method="POST" action="{{ route('shipping-notes.assign-kit', $shipping_note) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kit</label>
                            <select name="surgical_kit_id" required class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="">— Seleccionar —</option>
                                @foreach ($availableKits as $kit)
                                    <option value="{{ $kit->id }}">{{ $kit->code }} — {{ $kit->surgery_type ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Precio de Renta</label>
                            <input type="number" name="rental_price" step="0.01" min="0" value="0" required
                                class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="exclude_from_invoice" value="1" id="kit_exclude"
                                class="rounded border-gray-300 text-indigo-600">
                            <label for="kit_exclude" class="text-sm text-gray-700">Cortesía (no facturar)</label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showAddKit = false"
                            class="px-4 py-2 bg-gray-200 rounded-lg text-sm text-gray-700">Cancelar</button>
                        <button type="submit"
                            class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm font-semibold hover:bg-teal-700 transition">
                            Asignar Kit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Agregar Producto Individual --}}
        <div x-show="showAddItem" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape="showAddItem = false"
            x-data="productSearch()">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 p-6" @click.outside="showAddItem = false">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-cube text-gray-600 mr-2"></i>Agregar Producto Individual
                </h3>
                <form method="POST" action="{{ route('shipping-notes.add-item', $shipping_note) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar producto (nombre, código o EPC)</label>
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="search()"
                                class="w-full rounded-lg border-gray-300 text-sm" placeholder="Escribe para buscar...">
                            <div x-show="results.length > 0"
                                class="mt-1 max-h-48 overflow-y-auto border border-gray-200 rounded-lg divide-y">
                                <template x-for="product in results" :key="product.id">
                                    <button type="button" @click="selectProduct(product)"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-indigo-50 transition">
                                        <div class="font-medium text-gray-900" x-text="product.name"></div>
                                        <div class="text-xs text-gray-500">
                                            <span x-text="product.code"></span> · EPC: <span x-text="product.epc || '—'"></span>
                                            · $<span x-text="Number(product.list_price).toFixed(2)"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="product_unit_id" x-model="selectedProductId" required>
                        </div>
                        <div x-show="selectedProduct" class="bg-indigo-50 rounded-lg p-3">
                            <p class="text-sm font-medium text-indigo-900" x-text="selectedProduct?.name"></p>
                            <p class="text-xs text-indigo-600" x-text="'EPC: ' + (selectedProduct?.epc || '—')"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                                <input type="number" name="quantity" min="1" value="1" required
                                    class="w-full rounded-lg border-gray-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Modo Facturación</label>
                                <select name="billing_mode" required class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="sale">Venta</option>
                                    <option value="rental">Renta</option>
                                    <option value="no_charge">Sin Cargo</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Precio Unitario</label>
                            <input type="number" name="unit_price" step="0.01" min="0"
                                x-model="selectedProduct?.list_price || 0"
                                class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="exclude_from_invoice" value="1" id="item_exclude"
                                class="rounded border-gray-300 text-indigo-600">
                            <label for="item_exclude" class="text-sm text-gray-700">Cortesía (no facturar)</label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showAddItem = false"
                            class="px-4 py-2 bg-gray-200 rounded-lg text-sm text-gray-700">Cancelar</button>
                        <button type="submit" :disabled="!selectedProductId"
                            class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-semibold hover:bg-gray-800 transition disabled:opacity-50">
                            Agregar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Agregar Concepto de Renta --}}
        <div x-show="showAddConcept" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape="showAddConcept = false">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6" @click.outside="showAddConcept = false">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-hand-holding-usd text-yellow-500 mr-2"></i>Agregar Concepto de Renta
                </h3>
                <form method="POST" action="{{ route('shipping-notes.add-rental-concept', $shipping_note) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Concepto</label>
                            <input type="text" name="concept" required
                                class="w-full rounded-lg border-gray-300 text-sm"
                                placeholder="Ej: Renta de motor, Uso de sala...">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                                <input type="number" name="quantity" min="1" value="1" required
                                    class="w-full rounded-lg border-gray-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Precio Unitario</label>
                                <input type="number" name="unit_price" step="0.01" min="0" value="0" required
                                    class="w-full rounded-lg border-gray-300 text-sm">
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="exclude_from_invoice" value="1" id="concept_exclude"
                                class="rounded border-gray-300 text-indigo-600">
                            <label for="concept_exclude" class="text-sm text-gray-700">Cortesía (no facturar)</label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                            <textarea name="notes" rows="2"
                                class="w-full rounded-lg border-gray-300 text-sm"
                                placeholder="Observaciones..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showAddConcept = false"
                            class="px-4 py-2 bg-gray-200 rounded-lg text-sm text-gray-700">Cancelar</button>
                        <button type="submit"
                            class="px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm font-semibold hover:bg-yellow-600 transition">
                            Agregar Concepto
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Agregar Item de Urgencia --}}
        <div x-show="showAddUrgency" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape="showAddUrgency = false">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 p-6" @click.outside="showAddUrgency = false"
                 x-data="productSearch()">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>Agregar Item de Urgencia
                </h3>
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mb-4">
                    <p class="text-xs text-orange-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Los items de urgencia se marcan visualmente en la remisión y el PDF. Usa esto para productos solicitados de última hora.
                    </p>
                </div>
                <form method="POST" action="{{ route('shipping-notes.add-item', $shipping_note) }}">
                    @csrf
                    <input type="hidden" name="is_urgency" value="1">
                    <input type="hidden" name="product_unit_id" x-model="selectedProductId">

                    <div class="space-y-4">
                        {{-- Búsqueda de producto --}}
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="search()"
                                class="w-full rounded-lg border-gray-300 text-sm"
                                placeholder="Buscar por nombre o código...">
                            <div x-show="results.length > 0"
                                class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-40 overflow-y-auto">
                                <template x-for="product in results" :key="product.id">
                                    <button type="button" @click="selectProduct(product)"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 border-b border-gray-100">
                                        <span class="font-medium" x-text="product.code"></span> —
                                        <span x-text="product.name"></span>
                                        <span class="text-xs text-gray-400 ml-1" x-text="'$' + product.list_price"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                                <input type="number" name="quantity" value="1" min="1"
                                    class="w-full rounded-lg border-gray-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Precio Unitario</label>
                                <input type="number" name="unit_price" step="0.01" min="0"
                                    class="w-full rounded-lg border-gray-300 text-sm"
                                    :value="selectedProduct?.list_price ?? 0">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modo de cobro</label>
                            <select name="billing_mode" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="sale">Venta</option>
                                <option value="rental">Renta</option>
                                <option value="no_charge">Sin Cargo</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de urgencia</label>
                            <textarea name="urgency_reason" rows="2"
                                class="w-full rounded-lg border-gray-300 text-sm"
                                placeholder="Ej: Solicitado por el Dr. durante preparación..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-5">
                        <button type="button" @click="showAddUrgency = false"
                            class="px-4 py-2 bg-gray-200 rounded-lg text-sm text-gray-700">Cancelar</button>
                        <button type="submit"
                            class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-semibold hover:bg-orange-600 transition">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Agregar Urgencia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function productSearch() {
            return {
                searchQuery: '',
                results: [],
                selectedProduct: null,
                selectedProductId: '',

                async search() {
                    if (this.searchQuery.length < 2) {
                        this.results = [];
                        return;
                    }
                    try {
                        const response = await fetch(
                            `{{ route('shipping-notes.api.search-products') }}?q=${encodeURIComponent(this.searchQuery)}`,
                            { headers: { 'Accept': 'application/json' } }
                        );
                        if (response.ok) {
                            this.results = await response.json();
                        }
                    } catch (error) {
                        console.error('Error buscando productos:', error);
                    }
                },

                selectProduct(product) {
                    this.selectedProduct = product;
                    this.selectedProductId = product.id;
                    this.results = [];
                    this.searchQuery = product.name;
                },
            };
        }
    </script>
    @endpush
</x-app-layout>