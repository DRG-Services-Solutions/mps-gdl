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
                    <form action="{{ route('surgeries.preparations.start', $surgery) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all transform hover:scale-105">
                            <i class="fas fa-play-circle mr-2 text-lg"></i> INICIAR PREPARACIÓN
                        </button>
                    </form>
                @endif
                
                {{-- Otras acciones como Editar en un botón más discreto --}}
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
                                    'scheduled' => ['label' => 'Agendada', 'icon' => 'calendar', 'description' => 'Esperando inicio de preparación'],
                                    'in_preparation' => ['label' => 'En Preparación', 'icon' => 'spinner', 'description' => 'Surtiendo productos necesarios'],
                                    'ready' => ['label' => 'Lista', 'icon' => 'check-circle', 'description' => 'Preparación completa'],
                                    'in_surgery' => ['label' => 'En Cirugía', 'icon' => 'procedures', 'description' => 'Cirugía en proceso'],
                                    'completed' => ['label' => 'Completada', 'icon' => 'check', 'description' => 'Cirugía finalizada'],
                                    'cancelled' => ['label' => 'Cancelada', 'icon' => 'times-circle', 'description' => 'Cirugía cancelada'],
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
                    <p class="text-sm font-medium text-gray-900"><span class="text-gray-500 font-normal ">Dr.</span><span class="capitalize"> {{ $surgery->doctor->first_name }} {{ $surgery->doctor->last_name }}</span></p>
                    <p class="text-sm text-gray-600 mt-1 capitalize"><i class="fas fa-hospital mr-1 text-gray-400"></i> {{ Str::title( $surgery->hospital->name) }}</p>
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
                    </dl>
                </div>
            </div>

            <!-- Productos del Check List con Condicionales -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-boxes mr-2 text-indigo-600"></i>
                            Material Quirúrgico Requerido
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5 uppercase tracking-wider font-semibold">Basado en protocolos de {{ $surgery->hospital->name }}</p>
                    </div>
                    <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-1 rounded-md">
                        {{ count($checklistItems) }} ÍTEMS EN TOTAL
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-white">
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Descripción del Producto</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Cant.</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Tipo de Requisito</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($checklistItems as $data)
                                @php
                                    $item = $data['item']; 
                                    $qty = $data['adjusted_quantity'];
                                    $isConditional = ($data['source'] === 'conditional' || $data['source'] === 'extra');
                                @endphp
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-9 w-9 {{ $isConditional ? 'bg-purple-50 text-purple-600' : 'bg-blue-50 text-blue-600' }} rounded-lg flex items-center justify-center border {{ $isConditional ? 'border-purple-100' : 'border-blue-100' }}">
                                                <i class="fas {{ $isConditional ? 'fa-hand-holding-medical' : 'fa-box' }} text-sm"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900">{{ $item->product->name }}</div>
                                                <div class="text-[11px] text-gray-400 font-mono tracking-tighter">REF: {{ $item->product->sku ?? 'SIN SKU' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-sm font-black text-gray-700 border border-gray-200">
                                            {{ $qty }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($isConditional)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200 uppercase">
                                                <i class="fas fa-filter mr-1"></i> Condicional
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200 uppercase">
                                                <i class="fas fa-check-double mr-1"></i> Estándar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        {{-- Badge de estado visual --}}
                                        <div class="flex justify-center">
                                            <div class="h-2 w-2 rounded-full bg-gray-300 animate-pulse"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                        <i class="fas fa-exclamation-circle text-4xl mb-3 block"></i>
                                        <p class="text-sm font-medium">No hay productos requeridos para esta configuración.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                            {{-- Evento: Agendado (Siempre existe) --}}
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

                            {{-- Evento: Inicio de Preparación --}}
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

                            {{-- Evento: Completado --}}
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
</x-app-layout>