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
            <div class="bg-white rounded-lg shadow-sm">
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
                            <label for="product_search" class="block text-sm font-medium text-gray-700 mb-2">
                                Producto <span class="text-red-500">*</span>
                            </label>
                            <select id="product_search" name="product_id">
                                <option value="">Selecciona un producto...</option>

                                @foreach ($products as $product)
                                    @if($product->code && $product->name)
                                        <option
                                            value="{{ $product->id }}"
                                            data-code="{{ $product->code }}"
                                            data-name="{{ $product->name }}"
                                        >
                                            {{ $product->code }} - {{ $product->name }}
                                        </option>
                                    @endif
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
            <div class="bg-white rounded-lg shadow-sm ">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list mr-2 text-indigo-600"></i>
                        Productos del Check List ({{ $items->total() }})
                    </h3>
                </div>
                
                @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Condicionales</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="sortable-items">
                            @foreach($items as $item)
                            <tr class="hover:bg-gray-50 transition-colors" data-item-id="{{ $item->id }}">
                               
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
                 {{-- ===== PAGINACIÓN ===== --}}
                @if($items->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $items->links() }}
                </div>
                @endif
                {{-- ===== FIN PAGINACIÓN ===== --}}
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

            
        </div>
    </div>

@push('styles')
<style>
    /* ========================================
       TOM SELECT
       ======================================== */
    
    /* WRAPPER - Contenedor principal */
    .ts-wrapper {
        position: relative !important;
    }
    
    /* CONTROL - Input de búsqueda */
    .ts-control {
        min-height: 42px !important;
        padding: 0.5rem 0.75rem !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        background-color: white !important;
        font-size: 0.875rem !important;
        line-height: 1.5rem !important;
        transition: all 0.15s ease !important;
    }
    
    .ts-control:hover {
        border-color: #9ca3af !important;
    }
    
    .ts-control.focus {
        border-color: #9333ea !important;
        box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.1) !important;
        outline: none !important;
    }
    
    /* Input dentro del control */
    .ts-control input {
        color: #111827 !important;
        font-size: 0.875rem !important;
    }
    
    .ts-control input::placeholder {
        color: #9ca3af !important;
    }
    
    /* Item seleccionado */
    .ts-control .item {
        color: #111827 !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        font-size: 0.875rem !important;
    }
    
    /* DROPDOWN - Menú de opciones */
    .ts-dropdown {
        position: absolute !important;
        z-index: 10000 !important;
        margin-top: 0.25rem !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        background-color: white !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 
                    0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    }
    
    /* OPCIONES del dropdown */
    .ts-dropdown .option {
        padding: 0.75rem 1rem !important;
        cursor: pointer !important;
        border-bottom: 1px solid #f3f4f6 !important;
        transition: background-color 0.15s ease !important;
    }
    
    .ts-dropdown .option:last-child {
        border-bottom: none !important;
    }
    
    /* Opción al pasar el mouse */
    .ts-dropdown .option.active {
        background-color: #f9fafb !important;
        color: #111827 !important;
    }
    
    /* Opción seleccionada */
    .ts-dropdown .option.selected {
        background-color: #9333ea !important;
        color: white !important;
    }
    
    .ts-dropdown .option.selected.active {
        background-color: #7e22ce !important;
    }
    
    /* Mensaje de "No hay resultados" */
    .ts-dropdown .no-results {
        padding: 1rem !important;
        color: #6b7280 !important;
        text-align: center !important;
        font-size: 0.875rem !important;
    }
    
    /* Eliminar flechas nativas */
    .ts-wrapper.single .ts-control::after {
        border-color: #6b7280 transparent transparent transparent !important;
        border-width: 5px 5px 0 5px !important;
        margin-top: -3px !important;
    }
    
    .ts-wrapper.single.dropdown-active .ts-control::after {
        border-color: transparent transparent #6b7280 transparent !important;
        border-width: 0 5px 5px 5px !important;
        margin-top: -2px !important;
    }
    
    /* Ocultar spinner de carga si aparece */
    .ts-wrapper .spinner {
        display: none !important;
    }

    
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

new TomSelect('#product_search', {
    placeholder: 'Selecciona un producto...',
    allowEmptyOption: true,
    items: [],

    shouldLoad: function(query) {
        return query.length > 0;
    },

    render: {
        option: function (data, escape) {
            if (!data.code || !data.name) return '';
            return `
                <div>
                    <div style="font-weight:600">${escape(data.code)}</div>
                    <div style="font-size:0.875rem;color:#6b7280">
                        ${escape(data.name)}
                    </div>
                </div>
            `;
        },
        item: function (data, escape) {
            if (!data.code || !data.name) return '';
            return `
                <div>${escape(data.code)} - ${escape(data.name)}</div>
            `;
        }
    },

    onInitialize() {
        // 🔥 elimina opciones inválidas
        Object.keys(this.options).forEach(key => {
            const opt = this.options[key];
            if (!opt.code || !opt.name) {
                delete this.options[key];
            }
        });
        this.refreshOptions(false);
    }
});



});

// ========================================
// Modal de Condicionales
// ========================================
function openConditionalsModal(itemId) {
    alert(
        'Modal de condicionales para item: ' + itemId +
        '\n\nEsta funcionalidad se implementará con un componente modal completo.'
    );
}
</script>
@endpush



</x-app-layout>