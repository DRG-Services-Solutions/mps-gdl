<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center">
                    <i class="fas {{ in_array($item->type, ['tray', 'instrumental_set', 'implant_set']) ? 'fa-box-open text-emerald-600' : 'fa-tools text-indigo-600' }} mr-3"></i>
                    Expediente: {{ $item->code }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $item->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('items.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Catálogo
                </a>
                <a href="{{ route('items.edit', $item) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-sm">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ showBomModal: false, showUnitModal: false, showRelationModal: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- ALERTAS -->
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0"><i class="fas fa-check-circle text-green-500"></i></div>
                        <div class="ml-3"><p class="text-sm text-green-700 font-medium">{{ session('success') }}</p></div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0"><i class="fas fa-exclamation-triangle text-red-500"></i></div>
                        <div class="ml-3"><p class="text-sm text-red-700 font-medium">{{ session('error') }}</p></div>
                    </div>
                </div>
            @endif

            <!-- ESTRUCTURA GRID 2:1 -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- COLUMNA PRINCIPAL  -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- 1. INFORMACIÓN GENERAL -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-microscope mr-2 text-indigo-600"></i> Ficha Técnica del Modelo
                                </h3>
                                <div>
                                    @if($item->is_active)
                                        <span class="px-3 py-1 text-xs font-bold bg-green-100 text-green-800 rounded-full italic">
                                            <i class="fas fa-check-circle mr-1"></i> ACTIVO
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-bold bg-gray-100 text-gray-600 rounded-full italic">
                                            <i class="fas fa-archive mr-1"></i> ARCHIVADO
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 italic">Clasificación WMS</dt>
                                    <dd class="text-md font-bold text-indigo-700 uppercase tracking-wider mt-1">{{ $item->type_label }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 italic">Fabricante / Marca</dt>
                                    <dd class="text-md font-semibold text-gray-900 mt-1">{{ $item->manufacturer ?? 'No Especificado' }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500 italic">Nombre Comercial</dt>
                                    <dd class="text-lg font-semibold text-gray-900 mt-1">{{ $item->name }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    <!-- COMPATIBILIDADES Y DEPENDENCIAS -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-link mr-2 text-indigo-600"></i> Compatibilidades y Requisitos
                                </h3>
                                <button @click="showRelationModal = true" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 font-semibold rounded-md hover:bg-indigo-100 transition text-sm">
                                    <i class="fas fa-plus mr-1"></i> Añadir Regla
                                </button>
                            </div>

                            @if($item->relations->count() > 0 || $item->relatedToMe->count() > 0)
                                <div class="space-y-6">
                                    
                                    <!-- Items que este elemento REQUIERE o SUGIERE -->
                                    @if($item->relations->count() > 0)
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3 bg-gray-50 p-2 rounded">Este elemento configura hacia:</h4>
                                            <ul class="space-y-3">
                                                @foreach($item->relations as $rel)
                                                    <li class="flex items-center justify-between p-3 border rounded-lg hover:shadow-sm transition bg-white">
                                                        <div class="flex items-center gap-4">
                                                            <div class="p-2 rounded-full 
                                                                {{ $rel->pivot->type === 'required' ? 'bg-red-100 text-red-600' : '' }}
                                                                {{ $rel->pivot->type === 'suggested' ? 'bg-blue-100 text-blue-600' : '' }}
                                                                {{ $rel->pivot->type === 'compatible' ? 'bg-green-100 text-green-600' : '' }}
                                                            ">
                                                                <i class="fas 
                                                                    {{ $rel->pivot->type === 'required' ? 'fa-exclamation-circle' : '' }}
                                                                    {{ $rel->pivot->type === 'suggested' ? 'fa-lightbulb' : '' }}
                                                                    {{ $rel->pivot->type === 'compatible' ? 'fa-check-circle' : '' }}
                                                                "></i>
                                                            </div>
                                                            <div>
                                                                <p class="font-bold text-gray-900">
                                                                    <a href="{{ route('items.show', $rel) }}" class="hover:underline text-indigo-600">{{ $rel->code }}</a> - {{ $rel->name }}
                                                                </p>
                                                                <div class="flex gap-2 items-center text-xs mt-1">
                                                                    <span class="font-semibold uppercase tracking-wider
                                                                        {{ $rel->pivot->type === 'required' ? 'text-red-600' : '' }}
                                                                        {{ $rel->pivot->type === 'suggested' ? 'text-blue-600' : '' }}
                                                                        {{ $rel->pivot->type === 'compatible' ? 'text-green-600' : '' }}
                                                                    ">
                                                                        {{ $rel->pivot->type === 'required' ? 'Requiere' : '' }}
                                                                        {{ $rel->pivot->type === 'suggested' ? 'Sugiere' : '' }}
                                                                        {{ $rel->pivot->type === 'compatible' ? 'Compatible con' : '' }}
                                                                    </span>
                                                                    @if($rel->pivot->notes)
                                                                        <span class="text-gray-500 italic">| "{{ $rel->pivot->notes }}"</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <form action="{{ route('items.relations.destroy', [$item, $rel->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar esta regla?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-gray-400 hover:text-red-600 transition p-2">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Items que REQUIEREN o SUGIEREN a este elemento -->
                                    @if($item->relatedToMe->count() > 0)
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3 bg-gray-50 p-2 rounded">Es configurado desde:</h4>
                                            <ul class="space-y-3">
                                                @foreach($item->relatedToMe as $invRel)
                                                    <li class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:shadow-sm transition bg-gray-50">
                                                        <div class="flex items-center gap-4 opacity-80">
                                                            <div class="p-2 rounded-full bg-gray-200 text-gray-600">
                                                                <i class="fas fa-link"></i>
                                                            </div>
                                                            <div>
                                                                <p class="font-bold text-gray-800">
                                                                    <a href="{{ route('items.show', $invRel) }}" class="hover:underline text-indigo-600">{{ $invRel->code }}</a> - {{ $invRel->name }}
                                                                </p>
                                                                <div class="flex gap-2 items-center text-xs mt-1">
                                                                    <span class="text-gray-600">Este elemento es 
                                                                        <strong class="uppercase text-gray-800">
                                                                            {{ $invRel->pivot->type === 'required' ? 'requerido por' : '' }}
                                                                            {{ $invRel->pivot->type === 'suggested' ? 'sugerido por' : '' }}
                                                                            {{ $invRel->pivot->type === 'compatible' ? 'compatible con' : '' }}
                                                                        </strong> 
                                                                        él.
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                </div>
                            @else
                                <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                    <i class="fas fa-project-diagram text-4xl text-gray-300 mb-3"></i>
                                    <h4 class="text-gray-600 font-semibold">Sin relaciones definidas</h4>
                                    <p class="text-sm text-gray-500 mt-1">Este producto es independiente o universal.</p>
                                    <button @click="showRelationModal = true" class="mt-4 px-4 py-2 bg-indigo-600 text-white font-bold rounded shadow-sm hover:bg-indigo-700 transition">
                                        <i class="fas fa-plus mr-2"></i> Añadir Dependencia
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- COLUMNA SECUNDARIA (Derecha - Sidebar) -->
                <div class="space-y-6">
                    
                    <!-- Control Biomédico -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-sm font-bold text-gray-900 mb-4 uppercase tracking-wider border-b pb-2">
                                <i class="fas fa-heartbeat mr-2 text-indigo-600"></i> Control Biomédico
                            </h3>
                            <div class="space-y-4">
                                @if($item->requires_maintenance)
                                    <div class="flex items-start">
                                        <i class="fas fa-tools text-amber-500 mt-1 mr-3"></i>
                                        <div>
                                            <p class="text-sm font-bold text-gray-800">Sujeto a Mantenimiento</p>
                                            <p class="text-xs text-gray-500 mt-0.5">Se bloqueará operativamente tras alcanzar el límite de ciclos.</p>
                                        </div>
                                    </div>
                                    <div class="bg-amber-50 p-3 rounded-lg border border-amber-100">
                                        <p class="text-xs text-amber-800 font-medium uppercase mb-1">Intervalo de Servicio</p>
                                        <p class="text-xl font-black text-amber-600">{{ $item->maintenance_interval_uses }} <span class="text-sm font-semibold">usos/cirugías</span></p>
                                    </div>
                                @else
                                    <div class="flex items-center text-green-600 bg-green-50 p-3 rounded-lg border border-green-100">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <span class="text-sm font-medium">Libre de Mantenimiento Cíclico</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Descripción / Notas -->
                    @if($item->description)
                    <div class="bg-yellow-50 overflow-hidden shadow-sm sm:rounded-lg border border-yellow-100">
                        <div class="p-6">
                            <h3 class="text-sm font-bold text-yellow-800 mb-2 uppercase">
                                <i class="fas fa-info-circle mr-2"></i> Descripción Clínica
                            </h3>
                            <p class="text-sm text-yellow-900 italic leading-relaxed">
                                "{{ $item->description }}"
                            </p>
                        </div>
                    </div>
                    @endif

                </div>

            </div>

            <!-- 2. INVENTARIO FÍSICO (Gemelos Digitales) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 pb-2 border-b border-gray-100 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-cubes mr-2 text-blue-600"></i> Unidades Físicas ({{ $item->stockUnits->count() }})
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Activos reales rastreados por SN o Grabado Láser DPM.</p>
                        </div>
                        <button @click="showUnitModal = true" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg shadow-sm hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i> Alta de Producto Fisico
                        </button>
                    </div>
                    
                    @if($item->stockUnits->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Identificador (SN/DPM)</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Items Adjuntos</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Usos</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($item->stockUnits as $unit)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                    
                                            <div class="font-mono text-sm font-bold text-gray-900">{{ strtoupper($unit->serial_number) }}</div>
                                            @if($unit->epc)
                                                <div class="text-[10px] text-purple-600 mt-0.5"><i class="fas fa-wifi"></i> EPC: {{ $unit->epc }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $unit->status === 'sterile' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $unit->status === 'dirty' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ in_array($unit->status, ['in_process', 'in_surgery']) ? 'bg-blue-100 text-blue-800' : '' }} 
                                            ">
                                                {{$unit->status_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                                            @if($unit->requiredItems->count() > 0)
                                                <span class="bg-indigo-100 text-indigo-800 py-1 px-3 rounded-full text-xs font-bold">
                                                    {{ $unit->requiredItems->sum('pivot.quantity') }} items
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-xs italic">Vacío</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-500">
                                            {{ $unit->total_uses }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium flex justify-end space-x-2">
                                            <a href="{{ route('stock-units.show', $unit) }}" 
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-900 rounded-md transition-colors shadow-sm"
                                            title="Configurar Receta Exacta de esta Torre">
                                                <i class="fas fa-cogs mr-1.5"></i> Configurar
                                            </a>

                                            <!-- Botón Eliminar Original -->
                                            <form action="{{ route('items.stock-units.destroy', [$item, $unit]) }}" method="POST" class="inline" onsubmit="return confirm('¿Baja definitiva de esta unidad física?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded-md transition-colors" title="Dar de baja">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <i class="fas fa-barcode text-gray-400 text-3xl mb-3"></i>
                            <p class="text-gray-600 font-medium text-sm">Sin inventario físico registrado.</p>
                            <p class="text-gray-400 text-xs mt-1">Registra la primera pieza utilizando el botón "Alta Física".</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL 1: ALTA FÍSICA (Gemelo Digital) -->
        <!-- ========================================== -->
        <div x-show="showUnitModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div x-show="showUnitModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="showUnitModal" x-transition.scale @click.away="showUnitModal = false" class="relative bg-white rounded-xl shadow-2xl w-full max-w-md">
                    <form action="{{ route('items.stock-units.store', $item) }}" method="POST">
                        @csrf
                        <div class="px-6 py-5 border-b border-gray-100">
                            <h3 class="text-lg font-bold text-gray-900"><i class="fas fa-barcode text-blue-600 mr-2"></i> Registrar Pieza Física</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label for="serial_number" class="block text-sm font-bold text-gray-700">Serial / Grabado Láser DPM <span class="text-red-500">*</span></label>
                                <input type="text" name="serial_number" required autofocus class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono uppercase" placeholder="Ej: KLY-001">
                            </div>
                            
                            <!-- El EPC solo es relevante si es hardware mayor que no se esteriliza -->
                            @if(in_array($item->type, ['console', 'tower', 'equipment']))
                            <div>
                                <label for="epc" class="block text-sm font-bold text-gray-700">Tag RFID (EPC) <span class="text-xs text-gray-400 font-normal">(Opcional)</span></label>
                                <input type="text" name="epc" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono" placeholder="Escanea el tag UHF...">
                            </div>
                            @endif
                        </div>
                        <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex justify-end space-x-3">
                            <button type="button" @click="showUnitModal = false" class="px-4 py-2 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Guardar Unidad</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 2: AÑADIR REGLA DE COMPATIBILIDAD -->
        <!-- ========================================== -->
        <div x-show="showRelationModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div x-show="showRelationModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="showRelationModal" x-transition.scale @click.away="showRelationModal = false" class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg">
                    <form action="{{ route('items.relations.store', $item) }}" method="POST">
                        @csrf
                        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900"><i class="fas fa-link text-indigo-600 mr-2"></i> Añadir Dependencia</h3>
                            <button type="button" @click="showRelationModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="p-6 space-y-5">
                            <!-- Tipo de relación -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Este elemento ({{ $item->code }})...</label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="type" value="required" class="peer sr-only" required>
                                        <div class="p-3 text-center border rounded-lg peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:text-red-700 hover:bg-gray-50 transition">
                                            <i class="fas fa-exclamation-circle block text-lg mb-1"></i>
                                            <span class="text-xs font-bold uppercase tracking-wide">Requiere</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="type" value="suggested" class="peer sr-only">
                                        <div class="p-3 text-center border rounded-lg peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-700 hover:bg-gray-50 transition">
                                            <i class="fas fa-lightbulb block text-lg mb-1"></i>
                                            <span class="text-xs font-bold uppercase tracking-wide">Sugiere</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="type" value="compatible" class="peer sr-only" checked>
                                        <div class="p-3 text-center border rounded-lg peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700 hover:bg-gray-50 transition">
                                            <i class="fas fa-check-circle block text-lg mb-1"></i>
                                            <span class="text-xs font-bold uppercase tracking-wide">Es Compatible</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Buscar Componente -->
                            <div>
                                <label for="related_item_id" class="block text-sm font-bold text-gray-700 mb-1">...a este componente del catálogo <span class="text-red-500">*</span></label>
                                <select id="related_item_id" name="related_item_id" required class="w-full"></select>
                            </div>

                            <!-- Notas -->
                            <div>
                                <label for="notes" class="block text-sm font-bold text-gray-700 mb-1">Notas / Condiciones Especiales <span class="text-xs text-gray-400 font-normal">(Opcional)</span></label>
                                <input type="text" name="notes" id="notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ej: Solo usar con modelo X revision 2...">
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex justify-end space-x-3 border-t border-gray-100">
                            <button type="button" @click="showRelationModal = false" class="px-4 py-2 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition">Guardar Regla</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar TomSelect para búsqueda de Item Relacionado
                if (document.getElementById('related_item_id')) {
                    new TomSelect('#related_item_id', {
                        valueField: 'id',
                        labelField: 'name',
                        searchField: ['name', 'code'],
                        load: function(query, callback) {
                            if(query.length < 2) return callback();
                            fetch('/api/search-items?q=' + encodeURIComponent(query))
                                .then(response => response.json())
                                .then(json => callback(json))
                                .catch(() => callback());
                        },
                        render: {
                            option: function(item, escape) {
                                return `<div><span class="font-bold text-gray-800">${escape(item.code)}</span> - <span class="text-gray-600">${escape(item.name)}</span></div>`;
                            },
                            item: function(item, escape) {
                                return `<div><span class="font-bold">${escape(item.code)}</span> - ${escape(item.name)}</div>`;
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>