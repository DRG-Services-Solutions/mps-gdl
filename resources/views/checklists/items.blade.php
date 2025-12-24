<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-tasks mr-2 text-purple-600"></i>
                    Gestionar Items
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $checklist->name }} ({{ $checklist->code }})</p>
            </div>
            <a href="{{ route('checklists.show', $checklist) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver al Check List
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Agregar Producto -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-plus-circle mr-2 text-purple-600"></i>
                        Agregar Producto al Check List
                    </h3>
                </div>
                
                <form action="{{ route('checklist-items.store', $checklist) }}" method="POST" class="p-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Producto -->
                        <div class="md:col-span-2">
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Producto <span class="text-red-500">*</span>
                            </label>
                            <select name="product_id" 
                                    id="product_id"
                                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 @error('product_id') border-red-500 @enderror"
                                    required>
                                <option value="">Selecciona un producto...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->code }} - {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cantidad -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                Cantidad <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="quantity" 
                                   id="quantity" 
                                   min="1"
                                   value="1"
                                   class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 @error('quantity') border-red-500 @enderror"
                                   required>
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Obligatorio -->
                        <div>
                            <label for="is_mandatory" class="block text-sm font-medium text-gray-700 mb-2">
                                ¿Obligatorio? <span class="text-red-500">*</span>
                            </label>
                            <select name="is_mandatory" 
                                    id="is_mandatory"
                                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                    required>
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <!-- Botón -->
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full px-4 py-2.5 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas fa-plus mr-1"></i>
                                Agregar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Lista de Items -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list mr-2 text-indigo-600"></i>
                        Productos del Check List ({{ $checklist->items->count() }})
                    </h3>
                </div>
                
                @if($checklist->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-sort mr-1"></i>
                                    Orden
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Obligatorio</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Condicionales</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="sortable-items">
                            @foreach($checklist->items->sortBy('order') as $item)
                            <tr class="hover:bg-gray-50 transition-colors" data-item-id="{{ $item->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-grip-vertical text-gray-400 cursor-move"></i>
                                        <span class="text-sm text-gray-500">#{{ $item->order }}</span>
                                    </div>
                                </td>
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
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <form action="{{ route('checklist-items.update', $item) }}" 
                                          method="POST" 
                                          class="inline-flex items-center space-x-2"
                                          onchange="this.submit()">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" 
                                               name="quantity" 
                                               value="{{ $item->quantity }}"
                                               min="1"
                                               class="w-20 text-center rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <input type="hidden" name="is_mandatory" value="{{ $item->is_mandatory }}">
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <form action="{{ route('checklist-items.update', $item) }}" 
                                          method="POST" 
                                          onchange="this.submit()">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="quantity" value="{{ $item->quantity }}">
                                        <select name="is_mandatory" 
                                                class="text-xs rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="1" {{ $item->is_mandatory ? 'selected' : '' }}>Sí</option>
                                            <option value="0" {{ !$item->is_mandatory ? 'selected' : '' }}>No</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button type="button" 
                                            onclick="openConditionalsModal({{ $item->id }})"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->conditionals->count() > 0 ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }} hover:bg-purple-200 transition-colors">
                                        <i class="fas fa-filter mr-1"></i>
                                        {{ $item->conditionals->count() }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('checklist-items.destroy', $item) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('¿Eliminar este producto del check list?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                title="Eliminar">
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
                <div class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <i class="fas fa-box-open text-4xl mb-3"></i>
                        <p class="text-sm font-medium text-gray-900 mb-2">No hay productos en el check list</p>
                        <p class="text-xs text-gray-600">Agrega productos usando el formulario de arriba</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Información -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-blue-900 mb-2">Sobre los Condicionales</h4>
                        <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                            <li>Los condicionales permiten ajustar cantidades por hospital, doctor o modalidad</li>
                            <li>Puedes hacer un producto obligatorio solo para ciertos hospitales</li>
                            <li>El multiplicador aumenta la cantidad base (ej: 1.5 = +50%)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Placeholder para modal de condicionales (implementar con Alpine.js o modal component)
        function openConditionalsModal(itemId) {
            alert('Modal de condicionales para item: ' + itemId + '\n\nEsta funcionalidad se implementará con un componente modal completo.');
            // TODO: Implementar modal de condicionales
        }

        // Placeholder para drag & drop de reordenamiento
        // TODO: Implementar con SortableJS cuando sea necesario
    </script>
    @endpush
</x-app-layout>