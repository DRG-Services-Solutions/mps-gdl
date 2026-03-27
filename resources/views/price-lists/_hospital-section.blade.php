{{-- 
    Sección de Lista de Precios para incluir en hospitals/edit.blade.php
    
    Agregar dentro del formulario de edición del hospital, como una sección más.
    Requiere que el controlador pase $hospital con la relación cargada:
    
    $hospital->load(['activePriceList.items', 'priceLists']);
--}}

<!-- Lista de Precios -->
<div>
    <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
        <i class="fas fa-tags mr-2 text-indigo-600"></i>
        Lista de Precios
    </h3>

    @if($hospital->activePriceList)
        {{-- Tiene lista activa --}}
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-semibold text-green-900">{{ $hospital->activePriceList->name }}</p>
                        <p class="text-xs text-green-700">
                            <span class="font-mono">{{ $hospital->activePriceList->code }}</span>
                            <span class="mx-1">·</span>
                            {{ $hospital->activePriceList->items->count() }} productos
                        </p>
                    </div>
                </div>
                <a href="{{ route('price-lists.show', $hospital->activePriceList) }}"
                   class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-100 hover:bg-green-200 rounded-lg transition-colors">
                    <i class="fas fa-external-link-alt mr-1"></i> Administrar
                </a>
            </div>
        </div>

        {{-- Otras listas inactivas --}}
        @php
            $inactiveLists = $hospital->priceLists->where('is_active', false);
        @endphp
        @if($inactiveLists->count() > 0)
            <div class="mt-3">
                <p class="text-xs text-gray-500 mb-2">Otras listas (inactivas):</p>
                <div class="space-y-2">
                    @foreach($inactiveLists as $list)
                        <div class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="text-sm text-gray-600">
                                <span class="font-mono text-xs">{{ $list->code }}</span>
                                <span class="mx-1">·</span>
                                {{ $list->name }}
                            </div>
                            <a href="{{ route('price-lists.show', $list) }}" class="text-xs text-indigo-600 hover:text-indigo-800">
                                <i class="fas fa-eye mr-1"></i> Ver
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @else
        {{-- No tiene lista activa --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-semibold text-yellow-900">Sin lista de precios activa</p>
                        <p class="text-xs text-yellow-700">Este hospital no tiene una lista de precios asignada</p>
                    </div>
                </div>
                <a href="{{ route('price-lists.create') }}?hospital_id={{ $hospital->id }}"
                   class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-yellow-700 bg-yellow-100 hover:bg-yellow-200 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1"></i> Crear Lista
                </a>
            </div>
        </div>
    @endif
</div>
