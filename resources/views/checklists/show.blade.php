<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-indigo-100 rounded-xl p-3">
                    <i class="fas fa-clipboard-list text-2xl text-indigo-600"></i>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ $checklist->surgery_type }}
                    </h2>
                    <div class="flex items-center space-x-3 mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-gray-100 text-xs font-mono font-semibold text-gray-600">
                            {{ $checklist->code }}
                        </span>
                        @if($checklist->status === 'active')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                Activo
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span>
                                Inactivo
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('checklists.items', $checklist) }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200">
                    <i class="fas fa-tasks mr-2"></i>
                    Gestionar Items
                </a>
                <a href="{{ route('checklists.edit', $checklist) }}" 
                   class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg border border-gray-300 shadow-sm transition-all duration-200">
                    <i class="fas fa-edit mr-2 text-gray-400"></i>
                    Editar
                </a>
                <a href="{{ route('checklists.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg border border-gray-300 shadow-sm transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2 text-gray-400"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Métricas --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Items</span>
                        <div class="bg-blue-50 rounded-lg p-2">
                            <i class="fas fa-list text-blue-500 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ $checklist->items->count() }}</p>
                    <p class="text-xs text-gray-400 mt-1">en este checklist</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Productos</span>
                        <div class="bg-indigo-50 rounded-lg p-2">
                            <i class="fas fa-boxes text-indigo-500 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ $checklist->items->unique('product_id')->count() }}</p>
                    <p class="text-xs text-gray-400 mt-1">productos únicos</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cirugías</span>
                        <div class="bg-green-50 rounded-lg p-2">
                            <i class="fas fa-calendar-check text-green-500 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ $checklist->scheduledSurgeries->count() }}</p>
                    <p class="text-xs text-gray-400 mt-1">programadas</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Paquetes</span>
                        <div class="bg-purple-50 rounded-lg p-2">
                            <i class="fas fa-box text-purple-500 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ $checklist->preAssembledPackages->count() }}</p>
                    <p class="text-xs text-gray-400 mt-1">pre-armados</p>
                </div>
            </div>

            {{-- Información detallada --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                        <i class="fas fa-info-circle mr-2 text-indigo-500"></i>
                        Información Detallada
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Código</dt>
                            <dd class="text-sm text-gray-900 font-mono font-semibold">{{ $checklist->code }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Tipo de Cirugía</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $checklist->surgery_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Última actualización</dt>
                            <dd class="text-sm text-gray-900">{{ $checklist->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Productos del checklist --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                        <i class="fas fa-box mr-2 text-indigo-500"></i>
                        Productos del Check List
                    </h3>
                    @if($checklist->items->count() > 0)
                        <a href="{{ route('checklists.items', $checklist) }}" 
                           class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                            <i class="fas fa-plus mr-1.5 text-xs"></i>
                            Agregar productos
                        </a>
                    @endif
                </div>

                @if($checklist->items->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Condicionales</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($checklist->items->sortBy('order') as $item)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-box text-indigo-500 text-sm"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $item->product->name }}</p>
                                                    <p class="text-xs text-gray-400 font-mono">{{ $item->product->code }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center min-w-[2rem] px-2.5 py-1 rounded-lg text-sm font-bold bg-blue-50 text-blue-700">
                                                {{ $item->quantity }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($item->conditionals->count() > 0)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700">
                                                    <i class="fas fa-filter mr-1.5 text-purple-400"></i>
                                                    {{ $item->conditionals->count() }} {{ $item->conditionals->count() === 1 ? 'regla' : 'reglas' }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-300">Sin reglas</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-16 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-50 rounded-2xl mb-4">
                            <i class="fas fa-box-open text-2xl text-gray-300"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 mb-1">No hay productos agregados</p>
                        <p class="text-xs text-gray-400 mb-6">Agrega productos al check list para comenzar</p>
                        <a href="{{ route('checklists.items', $checklist) }}" 
                           class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Agregar Productos
                        </a>
                    </div>
                @endif
            </div>

            {{-- Estadísticas de uso --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                        <i class="fas fa-chart-bar mr-2 text-indigo-500"></i>
                        Estadísticas de Uso
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center p-4 rounded-xl bg-indigo-50/50">
                            <div class="text-3xl font-bold text-indigo-600">{{ $checklist->scheduledSurgeries->count() }}</div>
                            <div class="text-xs font-medium text-gray-500 mt-1">Cirugías Programadas</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-green-50/50">
                            <div class="text-3xl font-bold text-green-600">{{ $checklist->scheduledSurgeries->where('status', 'completed')->count() }}</div>
                            <div class="text-xs font-medium text-gray-500 mt-1">Cirugías Completadas</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-purple-50/50">
                            <div class="text-3xl font-bold text-purple-600">{{ $checklist->preAssembledPackages->count() }}</div>
                            <div class="text-xs font-medium text-gray-500 mt-1">Paquetes Pre-Armados</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Creado el {{ $checklist->created_at->format('d/m/Y') }}
                </p>
                <div class="flex items-center space-x-2">
                    <form action="{{ route('checklists.duplicate', $checklist) }}" 
                          method="POST" 
                          onsubmit="return confirm('¿Deseas duplicar este check list?')">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition-all duration-200">
                            <i class="fas fa-copy mr-2 text-gray-400"></i>
                            Duplicar
                        </button>
                    </form>

                    <form action="{{ route('checklists.destroy', $checklist) }}" 
                          method="POST" 
                          onsubmit="return confirm('¿Estás seguro de eliminar este check list? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 shadow-sm transition-all duration-200">
                            <i class="fas fa-trash mr-2"></i>
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>