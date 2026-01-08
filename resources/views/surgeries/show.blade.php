<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-procedures mr-2 text-indigo-600"></i>
                    Cirugía {{ $surgery->code }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $surgery->checklist->name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @if($surgery->status === 'scheduled')
                    <form action="{{ route('surgeries.preparations.start', $surgery) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded">
                            <i class="fas fa-play mr-2"></i> Iniciar Preparación
                        </button>
                    </form>
                @elseif($surgery->status === 'in_preparation')
                    <a href="{{ route('surgeries.preparations.compare', $surgery) }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-tasks mr-2"></i>
                        Continuar Preparación
                    </a>
                @elseif($surgery->status === 'ready' && !$surgery->invoice)
                    <a href="{{ route('invoices.create-from-surgery', $surgery) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Generar Remisión
                    </a>
                @endif
                
                @if($surgery->canBeEdited())
                    <a href="{{ route('surgeries.edit', $surgery) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-edit mr-2"></i>
                        Editar
                    </a>
                @endif
                
                <a href="{{ route('surgeries.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Paciente -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-user mr-2 text-indigo-600"></i>
                            Información del Paciente
                        </h3>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nombre del Paciente</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $surgery->patient_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Modalidad de Pago</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $surgery->payment_mode === 'particular' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        <i class="fas fa-{{ $surgery->payment_mode === 'particular' ? 'user' : 'shield-alt' }} mr-1"></i>
                                        {{ $surgery->payment_mode === 'particular' ? 'Particular' : 'Aseguradora' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Hospital y Doctor -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-hospital mr-2 text-indigo-600"></i>
                            Hospital y Doctor
                        </h3>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Hospital</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                    <i class="fas fa-hospital text-gray-400 mr-1"></i>
                                    {{ $surgery->hospital->name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Doctor</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                    <i class="fas fa-user-md text-gray-400 mr-1"></i>
                                    {{ $surgery->doctor->first_name }} {{ $surgery->doctor->last_name }}
                                </dd>
                            </div>
                        </dl>
                    </div>
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
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-box mr-2 text-indigo-600"></i>
                        Productos Requeridos
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Check list aplicado con condicionales según hospital, doctor y modalidad</p>
                </div>
                
                @php
                    $checklistItems = $surgery->getChecklistItemsWithConditionals();
                @endphp
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Obligatorio</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aplicado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($checklistItems as $item)
                            @php
                                $evaluation = $item->evaluateConditionals($surgery->hospital_id, $surgery->payment_mode);
                            @endphp
                            @if($evaluation['status'] !== 'excluded')
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $item->product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->product->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ $evaluation['quantity'] }}
                                    </span>
                                   
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($evaluation['status'] === 'required' || $item->is_mandatory)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            Sí
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            No
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($item->conditionals->count() > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-filter mr-1"></i>
                                            Con condicionales
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">Estándar</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No hay productos en el check list
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
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-history mr-2 text-indigo-600"></i>
                        Historial de Eventos
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-calendar text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Cirugía Agendada</p>
                                                <p class="text-xs text-gray-500">{{ $surgery->created_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">Por {{ $surgery->scheduler->name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            
                            @if($surgery->preparation && $surgery->preparation->started_at)
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-play text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Preparación Iniciada</p>
                                                <p class="text-xs text-gray-500">{{ $surgery->preparation->started_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">Por {{ $surgery->preparation->preparer->name ?? 'Sistema' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                            
                            @if($surgery->preparation && $surgery->preparation->completed_at)
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-check text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Preparación Completada</p>
                                                <p class="text-xs text-gray-500">{{ $surgery->preparation->completed_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">Por {{ $surgery->preparation->verifier->name ?? 'Sistema' }}</p>
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