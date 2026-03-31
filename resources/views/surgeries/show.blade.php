<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold tracking-wider">
                        {{ $surgery->code }}
                    </span>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ $surgery->checklist->name }}
                    </h2>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    <i class="far fa-clock mr-1"></i> Creada el {{ $surgery->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('surgeries.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>

                @if($surgery->status === 'scheduled')
                    <a href="{{ route('surgeries.preparations.selectPackage', $surgery) }}" 
                    class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all transform hover:scale-105">
                        <i class="fas fa-play-circle mr-2 text-lg"></i> INICIAR PREPARACIÓN
                    </a>
                @elseif($surgery->status === 'in_preparation' && $surgery->preparation)
                    @php
                        $prepRoute = match($surgery->preparation->status) {
                            'picking' => 'surgeries.preparations.picking',
                            default => 'surgeries.preparations.compare',
                        };
                    @endphp
                    <a href="{{ route($prepRoute, $surgery) }}" 
                    class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all transform hover:scale-105">
                        <i class="fas fa-arrow-circle-right mr-2 text-lg"></i> CONTINUAR PREPARACIÓN
                    </a>
                @elseif($surgery->status === 'ready')
                    <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 text-sm font-bold rounded-lg">
                        <i class="fas fa-check-circle mr-2"></i> PREPARACIÓN LISTA
                    </span>
                @endif
                
                @if($surgery->canBeEdited())
                    <a href="{{ route('surgeries.edit', $surgery) }}" class="p-2 text-gray-400 hover:text-blue-600 transition">
                        <i class="fas fa-edit"></i>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Estado Actual -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Estado de la Cirugía</h3>
                        <div class="flex items-center space-x-4">
                            @php
                                $statusConfig = [
                                    'scheduled'      => ['label' => 'Agendada',       'icon' => 'calendar',    'description' => 'Esperando inicio de preparación'],
                                    'in_preparation' => ['label' => 'En Preparación', 'icon' => 'spinner',     'description' => 'Surtiendo productos necesarios'],
                                    'ready'          => ['label' => 'Lista',           'icon' => 'check-circle','description' => 'Preparación completa'],
                                    'in_surgery'     => ['label' => 'En Cirugía',      'icon' => 'procedures', 'description' => 'Cirugía en proceso'],
                                    'completed'      => ['label' => 'Completada',      'icon' => 'check',       'description' => 'Cirugía finalizada'],
                                    'cancelled'      => ['label' => 'Cancelada',       'icon' => 'times-circle','description' => 'Cirugía cancelada'],
                                ];
                                $config = $statusConfig[$surgery->status] ?? ['label' => $surgery->status, 'icon' => 'circle', 'description' => ''];
                            @endphp
                            <div class="bg-white bg-opacity-20 rounded-full p-4">
                                <i class="fas fa-{{ $config['icon'] }} text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $config['label'] }}</p>
                                <p class="text-sm text-indigo-100">{{ $config['description'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-indigo-100">Programada para</p>
                        <p class="text-2xl font-bold">{{ $surgery->surgery_datetime->format('d/m/Y') }}</p>
                        <p class="text-lg">{{ $surgery->surgery_datetime->format('H:i') }} hrs</p>
                    </div>
                </div>
            </div>

            <!-- Información del Paciente y Hospital -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600 mr-3">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4 class="font-bold text-gray-700">Paciente</h4>
                    </div>
                    <p class="text-lg font-semibold text-gray-900">{{ $surgery->patient_name }}</p>
                    <span class="inline-block mt-2 px-2 py-1 text-[10px] uppercase font-bold rounded">
                        Modalidad: {{ $surgery->modality->name }}
                    </span>
                </div>

                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600 mr-3">
                            <i class="fas fa-hospital-user"></i>
                        </div>
                        <h4 class="font-bold text-gray-700">Equipo Médico</h4>
                    </div>
                    <p class="text-sm font-medium text-gray-900"><span class="text-gray-500 font-normal">Dr.</span><span class="capitalize"> {{ $surgery->doctor->first_name }} {{ $surgery->doctor->last_name }}</span></p>
                    <p class="text-sm text-gray-600 mt-1 capitalize"><i class="fas fa-hospital mr-1 text-gray-400"></i> {{ Str::title($surgery->hospital->name) }}</p>
                </div>

                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-indigo-500">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center text-gray-600 mr-3">
                            <i class="far fa-calendar-alt"></i>
                        </div>
                        <h4 class="font-bold text-gray-700">Programación</h4>
                    </div>
                    <p class="text-xl font-bold text-gray-900">{{ $surgery->surgery_datetime->format('d M, Y') }}</p>
                    <p class="text-lg text-indigo-600 font-medium">{{ $surgery->surgery_datetime->format('H:i') }} <span class="text-xs text-gray-500">hrs</span></p>
                </div>
            </div>

            <!-- Check List y Detalles -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-clipboard-list mr-2 text-indigo-600"></i>
                        Check List de la Cirugía
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Tipo de Cirugía</dt>
                            <dd class="text-sm text-gray-900">{{ $surgery->checklist->surgery_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Código de Cirugía</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $surgery->code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Agendado Por</dt>
                            <dd class="text-sm text-gray-900">{{ $surgery->scheduler->name }}</dd>
                        </div>
                        @if($surgery->surgery_notes)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 mb-1">Notas</dt>
                            <dd class="text-sm text-gray-900">{{ $surgery->surgery_notes }}</dd>
                        </div>
                        @endif

                        @if($surgery->additionalItems->isNotEmpty())
    
                            <div class="md:col-span-2 mt-4 pt-4 border-t border-gray-200">
                                <h4 class="text-sm font-bold text-gray-900 mb-4 uppercase tracking-wider">
                                    <i class="fas fa-plus-square text-purple-600 mr-1"></i> Adicionales a Check List
                                </h4>
                                
                                <div class="space-y-3">
                                    @foreach($surgery->additionalItems as $extra)
                                        @php
                                            $type = 'Desconocido';
                                            $icon = 'fa-box';
                                            $code = '';
                                            $name = '';
                                            $contents = collect();

                                            if ($extra->product_id) {
                                                $type = 'Insumo';
                                                $icon = 'fa-box';
                                                $code = $extra->product->code ?? 'S/C';
                                                $name = $extra->product->name ?? 'Producto sin nombre';
                                            } elseif ($extra->instrument_id) {
                                                $type = 'Instrumento';
                                                $icon = 'fa-tools';
                                                $code = $extra->instrument->serial_number ?? 'S/N';
                                                $name = $extra->instrument->name ?? 'Instrumento sin nombre';
                                            } elseif ($extra->instrument_kit_id) {
                                                $type = 'Kit Quirúrgico';
                                                $icon = 'fa-briefcase-medical';
                                                $code = $extra->instrumentKit->code ?? 'S/C';
                                                $name = $extra->instrumentKit->name ?? 'Kit sin nombre';
                                                $contents = $extra->instrumentKit->instruments ?? collect();
                                            }
                                        @endphp

                                        <div x-data="{ expanded: false }" class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                                            
                                            <div class="flex items-center justify-between p-4 hover:bg-purple-50 transition-colors">
                                                
                                                <div class="flex items-start flex-1">
                                                    <div class="flex-shrink-0 mt-1 h-10 w-10 bg-purple-50 text-purple-600 border border-purple-100 rounded-lg flex items-center justify-center">
                                                        <i class="fas {{ $icon }} text-sm"></i>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">{{ $type }}</div>
                                                        <div class="text-sm font-bold text-gray-900">{{ $code }}</div>
                                                        <div class="text-sm font-medium text-gray-700">{{ $name }}</div>
                                                        
                                                        @if($extra->reason)
                                                            <div class="text-xs text-gray-500 italic mt-1"><i class="fas fa-comment-dots text-gray-400 mr-1"></i> {{ $extra->reason }}</div>
                                                        @endif
                                                        
                                                        @if($type === 'Kit Quirúrgico' && $contents->isNotEmpty())
                                                            <button type="button" @click="expanded = !expanded" 
                                                                    class="mt-2 text-[11px] font-semibold text-purple-600 hover:text-purple-800 focus:outline-none flex items-center transition-colors">
                                                                <i class="fas mr-1" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                                                <span x-text="expanded ? 'Ocultar piezas del kit' : 'Ver piezas del kit'"></span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="ml-4 flex flex-col items-end justify-center">
                                                    <div class="text-[10px] text-gray-500 font-medium mb-1 uppercase">Cantidad</div>
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-700 text-sm font-bold shadow-sm">
                                                        {{ $extra->quantity }}
                                                    </span>
                                                </div>
                                            </div>

                                            @if($type === 'Kit Quirúrgico' && $contents->isNotEmpty())
                                                <div x-show="expanded" x-transition.opacity style="display: none;" class="border-t border-purple-100 bg-purple-50/30">
                                                    <div class="p-4 pl-16"> <h5 class="text-xs font-bold text-purple-800 mb-3 uppercase tracking-wider">
                                                            <i class="fas fa-list-ul mr-1"></i> Contenido del Kit
                                                        </h5>
                                                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-4 text-sm text-gray-700">
                                                            @foreach($contents as $inst)
                                                                <li class="flex items-start">
                                                                    <i class="fas fa-check text-purple-400 mt-1 mr-2 text-[10px]"></i>
                                                                    <span><span class="font-mono text-xs text-gray-500">{{ $inst->serial_number }}</span> — {{ $inst->name }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif
                                        </div> @endforeach
                                </div>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- ============================================================
                 TABLA: Material Quirúrgico con Condicionales y Existencias
                 ============================================================ --}}
            <div x-data="stockModal()" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-boxes mr-2 text-indigo-600"></i>
                            Material Quirúrgico Requerido
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5 uppercase tracking-wider font-semibold">
                            Basado en protocolos de {{ $surgery->hospital->name }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($excludedItems->isNotEmpty())
                            <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-1 rounded-md">
                                {{ $excludedItems->count() }} EXCLUIDO{{ $excludedItems->count() > 1 ? 'S' : '' }}
                            </span>
                        @endif
                        <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-1 rounded-md">
                            {{ count($checklistItems) }} ÍTEMS REQUERIDOS
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-white">
                                <th class="px-6 py-4 text-left   text-xs font-bold text-gray-400 uppercase tracking-widest">Descripción del Producto</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Base</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Final</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Condicional Aplicado</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Existencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">

                            {{-- ═══ ITEMS REQUERIDOS (qty > 0) ═══ --}}
                            @forelse($checklistItems as $data)
                                @php
                                    $item          = $data['item'];
                                    $baseQty       = $data['base_quantity'];
                                    $qty           = $data['adjusted_quantity'];
                                    $hasCond       = $data['has_conditional'];
                                    $appliedCond   = $data['conditional'] ?? null;
                                    $source        = $data['source'] ?? 'base';
                                    $isAdditional  = in_array($source, ['conditional', 'additional', 'extra']);
                                    $stock   = $stockMap[$data['product_id']] ?? 0;
                                    $stockOk = $stock >= $qty;

                                    // Color del fondo por tipo de condicional aplicado
                                    $rowBg = '';
                                    if ($appliedCond) {
                                        $rowBg = match($appliedCond->action_type) {
                                            'adjust_quantity' => 'bg-amber-50/50',
                                            'add_dependency' => 'bg-blue-50/50',
                                            'add_product' => 'bg-purple-50/50',
                                            default => 'bg-gray-50/30',
                                        };
                                    }
                                @endphp
                                <tr class="hover:bg-blue-50/30 transition-colors {{ $rowBg }}">

                                    {{-- Producto --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-9 w-9 {{ $isAdditional ? 'bg-purple-50 text-purple-600 border-purple-100' : 'bg-blue-50 text-blue-600 border-blue-100' }} rounded-lg flex items-center justify-center border">
                                                <i class="fas {{ $isAdditional ? 'fa-hand-holding-medical' : 'fa-box' }} text-sm"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900">{{ $item->product->code }}</div>
                                                <div class="text-[11px] text-gray-400 font-mono tracking-tighter">{{ $item->product->name }}</div>
                                                @if($isAdditional)
                                                    <span class="text-[10px] text-purple-600 font-medium">(Producto adicional)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Cantidad Base --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm text-gray-400">{{ $baseQty }}</span>
                                    </td>

                                    {{-- Cantidad Final --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($hasCond && $baseQty !== $qty)
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="text-xs text-gray-400 line-through">{{ $baseQty }}</span>
                                                <i class="fas fa-arrow-right text-[10px] text-gray-300"></i>
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-sm font-black text-amber-700 border border-amber-200">
                                                    {{ $qty }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-sm font-black text-gray-700 border border-gray-200">
                                                {{ $qty }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Condicional APLICADO (solo el que matchea, no todos) --}}
                                    <td class="px-6 py-4">
                                        @if($appliedCond)
                                            @php
                                                $condStyle = match($appliedCond->action_type) {
                                                    'adjust_quantity' => ['bg-amber-50 border-amber-200', 'text-amber-700'],
                                                    'add_product'     => ['bg-purple-50 border-purple-200', 'text-purple-700'],
                                                    'add_dependency'  => ['bg-blue-50 border-blue-200', 'text-blue-700'],
                                                    'replace'         => ['bg-orange-50 border-orange-200', 'text-orange-700'],
                                                    'exclude'         => ['bg-red-50 border-red-200', 'text-red-700'],
                                                    default           => ['bg-gray-50 border-gray-200', 'text-gray-700'],
                                                };
                                            @endphp
                                            <div class="w-full max-w-xs mx-auto">
                                                <div class="flex items-start gap-1.5 px-2.5 py-1.5 rounded-lg border {{ $condStyle[0] }}">
                                                    <div class="min-w-0">
                                                        <p class="text-[10px] font-bold uppercase tracking-wide leading-tight {{ $condStyle[1] }}">
                                                            @switch($appliedCond->action_type)
                                                                @case('adjust_quantity')
                                                                    <i class="fas fa-edit mr-0.5"></i> Ajuste: {{ $appliedCond->quantity_override }} uds.
                                                                    @break
                                                                @case('add_product')
                                                                    <i class="fas fa-plus-circle mr-0.5"></i> +{{ $appliedCond->additional_quantity }} uds.
                                                                    @break
                                                                @case('add_dependency')
                                                                    <i class="fas fa-link mr-0.5"></i> Dependencia ×{{ $appliedCond->dependency_quantity }}
                                                                    @break
                                                                @case('replace')
                                                                    <i class="fas fa-exchange-alt mr-0.5"></i> Reemplazado
                                                                    @break
                                                                @case('exclude')
                                                                    <i class="fas fa-times-circle mr-0.5"></i> Excluido
                                                                    @break
                                                            @endswitch
                                                        </p>
                                                        {{-- Criterios que activaron el condicional --}}
                                                        <p class="text-[10px] mt-0.5 leading-tight truncate text-gray-500">
                                                            @if($appliedCond->doctor)
                                                                Dr. {{ $appliedCond->doctor->first_name }} {{ $appliedCond->doctor->last_name }}
                                                            @endif
                                                            @if($appliedCond->hospital)
                                                                @if($appliedCond->doctor) · @endif
                                                                {{ $appliedCond->hospital->name }}
                                                            @endif
                                                            @if($appliedCond->modality)
                                                                @if($appliedCond->doctor || $appliedCond->hospital) · @endif
                                                                {{ $appliedCond->modality->name }}
                                                            @endif
                                                        </p>
                                                        @if($appliedCond->targetProduct)
                                                            <p class="text-[10px] mt-0.5 truncate italic text-gray-400">
                                                                → {{ $appliedCond->targetProduct->name }}
                                                            </p>
                                                        @endif
                                                        @if($appliedCond->exclude_from_invoice)
                                                            <span class="inline-flex items-center mt-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-700">
                                                                <i class="fas fa-gift mr-0.5"></i> Sin cargo
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center">
                                                <span class="text-xs text-gray-300 italic">— Estándar —</span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Existencia --}}
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            type="button"
                                            @click="openModal({
                                                productId:   {{ $item->product->id }},
                                                productName: '{{ addslashes($item->product->name) }}',
                                                productSku:  '{{ $item->product->code ?? '' }}',
                                                stock:       {{ $stock }},
                                                required:    {{ $qty }}
                                            })"
                                            class="group inline-flex flex-col items-center gap-0.5 transition-all hover:scale-105 focus:outline-none"
                                            title="Ver detalle de existencia"
                                        >
                                            <span class="inline-flex items-center justify-center min-w-[2.5rem] h-9 px-2 rounded-lg text-sm font-black border-2 transition-all
                                                {{ $stockOk
                                                    ? 'bg-green-50 text-green-700 border-green-300 group-hover:bg-green-100'
                                                    : 'bg-red-50 text-red-700 border-red-300 group-hover:bg-red-100 animate-pulse' }}">
                                                {{ $stock }}
                                            </span>
                                            <span class="text-[9px] font-semibold uppercase tracking-wide
                                                {{ $stockOk ? 'text-green-500' : 'text-red-400' }}">
                                                {{ $stockOk ? 'OK' : 'BAJO' }}
                                            </span>
                                        </button>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        <i class="fas fa-exclamation-circle text-4xl mb-3 block"></i>
                                        <p class="text-sm font-medium">No hay productos requeridos para esta configuración.</p>
                                    </td>
                                </tr>
                            @endforelse

                            {{-- ═══ ITEMS EXCLUIDOS/REEMPLAZADOS (qty = 0 por condicional) ═══ --}}
                            @if($excludedItems->isNotEmpty())
                                <tr class="bg-gray-50">
                                    <td colspan="5" class="px-6 py-2">
                                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-ban mr-1 text-red-400"></i>
                                            Productos Excluidos por Condicionales
                                        </span>
                                    </td>
                                </tr>
                                @foreach($excludedItems as $excl)
                                    @php
                                        $exclItem = $excl['item'];
                                        $exclCond = $excl['conditional'];
                                    @endphp
                                    <tr class="bg-red-50/40 opacity-70">
                                        <td class="px-6 py-3">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-9 w-9 bg-red-50 text-red-400 border-red-100 rounded-lg flex items-center justify-center border">
                                                    <i class="fas fa-ban text-sm"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-bold text-gray-500 line-through">{{ $exclItem->product->code }}</div>
                                                    <div class="text-[11px] text-gray-400 font-mono tracking-tighter line-through">{{ $exclItem->product->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="text-sm text-gray-400 line-through">{{ $excl['base_quantity'] }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-sm font-black text-red-500 border border-red-200">
                                                0
                                            </span>
                                        </td>
                                        <td class="px-6 py-3">
                                            <div class="w-full max-w-xs mx-auto">
                                                <div class="flex items-start gap-1.5 px-2.5 py-1.5 rounded-lg border bg-red-50 border-red-200">
                                                    <div class="min-w-0">
                                                        <p class="text-[10px] font-bold text-red-700 uppercase">
                                                            @if($exclCond?->action_type === 'replace')
                                                                <i class="fas fa-exchange-alt mr-0.5"></i> REEMPLAZADO
                                                            @else
                                                                <i class="fas fa-ban mr-0.5"></i> EXCLUIDO
                                                            @endif
                                                        </p>
                                                        <p class="text-[10px] text-red-500 mt-0.5 truncate">{{ $excl['conditional_description'] }}</p>
                                                        @if($exclCond?->targetProduct)
                                                            <p class="text-[10px] mt-0.5 italic text-orange-600">
                                                                → Usar: {{ $exclCond->targetProduct->name }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="text-xs text-gray-300">N/A</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                     MODAL DE EXISTENCIAS — Alpine.js (dentro del mismo x-data)
                     ══════════════════════════════════════════════════════════ --}}
                <div x-show="isOpen"
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     style="display:none;">

                    {{-- Overlay --}}
                    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                         @click="closeModal()"></div>

                    {{-- Panel --}}
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                             @click.away="closeModal()"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">

                            {{-- Header --}}
                            <div class="px-6 py-4"
                                 :class="isLow ? 'bg-gradient-to-r from-red-600 to-rose-600' : 'bg-gradient-to-r from-green-600 to-emerald-600'">
                                <div class="flex items-center justify-between text-white">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-warehouse text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold opacity-80 uppercase tracking-wide">Existencia en Almacén</p>
                                            <p class="text-base font-bold leading-tight" x-text="current.productName"></p>
                                        </div>
                                    </div>
                                    <button @click="closeModal()" class="text-white hover:text-gray-200 transition-colors p-1">
                                        <i class="fas fa-times text-lg"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Body --}}
                            <div class="px-6 py-5 space-y-4">

                                {{-- SKU --}}
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Referencia (SKU)</span>
                                    <span class="font-mono font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded" x-text="current.productSku || 'Sin SKU'"></span>
                                </div>

                                {{-- Comparativa Stock vs Requerido --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center p-4 rounded-xl border-2"
                                         :class="isLow ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200'">
                                        <p class="text-3xl font-black"
                                           :class="isLow ? 'text-red-600' : 'text-green-600'"
                                           x-text="current.stock"></p>
                                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500 mt-1">En almacén</p>
                                    </div>
                                    <div class="text-center p-4 rounded-xl border-2 bg-indigo-50 border-indigo-200">
                                        <p class="text-3xl font-black text-indigo-600" x-text="current.required"></p>
                                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500 mt-1">Requerido</p>
                                    </div>
                                </div>

                                {{-- Barra de progreso --}}
                                <div>
                                    <div class="flex justify-between text-xs font-semibold mb-1.5"
                                         :class="isLow ? 'text-red-600' : 'text-green-600'">
                                        <span x-text="isLow ? 'Stock insuficiente' : 'Stock suficiente'"></span>
                                        <span x-text="Math.min(100, Math.round((current.stock / current.required) * 100)) + '%'"></span>
                                    </div>
                                    <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500"
                                             :class="isLow ? 'bg-red-500' : 'bg-green-500'"
                                             :style="`width: ${Math.min(100, Math.round((current.stock / current.required) * 100))}%`">
                                        </div>
                                    </div>
                                </div>

                                {{-- Alerta si bajo --}}
                                <div x-show="isLow"
                                     class="flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
                                    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 flex-shrink-0"></i>
                                    <div>
                                        <p class="text-sm font-bold text-red-700">Stock insuficiente</p>
                                        <p class="text-xs text-red-600 mt-0.5">
                                            Faltan <span class="font-black" x-text="current.required - current.stock"></span> unidades para completar esta cirugía.
                                        </p>
                                    </div>
                                </div>

                                {{-- OK --}}
                                <div x-show="!isLow"
                                     class="flex items-start gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl">
                                    <i class="fas fa-check-circle text-green-500 mt-0.5 flex-shrink-0"></i>
                                    <div>
                                        <p class="text-sm font-bold text-green-700">Stock disponible</p>
                                        <p class="text-xs text-green-600 mt-0.5">
                                            Hay <span class="font-black" x-text="current.stock - current.required"></span> unidades de excedente en almacén.
                                        </p>
                                    </div>
                                </div>

                            </div>

                            {{-- Footer --}}
                            <div class="px-6 pb-5">
                                <button @click="closeModal()"
                                        class="w-full py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Preparación (si existe) -->
            @if($surgery->preparation)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-tasks mr-2 text-purple-600"></i>
                        Estado de la Preparación
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ $surgery->preparation->getCompletenessPercentage() }}%</div>
                            <div class="text-sm text-gray-600 mt-1">Completitud</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $surgery->preparation->items->count() }}</div>
                            <div class="text-sm text-gray-600 mt-1">Items Totales</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ $surgery->preparation->items->where('status', 'complete')->count() }}</div>
                            <div class="text-sm text-gray-600 mt-1">Completos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600">{{ $surgery->preparation->items->where('status', 'missing')->count() }}</div>
                            <div class="text-sm text-gray-600 mt-1">Faltantes</div>
                        </div>
                    </div>
                    
                    @if($surgery->preparation->preAssembledPackage)
                    <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-box-open text-green-600 text-2xl mr-3"></i>
                            <div>
                                <p class="text-sm font-semibold text-green-900">Paquete Pre-Armado Asignado</p>
                                <a href="{{ route('pre-assembled.show', $surgery->preparation->preAssembledPackage) }}" 
                                   class="text-sm text-green-700 hover:text-green-900 font-medium">
                                    {{ $surgery->preparation->preAssembledPackage->name }} ({{ $surgery->preparation->preAssembledPackage->code }})
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="mt-6 text-center">
                        <a href="{{ route('surgeries.preparations.compare', $surgery) }}" 
                           class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-tasks mr-2"></i>
                            Ver Detalles de Preparación
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Remisión (si existe) -->
            @if($surgery->invoice)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-file-invoice mr-2 text-yellow-600"></i>
                        Remisión
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Número de Remisión</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono font-semibold">{{ $surgery->invoice->invoice_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Subtotal</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">${{ number_format($surgery->invoice->subtotal, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IVA</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">${{ number_format($surgery->invoice->iva, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total</dt>
                            <dd class="mt-1 text-lg text-indigo-600 font-bold">${{ number_format($surgery->invoice->total, 2) }}</dd>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center justify-center space-x-3">
                        <a href="{{ route('invoices.show', $surgery->invoice) }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-eye mr-2"></i>
                            Ver Remisión
                        </a>
                        <a href="{{ route('invoices.pdf', $surgery->invoice) }}" 
                           class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors"
                           target="_blank">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Descargar PDF
                        </a>
                    </div>
                </div>
            </div>
            @endif

            

            <!-- Timeline de Eventos -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fas fa-stream mr-2 text-indigo-600"></i>
                        Trazabilidad del Proceso
                    </h3>
                </div>
                <div class="p-6 bg-white">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex items-start space-x-4">
                                        <div class="relative">
                                            <span class="h-10 w-10 rounded-full bg-indigo-50 flex items-center justify-center ring-8 ring-white border border-indigo-200">
                                                <i class="fas fa-calendar-plus text-indigo-600"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex justify-between items-center">
                                                <p class="text-sm font-bold text-gray-900">Cirugía Registrada</p>
                                                <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded">
                                                    {{ $surgery->created_at->format('d M, Y - H:i') }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Registrada en el sistema por <span class="font-semibold text-gray-700">{{ $surgery->scheduler->name }}</span>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            @if($surgery->preparation && $surgery->preparation->started_at)
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex items-start space-x-4">
                                        <div class="relative">
                                            <span class="h-10 w-10 rounded-full bg-yellow-50 flex items-center justify-center ring-8 ring-white border border-yellow-200">
                                                <i class="fas fa-spinner fa-spin text-yellow-600"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex justify-between items-center">
                                                <p class="text-sm font-bold text-gray-900">Preparación en Curso</p>
                                                <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded">
                                                    {{ $surgery->preparation->started_at->format('d M, Y - H:i') }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Material solicitado y en proceso de surtido por <span class="font-semibold text-gray-700">{{ $surgery->preparation->preparer->name ?? 'Personal de Almacén' }}</span>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif

                            @if($surgery->status === 'ready' || ($surgery->preparation && $surgery->preparation->completed_at))
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex items-start space-x-4">
                                        <div class="relative">
                                            <span class="h-10 w-10 rounded-full bg-green-50 flex items-center justify-center ring-8 ring-white border border-green-200 shadow-sm">
                                                <i class="fas fa-check-circle text-green-600"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex justify-between items-center">
                                                <p class="text-sm font-bold text-gray-900">Material Listo para Quirófano</p>
                                                <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded">
                                                    {{ optional($surgery->preparation->completed_at)->format('d M, Y - H:i') ?? 'Pendiente' }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Verificación final completada. Equipo listo para entrega.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex items-center justify-end space-x-3">
                @if($surgery->canBeCancelled())
                    <form action="{{ route('surgeries.cancel', $surgery) }}" 
                          method="POST" 
                          onsubmit="return confirm('¿Estás seguro de cancelar esta cirugía?')">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-ban mr-1"></i>
                            Cancelar Cirugía
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function stockModal() {
    return {
        isOpen: false,
        current: {
            productId:   null,
            productName: '',
            productSku:  '',
            stock:       0,
            required:    0,
        },

        get isLow() {
            return this.current.stock < this.current.required;
        },

        openModal(data) {
            this.current = data;
            this.isOpen  = true;
        },

        closeModal() {
            this.isOpen = false;
        },
    };
}
</script>
@endpush

</x-app-layout>