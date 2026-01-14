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
                                   class="w-full rounded-lg  focus:border-purple-500 focus:ring-purple-500 @error('quantity') border-red-500 @enderror"
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
                                            @click="$dispatch('open-conditionals-modal', { 
                                                itemId: {{ $item->id }}, 
                                                itemName: '{{ addslashes($item->product->name) }}',
                                                baseQuantity: {{ $item->quantity }}
                                            })"
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
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
// ==========================================
// TOM SELECT - Inicialización
// ==========================================
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

// ==========================================
// ALPINE.JS - Modal de Condicionales
// ==========================================
function conditionalsModal() {
    return {
        // Estado
        isOpen: false,
        loading: false,
        isSubmitting: false,
        errorMessage: '',
        
        // Datos del item
        itemId: null,
        itemName: '',
        baseQuantity: 0,
        
        // Condicionales existentes
        conditionals: [],
        
        // Datos del formulario (selects)
        formData: {
            doctors: [],
            hospitals: [],
            modalities: [],
            legal_entities: []
        },
        
        // Nuevo condicional
        newConditional: {
            doctor_id: '',
            hospital_id: '',
            modality_id: '',
            legal_entity_id: '',
            is_additional_product: false,
            quantity_override: null,
            additional_quantity: null,
            notes: ''
        },
        
        // Abrir modal
        async openModal(data) {
            console.log('Abriendo modal con datos:', data);
            
            this.itemId = data.itemId;
            this.itemName = data.itemName;
            this.baseQuantity = data.baseQuantity;
            this.isOpen = true;
            this.errorMessage = '';
            
            // Cargar datos
            await this.loadFormData();
            await this.loadConditionals();
        },
        
        // Cerrar modal
        closeModal() {
            this.isOpen = false;
            this.resetForm();
        },
        
        // Cargar datos del formulario
        async loadFormData() {
            try {
                const response = await fetch('{{ route("conditional-form-data") }}', {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.formData = result.data;
                    console.log('Datos del formulario cargados:', this.formData);
                }
            } catch (error) {
                console.error('Error al cargar datos del formulario:', error);
            }
        },
        
        // Cargar condicionales existentes
        async loadConditionals() {
            this.loading = true;
            
            try {
                const response = await fetch(`/checklist-items/${this.itemId}/conditionals`, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.conditionals = result.data;
                    console.log('Condicionales cargados:', this.conditionals);
                }
            } catch (error) {
                console.error('Error al cargar condicionales:', error);
            } finally {
                this.loading = false;
            }
        },
        
        // Crear condicional
        async submitConditional() {
    this.isSubmitting = true;
    this.errorMessage = '';
    
    console.log('=== INICIO SUBMIT ===');
    console.log('Item ID:', this.itemId);
    
    try {
        const data = {
            doctor_id: this.newConditional.doctor_id || null,
            hospital_id: this.newConditional.hospital_id || null,
            modality_id: this.newConditional.modality_id || null,
            legal_entity_id: this.newConditional.legal_entity_id || null,
            is_additional_product: this.newConditional.is_additional_product,
            quantity_override: this.newConditional.is_additional_product ? null : this.newConditional.quantity_override,
            additional_quantity: this.newConditional.is_additional_product ? this.newConditional.additional_quantity : null,
            notes: this.newConditional.notes || null
        };
        
        console.log('📤 Datos a enviar:', data);
        
        const url = `/checklist-items/${this.itemId}/conditionals`;
        console.log('🌐 URL:', url);
        console.log('🌐 URL completa:', window.location.origin + url);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        console.log('📥 Response Status:', response.status);
        console.log('📥 Response StatusText:', response.statusText);
        console.log('📥 Response OK:', response.ok);
        
        // LEER COMO TEXTO PRIMERO
        const textResponse = await response.text();
        console.log('📥 TEXTO COMPLETO DE RESPUESTA:');
        console.log(textResponse);
        console.log('📥 Primeros 500 caracteres:', textResponse.substring(0, 500));
        console.log('📥 Longitud de respuesta:', textResponse.length, 'caracteres');
        
        // Verificar si es HTML
        if (textResponse.trim().startsWith('<')) {
            console.error('❌ ¡EL SERVIDOR DEVOLVIÓ HTML!');
            console.error('Probablemente es una página de error 404, 500 o redirección');
            
            // Intentar extraer el título de la página
            const titleMatch = textResponse.match(/<title>(.*?)<\/title>/i);
            if (titleMatch) {
                console.error('Título de la página:', titleMatch[1]);
                this.errorMessage = 'Error del servidor: ' + titleMatch[1];
            } else {
                this.errorMessage = 'El servidor devolvió HTML en lugar de JSON. Status: ' + response.status;
            }
            return;
        }
        
        // Intentar parsear JSON
        let result;
        try {
            result = JSON.parse(textResponse);
            console.log('✅ JSON parseado:', result);
        } catch (parseError) {
            console.error('❌ Error al parsear JSON:', parseError);
            console.error('No se pudo convertir a JSON. Texto:', textResponse);
            this.errorMessage = 'Respuesta inválida del servidor. Ver consola.';
            return;
        }
        
        if (result.success) {
            console.log('✅ Éxito!');
            alert('✓ ' + result.message);
            this.resetForm();
            await this.loadConditionals();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            console.warn('⚠️ Success = false:', result.message);
            this.errorMessage = result.message;
        }
        
    } catch (error) {
        console.error('❌ EXCEPCIÓN CAPTURADA:', error);
        console.error('Tipo de error:', error.name);
        console.error('Mensaje:', error.message);
        console.error('Stack:', error.stack);
        this.errorMessage = 'Error: ' + error.message;
    } finally {
        this.isSubmitting = false;
        console.log('=== FIN SUBMIT ===');
    }
},

        // Eliminar condicional
        async deleteConditional(conditionalId) {
            if (!confirm('¿Estás seguro de eliminar este condicional?')) {
                return;
            }
            
            try {
                const response = await fetch(`/checklist-items/${this.itemId}/conditionals/${conditionalId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✓ ' + result.message);
                    await this.loadConditionals();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('✗ ' + result.message);
                }
            } catch (error) {
                console.error('Error al eliminar condicional:', error);
                alert('✗ Error al eliminar condicional');
            }
        },
        
        // Resetear formulario
        resetForm() {
            this.newConditional = {
                doctor_id: '',
                hospital_id: '',
                modality_id: '',
                legal_entity_id: '',
                is_additional_product: false,
                quantity_override: null,
                additional_quantity: null,
                notes: ''
            };
            this.errorMessage = '';
        }
    }
}
</script>
@endpush


{{-- ============================================
     MODAL DE CONDICIONALES - Alpine.js
     ============================================ --}}
<div x-data="conditionalsModal()" 
     x-show="isOpen" 
     x-cloak
     @open-conditionals-modal.window="openModal($event.detail)"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" 
         @click="closeModal()"></div>
    
    {{-- Modal Content --}}
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
             @click.away="closeModal()">
            
            {{-- Header --}}
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-white">
                        <i class="fas fa-filter mr-3 text-2xl"></i>
                        <div>
                            <h3 class="text-lg font-bold">Gestionar Condicionales</h3>
                            <p class="text-sm text-purple-100" x-text="itemName"></p>
                        </div>
                    </div>
                    <button @click="closeModal()" 
                            class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="bg-white px-6 py-4 max-h-[70vh] overflow-y-auto">
                
                {{-- Info del Item --}}
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-blue-900">Cantidad Base del Checklist</p>
                            <p class="text-3xl font-bold text-blue-600" x-text="baseQuantity"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-blue-700">Condicionales Activos</p>
                            <p class="text-2xl font-bold text-purple-600" x-text="conditionals.length"></p>
                        </div>
                    </div>
                </div>

                {{-- Loading State --}}
                <div x-show="loading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-4xl text-purple-600 mb-3"></i>
                    <p class="text-gray-600">Cargando condicionales...</p>
                </div>

                {{-- Lista de Condicionales Existentes --}}
                <div x-show="!loading" class="mb-6">
                    <h4 class="text-md font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-list mr-2 text-indigo-600"></i>
                        Condicionales Configurados
                    </h4>
                    
                    <template x-if="conditionals.length === 0">
                        <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                            <i class="fas fa-inbox text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600 text-sm">No hay condicionales configurados</p>
                            <p class="text-gray-500 text-xs mt-1">Agrega uno usando el formulario de abajo</p>
                        </div>
                    </template>

                    <div class="space-y-3">
                        <template x-for="(cond, index) in conditionals" :key="cond.id">
                            <div class="p-4 bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-lg">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-gray-900 mb-2" x-text="cond.description"></p>
                                        
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div>
                                                <span class="text-gray-600">Doctor:</span>
                                                <span class="font-semibold text-gray-900" x-text="cond.doctor_name"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Hospital:</span>
                                                <span class="font-semibold text-gray-900" x-text="cond.hospital_name"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Modalidad:</span>
                                                <span class="font-semibold text-gray-900" x-text="cond.modality_name"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Razon Social:</span>
                                                <span class="font-semibold text-gray-900" x-text="cond.legal_entity_name"></span>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-600 text-white">
                                                <i class="fas fa-arrow-right mr-1"></i>
                                                Cantidad: <span class="ml-1" x-text="cond.is_additional_product ? cond.additional_quantity : cond.quantity_override"></span>
                                                <span class="ml-1" x-text="cond.is_additional_product ? '(Adicional)' : '(Reemplazo)'"></span>
                                            </span>
                                        </div>

                                        <template x-if="cond.notes">
                                            <p class="text-xs text-gray-600 mt-2 italic">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                <span x-text="cond.notes"></span>
                                            </p>
                                        </template>
                                    </div>
                                    
                                    <button @click="deleteConditional(cond.id)" 
                                            class="ml-4 text-red-600 hover:text-red-800 transition-colors"
                                            title="Eliminar condicional">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Formulario para Agregar Nuevo Condicional --}}
                <div x-show="!loading" class="border-t-2 border-gray-200 pt-6">
                    <h4 class="text-md font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-plus-circle mr-2 text-green-600"></i>
                        Agregar Nuevo Condicional
                    </h4>

                    <form @submit.prevent="submitConditional()" class="space-y-4">
                        
                        {{-- Criterios --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Doctor --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Doctor (opcional)
                                </label>
                                <select x-model="newConditional.doctor_id" 
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">-- Todos los doctores --</option>
                                    <template x-for="doctor in formData.doctors" :key="doctor.id">
                                        <option :value="doctor.id" x-text="doctor.name"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Hospital --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Hospital (opcional)
                                </label>
                                <select x-model="newConditional.hospital_id" 
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">-- Todos los hospitales --</option>
                                    <template x-for="hospital in formData.hospitals" :key="hospital.id">
                                        <option :value="hospital.id" x-text="hospital.name"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Modalidad --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Modalidad de Pago (opcional)
                                </label>
                                <select x-model="newConditional.modality_id" 
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">-- Todas las modalidades --</option>
                                    <template x-for="modality in formData.modalities" :key="modality.id">
                                        <option :value="modality.id" x-text="modality.name"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Legal Entity --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Legal Entity (opcional)
                                </label>
                                <select x-model="newConditional.legal_entity_id" 
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">-- Todas las entidades --</option>
                                    <template x-for="entity in formData.legal_entities" :key="entity.id">
                                        <option :value="entity.id" x-text="entity.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Tipo de Acción --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Producto <span class="text-red-500">*</span>
                            </label>
                            <div class="flex space-x-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           x-model="newConditional.is_additional_product" 
                                           :value="false" 
                                           class="text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Reemplazar cantidad base</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           x-model="newConditional.is_additional_product" 
                                           :value="true" 
                                           class="text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Producto adicional</span>
                                </label>
                            </div>
                        </div>

                        {{-- Cantidad --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Cantidad <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   x-model.number="newConditional.is_additional_product ? newConditional.additional_quantity : newConditional.quantity_override"
                                   min="0"
                                   required
                                   class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                   placeholder="Ingresa la cantidad">
                        </div>

                        {{-- Notas --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Notas (opcional)
                            </label>
                            <textarea x-model="newConditional.notes" 
                                      rows="2"
                                      class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                      placeholder="Ej: Dr. Pérez requiere instrumental adicional"></textarea>
                        </div>

                        {{-- Mensaje de Error --}}
                        <div x-show="errorMessage" 
                             x-transition
                             class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-800" x-text="errorMessage"></p>
                        </div>

                        {{-- Botones --}}
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" 
                                    @click="closeModal()" 
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                                Cerrar
                            </button>
                            <button type="submit" 
                                    :disabled="isSubmitting"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="isSubmitting">
                                    <span><i class="fas fa-spinner fa-spin mr-2"></i>Guardando...</span>
                                </template>
                                <template x-if="!isSubmitting">
                                    <span><i class="fas fa-save mr-2"></i>Guardar Condicional</span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Estilos para x-cloak --}}
<style>
    [x-cloak] { 
        display: none !important; 
    }
</style>


</x-app-layout>