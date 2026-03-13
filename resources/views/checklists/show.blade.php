<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-clipboard-list mr-2 text-indigo-600"></i>
                    {{ $checklist->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $checklist->code }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('checklists.items', $checklist) }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-tasks mr-2"></i>
                    Gestionar Items
                </a>
                <a href="{{ route('checklists.edit', $checklist) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
                <a href="{{ route('checklists.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Información General -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Card: Tipo de Cirugía -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Tipo de Cirugía</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $checklist->surgery_type }}</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <i class="fas fa-stethoscope text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Card: Total Items -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Items</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $checklist->items->count() }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-list text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Card: Productos Únicos -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Productos Únicos</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $checklist->items->unique('product_id')->count() }}</p>
                        </div>
                        <div class="bg-indigo-100 rounded-full p-3">
                            <i class="fas fa-boxes text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Card: Estado -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Estado</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                @if($checklist->status === 'active')
                                    <span class="text-green-600">Activo</span>
                                @else
                                    <span class="text-gray-600">Inactivo</span>
                                @endif
                            </p>
                        </div>
                        <div class="bg-{{ $checklist->status === 'active' ? 'green' : 'gray' }}-100 rounded-full p-3">
                            <i class="fas fa-{{ $checklist->status === 'active' ? 'check' : 'times' }}-circle text-2xl text-{{ $checklist->status === 'active' ? 'green' : 'gray' }}-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle mr-2 text-indigo-600"></i>
                        Información Detallada
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Código</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $checklist->code }}</dd>
                        </div>
                 
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Última actualización</dt>
                            <dd class="text-sm text-gray-900">{{ $checklist->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Lista de Items -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-box mr-2 text-indigo-600"></i>
                        Productos del Check List
                    </h3>
                    <a href="{{ route('checklists.items', $checklist) }}" 
                       class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <i class="fas fa-plus mr-1"></i>
                        Agregar productos
                    </a>
                </div>
                
                @if($checklist->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Condicionales</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($checklist->items->sortBy('order') as $item)
                            <tr class="hover:bg-gray-50">
                               
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $item->product->code }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->product->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($item->conditionals->count() > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-filter mr-1"></i>
                                            {{ $item->conditionals->count() }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <i class="fas fa-box-open text-4xl mb-3"></i>
                        <p class="text-sm font-medium text-gray-900 mb-2">No hay productos agregados</p>
                        <p class="text-xs text-gray-600 mb-4">Agrega productos al check list para comenzar</p>
                        <a href="{{ route('checklists.items', $checklist) }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Agregar Productos
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <!-- Estadísticas de Uso -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-bar mr-2 text-indigo-600"></i>
                        Estadísticas de Uso
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600">{{ $checklist->scheduledSurgeries->count() }}</div>
                            <div class="text-sm text-gray-600 mt-1">Cirugías Programadas</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ $checklist->preAssembledPackages->count() }}</div>
                            <div class="text-sm text-gray-600 mt-1">Paquetes Pre-Armados</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ $checklist->scheduledSurgeries->where('status', 'completed')->count() }}</div>
                            <div class="text-sm text-gray-600 mt-1">Cirugías Completadas</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex items-center justify-end space-x-3">
                <form action="{{ route('checklists.duplicate', $checklist) }}" 
                      method="POST" 
                      onsubmit="return confirm('¿Deseas duplicar este check list?')">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-copy mr-1"></i>
                        Duplicar Check List
                    </button>
                </form>
                
                <form action="{{ route('checklists.destroy', $checklist) }}" 
                      method="POST" 
                      onsubmit="return confirm('¿Estás seguro de eliminar este check list? Esta acción no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-1"></i>
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>