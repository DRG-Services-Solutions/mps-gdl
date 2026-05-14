<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 flex items-center">
                    <i class="fas fa-microchip text-blue-600 mr-3"></i>
                    Expediente Físico: <span class="font-mono ml-2">{{ $stockUnit->serial_number }}</span>
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Derivado del modelo: <a href="{{ route('items.show', $stockUnit->item) }}" class="text-blue-600 hover:underline font-bold">{{ $stockUnit->item->name }}</a>
                </p>
            </div>
            <a href="{{ route('items.show', $stockUnit->item) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow-sm hover:bg-gray-300 font-bold text-xs uppercase transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Catálogo
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ showRecipeModal: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- ALERTAS DE ÉXITO Y ERROR -->
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0"><i class="fas fa-check-circle text-green-500"></i></div>
                        <div class="ml-3"><p class="text-sm text-green-700 font-medium">{{ session('success') }}</p></div>
                    </div>
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0"><i class="fas fa-exclamation-triangle text-red-500"></i></div>
                        <div class="ml-3">
                            <ul class="text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TARJETA 1: Estatus de la Unidad -->
            <div class="bg-white shadow-sm sm:rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4"><i class="fas fa-info-circle mr-2 text-blue-600"></i> Estado Actual de la Unidad</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Estado Operativo</p>
                        <p class="text-xl font-bold uppercase {{ $stockUnit->status === 'sterile' ? 'text-green-600' : 'text-gray-900' }}">{{ $stockUnit->status_label ?? 'Estéril' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Usos / Cirugías Totales</p>
                        <p class="text-xl font-bold text-gray-900">{{ $stockUnit->total_uses ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tag RFID / EPC</p>
                        <p class="text-sm font-mono font-bold text-gray-900 mt-1">{{ $stockUnit->epc ?? 'Sin Tag Asignado' }}</p>
                    </div>
                </div>
            </div>

            <!-- TARJETA 2: LA RECETA FÍSICA (Instance BOM) -->
            <div class="bg-white shadow-sm sm:rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-emerald-50 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-emerald-900"><i class="fas fa-list-ol mr-2"></i> Receta de Configuración Exacta</h3>
                        <p class="text-xs text-emerald-700">Lo que DEBE contener esta torre física ({{ $stockUnit->serial_number }}).</p>
                    </div>
                    <button @click="showRecipeModal = true" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center">
                        <i class="fas fa-plus mr-2"></i> Añadir a Receta
                    </button>
                </div>
                
                <div class="p-0">
                    @if($stockUnit->requiredItems->count() > 0)
                        <ul class="divide-y divide-gray-100">
                            @foreach($stockUnit->requiredItems as $reqItem)
                                <li class="px-6 py-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center space-x-4">
                                        <div class="bg-emerald-100 text-emerald-800 font-black px-3 py-1 rounded shadow-sm text-sm">
                                            x{{ $reqItem->pivot->quantity }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm sm:text-base">{{ $reqItem->name }}</p>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span class="text-[10px] font-mono bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded">{{ $reqItem->code }}</span>
                                                
                                                @if($reqItem->pivot->requirement_type === 'mandatory')
                                                    <span class="text-[10px] font-bold text-green-700 bg-green-100 px-2 py-0.5 rounded"><i class="fas fa-check-circle mr-1"></i> Obligatorio</span>
                                                @elseif($reqItem->pivot->requirement_type === 'conditional')
                                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded"><i class="fas fa-exclamation-triangle mr-1"></i> Condicional</span>
                                                @else
                                                    <span class="text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded"><i class="fas fa-info-circle mr-1"></i> Opcional</span>
                                                @endif
                                            </div>
                                            
                                            @if($reqItem->pivot->notes)
                                                <p class="text-xs text-gray-500 italic mt-1"><i class="fas fa-comment-medical mr-1"></i> {{ $reqItem->pivot->notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Botón para retirar el ítem de la receta física -->
                                    <form action="{{ route('stock-units.recipe.destroy', [$stockUnit->id, $reqItem->id]) }}" method="POST" onsubmit="return confirm('¿Retirar este componente de la torre {{ $stockUnit->serial_number }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600 p-2 transition-colors" title="Retirar componente">
                                            <i class="fas fa-times text-lg"></i>
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-12 text-center text-gray-500 bg-gray-50">
                            <i class="fas fa-puzzle-piece text-4xl mb-4 text-gray-300"></i>
                            <p class="font-medium text-gray-600">Esta unidad física no tiene una receta configurada aún.</p>
                            <p class="text-sm mt-1 text-gray-400">Comienza añadiendo la consola o instrumental principal que la compone.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: CONSTRUCTOR DE RECETAS FÍSICAS (AJAX) -->
        <!-- ========================================== -->
        <div x-show="showRecipeModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div x-show="showRecipeModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="showRecipeModal" x-transition.scale @click.away="showRecipeModal = false" class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-visible">
                    
                    <form action="{{ route('stock-units.recipe.store', $stockUnit) }}" method="POST">
                        @csrf
                        
                        <div class="px-6 py-5 border-b border-emerald-100 bg-emerald-50 rounded-t-xl">
                            <h3 class="text-lg font-bold text-emerald-900 flex items-center">
                                <i class="fas fa-link mr-2"></i> Añadir a Receta Específica
                            </h3>
                            <p class="text-xs text-emerald-700 mt-1 font-mono">Unidad Física: {{ $stockUnit->serial_number }}</p>
                        </div>
                        
                        <div class="p-6 space-y-5 overflow-visible">

                            <!-- Buscador AJAX con TomSelect -->
                            <div class="relative z-50">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Componente del Catálogo <span class="text-red-500">*</span></label>
                                <!-- Inicialización de TomSelect con Fetch a tu API interna -->
                                <select name="child_item_id" required x-init="
                                    new TomSelect($el, {
                                        valueField: 'id',
                                        labelField: 'name',
                                        searchField: ['name', 'code'],
                                        placeholder: 'Escribe código o nombre (Ej. PINZ)...',
                                        load: function(query, callback) {
                                            if(query.length < 2) return callback();
                                            fetch('/api/search-items?q=' + encodeURIComponent(query) + '&parent_type={{ $stockUnit->item->type }}')
                                                .then(response => response.json())
                                                .then(json => callback(json))
                                                .catch(() => callback());
                                        },
                                        render: {
                                            option: function(item, escape) {
                                                return '<div class=\'py-2 px-3 border-b border-gray-50 hover:bg-emerald-50 transition-colors\'>' +
                                                       '<span class=\'font-mono text-xs font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded mr-2\'>[' + escape(item.code) + ']</span>' +
                                                       '<span class=\'font-bold text-gray-800\'>' + escape(item.name) + '</span>' +
                                                       '</div>';
                                            },
                                            item: function(item, escape) {
                                                return '<div class=\'font-bold text-emerald-900\'>[' + escape(item.code) + '] ' + escape(item.name) + '</div>';
                                            }
                                        }
                                    });
                                "></select>
                            </div>

                            <!-- Reglas Clínicas -->
                            <div class="grid grid-cols-2 gap-4 relative z-40">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Cantidad <span class="text-red-500">*</span></label>
                                    <input type="number" name="quantity" value="1" min="1" required 
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm font-bold text-gray-900">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Regla Clínica <span class="text-red-500">*</span></label>
                                    <select name="requirement_type" required 
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm font-medium text-gray-700">
                                        <option value="mandatory">🟢 Obligatorio</option>
                                        <option value="conditional">🟡 Condicional (Dr/Hosp)</option>
                                        <option value="optional">🔵 Opcional (Solamente si se pide)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Notas -->
                            <div class="relative z-30">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Condiciones / Notas Clínicas <span class="text-gray-400 font-normal">(Opcional)</span></label>
                                <textarea name="notes" rows="2" 
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" 
                                          placeholder="Ej: Obligatorio en CX Dr Mendoza en San Jose/Lazaro..."></textarea>
                                <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle text-blue-500"></i> Estas notas guiarán al operador durante el picking de la cirugía.</p>
                            </div>

                        </div>
                        
                        <!-- Footer Modal -->
                        <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex justify-end space-x-3 border-t border-gray-200">
                            <button type="button" @click="showRecipeModal = false" class="px-4 py-2 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                                <i class="fas fa-save mr-2"></i> Vincular a esta Torre
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>