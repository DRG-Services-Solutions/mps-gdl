<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-box-open mr-2 text-green-600"></i>
                    {{ $preAssembled->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $preAssembled->code }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="openBulkScanModal()" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-barcode mr-2"></i>
                    Escaneo Masivo
                </button>
                <a href="{{ route('pre-assembled.edit', $preAssembled) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
                <a href="{{ route('pre-assembled.index') }}" 
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
                <!-- Card: Estado -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Estado</p>
                   
                            <p class="text-xl font-bold text-600 mt-2"></p>
                        </div>
                        <div class="bg-100 rounded-full p-3">
                            <i class="fas fa-circle text-2xl text-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Card: Total Items -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Items</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $preAssembled->contents->count() }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-cubes text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Card: Completitud -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Completitud</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($preAssembled->getCompletenessPercentage(), 1) }}%</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <i class="fas fa-chart-pie text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Card: Usos -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Veces Usado</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $preAssembled->times_used }}</p>
                        </div>
                        <div class="bg-indigo-100 rounded-full p-3">
                            <i class="fas fa-sync-alt text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle mr-2 text-green-600"></i>
                        Información Detallada
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Código</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $preAssembled->code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Nombre Pre Armado</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $preAssembled->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Check List Base</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="{{ route('checklists.show', $preAssembled->surgeryChecklist) }}" 
                                   class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $preAssembled->surgeryChecklist->surgery_type }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">EPC Contenedor</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $preAssembled->package_epc ?: 'No asignado' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Ubicación</dt>
                            <dd class="text-sm text-gray-900">{{ $preAssembled->storageLocation->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Último Uso</dt>
                            <dd class="text-sm text-gray-900">{{ $preAssembled->last_used_at ? $preAssembled->last_used_at->format('d/m/Y H:i') : 'Nunca' }}</dd>
                        </div>
                        @if($preAssembled->notes)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 mb-1">Notas</dt>
                            <dd class="text-sm text-gray-900">{{ $preAssembled->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Agregar Producto -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-blue-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-plus-circle mr-2 text-green-600"></i>
                        Agregar Producto al Paquete
                    </h3>
                </div>
                
                <form action="{{ route('pre-assembled.add-product', $preAssembled) }}" method="POST" class="p-6">
                    @csrf
                    
                    <div class="flex items-end gap-4">
                        <!-- Campo de búsqueda -->
                        <div class="flex-1">
                            <label for="search_input" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i>
                                Buscar Producto
                            </label>
                            <input type="text" 
                                name="search_input" 
                                id="search_input" 
                                placeholder="Escanea EPC, ingresa código o serial..."
                                class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('search_input') border-red-500 @enderror"
                                autofocus
                                required>
                            @error('search_input')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                💡 Puedes buscar por: EPC (24 caracteres), Código del producto (ej: 0-102), o Serial (ej: SN-12345)
                            </p>
                        </div>

                        <!-- Botón -->
                        <div class="flex-shrink-0">
                            <button type="submit" 
                                    class="px-6 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap">
                                <i class="fas fa-plus mr-1"></i>
                                Agregar
                            </button>
                        </div>
                    </div>
                </form>

            </div>

            <!-- Contenido del Paquete -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-box mr-2 text-green-600"></i>
                        Contenido del Paquete ({{ $preAssembled->contents->count() }} items)
                    </h3>
                </div>
                
                
                
                @if($preAssembled->contents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EPC</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Caducidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Agregado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($preAssembled->contents->groupBy('product_id') as $productId => $items)
                            @php
                                $firstItem = $items->first();
                                $product = $firstItem->product;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $product->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        @foreach($items as $item)
                                            @if($item->productUnit)
                                                <div class="text-xs font-mono text-gray-600">
                                                    {{ ($item->productUnit->epc) }}
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-400">Sin EPC</div>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ $items->sum('quantity') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $hasExpired = $items->contains(fn($item) => $item->productUnit && $item->isExpired());
                                        $nearExpiry = $items->contains(fn($item) => $item->productUnit && $item->isExpiringSoon(30));
                                    @endphp
                                    @if($hasExpired)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Vencido
                                        </span>
                                    @elseif($nearExpiry)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Próximo
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500">OK</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-gray-500">
                                    {{ $firstItem->added_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <form action="{{ route('pre-assembled.remove-product', $preAssembled) }}" 
                                        method="POST" 
                                        class="inline"
                                        onsubmit="return confirm('¿Remover este producto del paquete?')">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $productId }}">
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                title="Remover">
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
                        <p class="text-sm font-medium text-gray-900 mb-2">No hay productos en el paquete</p>
                        <p class="text-xs text-gray-600">Agrega productos usando el formulario de arriba</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Acciones -->
            <div class="flex items-center justify-end space-x-3">
                <form action="{{ route('pre-assembled.update-status', $preAssembled) }}" 
                      method="POST">
                    @csrf
                    <select name="status" 
                            onchange="this.form.submit()"
                            class="rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                        <option value="">Cambiar estado...</option>
                        <option value="available">Disponible</option>
                        <option value="in_preparation">En Preparación</option>
                        <option value="in_surgery">En Cirugía</option>
                        <option value="maintenance">Mantenimiento</option>
                    </select>
                </form>
                
                <form action="{{ route('pre-assembled.destroy', $preAssembled) }}" 
                      method="POST" 
                      onsubmit="return confirm('¿Estás seguro de eliminar este paquete? Esta acción no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-1"></i>
                        Eliminar Paquete
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openBulkScanModal() {
            alert('Modal de escaneo masivo\n\nEsta funcionalidad permitirá escanear múltiples EPCs consecutivamente.');
            // TODO: Implementar modal de escaneo masivo
        }
    </script>
    @endpush
</x-app-layout>