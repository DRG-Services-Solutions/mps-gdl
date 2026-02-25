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
            
            <!-- ============================================
                 AGREGAR PRODUCTO
                 ============================================ -->
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
                                   class="w-full rounded-lg focus:border-purple-500 focus:ring-purple-500 @error('quantity') border-red-500 @enderror"
                                   required>
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Botón -->
                        <div class="flex items-end gap-4">
                            <button type="submit" 
                                    class="w-[250px] px-4 py-2.5 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas fa-plus mr-1"></i>
                                Agregar
                            </button>
                            <button type="button"
                                    @click="$dispatch('open-bulk-import-modal')"
                                    class="w-[450px] px-4 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-file-upload mr-1"></i>
                                Carga Masiva
                            </button>
                        </div>
                    </div>
                </form>
            </div>

       <!-- ============================================
                 LISTA DE ITEMS DEL CHECKLIST
                 ============================================ -->
            <div class="bg-white rounded-lg shadow-sm">
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
                                
                                {{-- BOTÓN DE CONDICIONALES --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button type="button" 
                                            @click="$dispatch('open-conditionals-modal', { 
                                                itemId: {{ $item->id }}, 
                                                itemName: '{{ addslashes($item->product->name) }}',
                                                baseQuantity: {{ $item->quantity }}
                                            })"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                                                   {{ $item->conditionals->count() > 0 
                                                      ? 'bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-800 hover:from-purple-200 hover:to-indigo-200 border border-purple-300' 
                                                      : 'bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-300' }}">
                                        <i class="fas fa-filter mr-1.5"></i>
                                        <span class="font-bold">{{ $item->conditionals->count() }}</span>
                                        <span class="ml-1">{{ $item->conditionals->count() === 1 ? 'condicional' : 'condicionales' }}</span>
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
                                                class="text-red-600 hover:text-red-900 transition-colors"
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

                {{-- PAGINACIÓN --}}
                @if($items->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $items->links() }}
                </div>
                @endif

                @else
                {{-- ESTADO VACÍO --}}
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
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full"
             @click.away="closeModal()">
            
            {{-- ===== HEADER ===== --}}
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

            {{-- ===== BODY ===== --}}
            <div class="bg-white px-6 py-4 max-h-[70vh] overflow-y-auto">
                
                {{-- INFO DEL ITEM --}}
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

                {{-- LOADING STATE --}}
                <div x-show="loading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-4xl text-purple-600 mb-3"></i>
                    <p class="text-gray-600">Cargando condicionales...</p>
                </div>

                {{-- ===== LISTA DE CONDICIONALES EXISTENTES ===== --}}
                <div x-show="!loading" class="mb-6">
                    <h4 class="text-md font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-list mr-2 text-indigo-600"></i>
                        Condicionales Configurados
                    </h4>
                    
                    {{-- ESTADO VACÍO --}}
                    <template x-if="conditionals.length === 0">
                        <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                            <i class="fas fa-inbox text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600 text-sm">No hay condicionales configurados</p>
                            <p class="text-gray-500 text-xs mt-1">Agrega uno usando el formulario de abajo</p>
                        </div>
                    </template>

                    {{-- LISTA DE CONDICIONALES --}}
                    <div class="space-y-3">
                        <template x-for="(cond, index) in conditionals" :key="cond.id">
                            <div class="p-4 rounded-lg border-2 transition-all hover:shadow-md"
                                 :class="{
                                     'bg-gradient-to-r from-purple-50 to-indigo-50 border-purple-300': cond.action_type === 'adjust_quantity',
                                     'bg-gradient-to-r from-green-50 to-emerald-50 border-green-300': cond.action_type === 'add_product',
                                     'bg-gradient-to-r from-red-50 to-rose-50 border-red-300': cond.action_type === 'exclude',
                                     'bg-gradient-to-r from-orange-50 to-amber-50 border-orange-300': cond.action_type === 'replace',
                                     'bg-gradient-to-r from-blue-50 to-cyan-50 border-blue-300': cond.action_type === 'add_dependency'
                                 }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        {{-- TIPO DE ACCIÓN + MODIFICADORES (reemplaza el bloque "TIPO DE ACCIÓN" dentro del x-for) --}}
                                        <div class="mb-3 flex flex-wrap items-center gap-2">

                                            {{-- Badge principal: tipo de acción --}}
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"
                                                :class="{
                                                    'bg-purple-600 text-white': cond.action_type === 'adjust_quantity',
                                                    'bg-green-600 text-white':  cond.action_type === 'add_product',
                                                    'bg-red-600 text-white':    cond.action_type === 'exclude',
                                                    'bg-orange-600 text-white': cond.action_type === 'replace',
                                                    'bg-blue-600 text-white':   cond.action_type === 'add_dependency'
                                                }">
                                                <i class="fas mr-1.5"
                                                :class="{
                                                    'fa-edit':         cond.action_type === 'adjust_quantity',
                                                    'fa-plus-circle':  cond.action_type === 'add_product',
                                                    'fa-times-circle': cond.action_type === 'exclude',
                                                    'fa-exchange-alt': cond.action_type === 'replace',
                                                    'fa-link':         cond.action_type === 'add_dependency'
                                                }"></i>
                                                <span x-text="cond.action_description"></span>
                                            </span>

                                            {{-- Badge: No remisionar --}}
                                            <template x-if="cond.exclude_from_invoice">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                    <i class="fas fa-file-invoice mr-1"></i>
                                                    No remisionar
                                                </span>
                                            </template>

                                            {{-- Badge: Requiere aprobación --}}
                                            <template x-if="cond.requires_approval">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-violet-100 text-violet-800 border border-violet-300">
                                                    <i class="fas fa-user-shield mr-1"></i>
                                                    Requiere aprobación
                                                </span>
                                            </template>

                                        </div>

                                        {{-- CRITERIOS --}}
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
                                                <span class="text-gray-600">Razón Social:</span>
                                                <span class="font-semibold text-gray-900" x-text="cond.legal_entity_name"></span>
                                            </div>
                                        </div>

                                        {{-- PRODUCTO OBJETIVO (dependencias/reemplazos) --}}
                                        <template x-if="cond.target_product_name">
                                            <div class="mt-3 p-2 bg-white bg-opacity-60 rounded border border-gray-300">
                                                <p class="text-xs font-semibold text-gray-700">
                                                    <i class="fas fa-box-open mr-1"></i>
                                                    <span x-text="cond.action_type === 'replace' ? 'Reemplazar por:' : 'Requiere:'"></span>
                                                    <span class="text-indigo-700" x-text="cond.target_product_name"></span>
                                                </p>
                                            </div>
                                        </template>

                                        {{-- NOTAS --}}
                                        <template x-if="cond.notes">
                                            <p class="text-xs text-gray-600 mt-2 italic bg-white bg-opacity-40 p-2 rounded">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                <span x-text="cond.notes"></span>
                                            </p>
                                        </template>

                                        {{-- NIVEL DE ESPECIFICIDAD --}}
                                        <div class="mt-2">
                                            <span class="text-xs text-gray-500">
                                                Especificidad: 
                                                <span class="font-bold" x-text="cond.specificity_level"></span>/4
                                            </span>
                                        </div>
                                    </div>
                                    
                                    {{-- BOTÓN ELIMINAR --}}
                                    <button @click="deleteConditional(cond.id)" 
                                            class="ml-4 text-red-600 hover:text-red-800 hover:bg-red-100 p-2 rounded-lg transition-all"
                                            title="Eliminar condicional">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

{{-- ===== FORMULARIO PARA AGREGAR NUEVO CONDICIONAL ===== --}}
                <div x-show="!loading" class="border-t-2 border-gray-200 pt-6">
                    <h4 class="text-md font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-plus-circle mr-2 text-green-600"></i>
                        Agregar Nuevo Condicional
                    </h4>

                    <form @submit.prevent="submitConditional()" class="space-y-4">
                        
                        {{-- ===== CRITERIOS DE APLICACIÓN ===== --}}
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h5 class="text-sm font-bold text-gray-700 mb-3">
                                <i class="fas fa-filter mr-1"></i>
                                Criterios de Aplicación (al menos uno)
                            </h5>
                            
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
                                        Razón Social (opcional)
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
                        </div>

                        {{-- ===== TIPO DE ACCIÓN ===== --}}
                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                            <label class="block text-sm font-bold text-gray-700 mb-3">
                                <i class="fas fa-cog mr-1"></i>
                                Tipo de Acción <span class="text-red-500">*</span>
                            </label>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                {{-- Ajustar Cantidad --}}
                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md"
                                       :class="newConditional.action_type === 'adjust_quantity' ? 'border-purple-500 bg-purple-50' : 'border-gray-300 bg-white'">
                                    <input type="radio" 
                                           x-model="newConditional.action_type" 
                                           value="adjust_quantity" 
                                           class="mt-1 text-purple-600 focus:ring-purple-500">
                                    <div class="ml-3">
                                        <div class="text-sm font-bold text-gray-900">
                                            <i class="fas fa-edit text-purple-600 mr-1"></i>
                                            Ajustar Cantidad
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">Reemplaza la cantidad base del checklist</p>
                                    </div>
                                </label>

                                {{-- Agregar Producto --}}
                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md"
                                       :class="newConditional.action_type === 'add_product' ? 'border-green-500 bg-green-50' : 'border-gray-300 bg-white'">
                                    <input type="radio" 
                                           x-model="newConditional.action_type" 
                                           value="add_product" 
                                           class="mt-1 text-green-600 focus:ring-green-500">
                                    <div class="ml-3">
                                        <div class="text-sm font-bold text-gray-900">
                                            <i class="fas fa-plus-circle text-green-600 mr-1"></i>
                                            Agregar Adicional
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">Suma unidades extras al producto</p>
                                    </div>
                                </label>

                                {{-- Excluir Producto --}}
                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md"
                                       :class="newConditional.action_type === 'exclude' ? 'border-red-500 bg-red-50' : 'border-gray-300 bg-white'">
                                    <input type="radio" 
                                           x-model="newConditional.action_type" 
                                           value="exclude" 
                                           class="mt-1 text-red-600 focus:ring-red-500">
                                    <div class="ml-3">
                                        <div class="text-sm font-bold text-gray-900">
                                            <i class="fas fa-times-circle text-red-600 mr-1"></i>
                                            Excluir Producto
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">No incluir este producto en el checklist</p>
                                    </div>
                                </label>

                                {{-- Reemplazar Producto --}}
                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md"
                                       :class="newConditional.action_type === 'replace' ? 'border-orange-500 bg-orange-50' : 'border-gray-300 bg-white'">
                                    <input type="radio" 
                                           x-model="newConditional.action_type" 
                                           value="replace" 
                                           class="mt-1 text-orange-600 focus:ring-orange-500">
                                    <div class="ml-3">
                                        <div class="text-sm font-bold text-gray-900">
                                            <i class="fas fa-exchange-alt text-orange-600 mr-1"></i>
                                            Reemplazar
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">Sustituir por otro producto</p>
                                    </div>
                                </label>

                                {{-- Agregar Dependencia --}}
                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md col-span-full"
                                       :class="newConditional.action_type === 'add_dependency' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-white'">
                                    <input type="radio" 
                                           x-model="newConditional.action_type" 
                                           value="add_dependency" 
                                           class="mt-1 text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="text-sm font-bold text-gray-900">
                                            <i class="fas fa-link text-blue-600 mr-1"></i>
                                            Agregar Dependencia
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">Este producto requiere otro para funcionar (ej: broca → taladro)</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- ===== CAMPOS DINÁMICOS SEGÚN ACTION_TYPE ===== --}}
                        
                        {{-- AJUSTAR CANTIDAD --}}
                        <div x-show="newConditional.action_type === 'adjust_quantity'" x-transition class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nueva Cantidad <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   x-model.number="newConditional.quantity_override"
                                   min="0"
                                   class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                   placeholder="Ejemplo: 5">
                            <p class="text-xs text-gray-600 mt-1">Esta cantidad reemplazará la cantidad base del checklist</p>
                        </div>

                        {{-- AGREGAR PRODUCTO ADICIONAL --}}
                        <div x-show="newConditional.action_type === 'add_product'" x-transition class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Cantidad Adicional <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   x-model.number="newConditional.additional_quantity"
                                   min="1"
                                   class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
                                   placeholder="Ejemplo: 3">
                            <p class="text-xs text-gray-600 mt-1">Se sumarán estas unidades a la cantidad base</p>
                        </div>

                        {{-- EXCLUIR - Solo mensaje informativo --}}
                        <div x-show="newConditional.action_type === 'exclude'" x-transition class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-red-600 mt-0.5 mr-2"></i>
                                <div class="text-sm text-gray-700">
                                    <p class="font-semibold">El producto no se incluirá en el checklist</p>
                                    <p class="text-xs mt-1">Cuando se cumplan los criterios seleccionados, este producto se omitirá completamente.</p>
                                </div>
                            </div>
                        </div>

                        {{-- REEMPLAZAR PRODUCTO --}}
                        <div x-show="newConditional.action_type === 'replace'" x-transition class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Producto de Reemplazo <span class="text-red-500">*</span>
                            </label>
                            <select x-model="newConditional.target_product_id" 
                                    class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                                <option value="">-- Selecciona un producto --</option>
                                <template x-for="product in formData.products" :key="product.id">
                                    <option :value="product.id" x-text="product.label"></option>
                                </template>
                            </select>
                            <p class="text-xs text-gray-600 mt-1">Este producto sustituirá al original cuando se cumplan los criterios</p>
                        </div>

                        {{-- AGREGAR DEPENDENCIA --}}
                        <div x-show="newConditional.action_type === 'add_dependency'" x-transition class="bg-blue-50 p-4 rounded-lg border border-blue-200 space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Producto Requerido <span class="text-red-500">*</span>
                                </label>
                                <select x-model="newConditional.target_product_id" 
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Selecciona el producto dependiente --</option>
                                    <template x-for="product in formData.products" :key="product.id">
                                        <option :value="product.id" x-text="product.label"></option>
                                    </template>
                                </select>
                                <p class="text-xs text-gray-600 mt-1">Producto necesario para el funcionamiento</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Cantidad Requerida <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       x-model.number="newConditional.dependency_quantity"
                                       min="1"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Ejemplo: 1">
                                <p class="text-xs text-gray-600 mt-1">Ejemplo: Broca requiere 1 taladro</p>
                            </div>
                        </div>

                        {{-- ===== MODIFICADORES TRANSVERSALES ===== --}}
                        <div class="rounded-lg border-2 border-dashed border-gray-300 overflow-hidden">

                            {{-- Header de la sección --}}
                            <div class="px-4 py-3 bg-gray-100 border-b border-gray-300 flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-sliders-h mr-2 text-gray-500"></i>
                                    <span class="text-sm font-bold text-gray-700">Modificadores adicionales</span>
                                </div>
                                <span class="text-xs text-gray-500 italic">Aplican independientemente del tipo de acción</span>
                            </div>

                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">

                                {{-- No remisionar --}}
                                <label
                                    class="flex items-start gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all duration-150 select-none"
                                    :class="newConditional.exclude_from_invoice
                                        ? 'border-amber-400 bg-amber-50'
                                        : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'"
                                >
                                    <input
                                        type="checkbox"
                                        x-model="newConditional.exclude_from_invoice"
                                        class="mt-0.5 rounded text-amber-500 focus:ring-amber-400 cursor-pointer"
                                    >
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-file-invoice text-amber-500 text-sm"></i>
                                            <span class="text-sm font-semibold text-gray-900">No agregar en remisión</span>
                                            <template x-if="newConditional.exclude_from_invoice">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-700 border border-amber-300">
                                                    Activo
                                                </span>
                                            </template>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                            El producto se despacha físicamente pero <strong>no aparece</strong> en la remisión ni se factura al cliente.
                                        </p>
                                    </div>
                                </label>

                                {{-- Requiere aprobación --}}
                                <label
                                    class="flex items-start gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all duration-150 select-none"
                                    :class="newConditional.requires_approval
                                        ? 'border-violet-400 bg-violet-50'
                                        : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'"
                                >
                                    <input
                                        type="checkbox"
                                        x-model="newConditional.requires_approval"
                                        class="mt-0.5 rounded text-violet-500 focus:ring-violet-400 cursor-pointer"
                                    >
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-user-shield text-violet-500 text-sm"></i>
                                            <span class="text-sm font-semibold text-gray-900">Requiere aprobación manual</span>
                                            <template x-if="newConditional.requires_approval">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-violet-100 text-violet-700 border border-violet-300">
                                                    Activo
                                                </span>
                                            </template>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                            Este condicional <strong>no se aplica automáticamente</strong>; debe ser revisado y aprobado por un supervisor antes de surtirse.
                                        </p>
                                    </div>
                                </label>

                            </div>
                        </div>

                        {{-- ===== NOTAS ===== --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Notas (opcional)
                            </label>
                            <textarea x-model="newConditional.notes" 
                                      rows="2"
                                      class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                      placeholder="Ej: Dr. Pérez requiere instrumental adicional para cirugías complejas"></textarea>
                        </div>

                        {{-- ===== MENSAJE DE ERROR/WARNING ===== --}}
                        <div x-show="errorMessage" 
                             x-transition
                             class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-red-600 mt-0.5 mr-2"></i>
                                <p class="text-sm text-red-800" x-text="errorMessage"></p>
                            </div>
                        </div>

                        <div x-show="warnings.length > 0" 
                             x-transition
                             class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-circle text-yellow-600 mt-0.5 mr-2"></i>
                                <div>
                                    <p class="text-sm font-semibold text-yellow-800">Advertencias:</p>
                                    <ul class="text-sm text-yellow-700 mt-1 space-y-1">
                                        <template x-for="warning in warnings" :key="warning">
                                            <li class="text-xs" x-text="'• ' + warning"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- ===== BOTONES ===== --}}
                        <div class="flex justify-end space-x-3 pt-4 border-t">
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
<div x-data="bulkImportModal()" 
     x-show="isOpen" 
     x-cloak
     @open-bulk-import-modal.window="openModal()"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" 
         @click="closeModal()"></div>
    
    {{-- Modal --}}
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden"
             @click.away="closeModal()">
            
            {{-- Header --}}
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-white">
                        <i class="fas fa-file-upload mr-3 text-2xl"></i>
                        <div>
                            <h3 class="text-lg font-bold">Carga Masiva de Productos</h3>
                            <p class="text-sm text-green-100">{{ $checklist->code }} - {{ $checklist->surgery_type }}</p>
                        </div>
                    </div>
                    <button @click="closeModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">
                
                {{-- ===== PASO 1: SUBIR ARCHIVO ===== --}}
                <div x-show="step === 'upload'">
                    
                    {{-- Descargar plantilla --}}
                    <div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-900">Paso 1: Descarga la plantilla</p>
                                <p class="text-xs text-blue-700 mt-1">Llénala con los SKU y cantidades de los productos</p>
                            </div>
                            <a href="{{ route('checklist-items.bulk-template', $checklist) }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors">
                                <i class="fas fa-download mr-1.5"></i>
                                Plantilla .xlsx
                            </a>
                        </div>
                    </div>

                    {{-- Drop zone --}}
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Paso 2: Sube tu archivo</p>
                        
                        <div id="bulkDropZone"
                             @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="handleDrop($event)"
                             @click="$refs.bulkFileInput.click()"
                             class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-all duration-200"
                             :class="isDragging ? 'border-green-400 bg-green-50' : (selectedFile ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-green-400')">
                            
                            {{-- Sin archivo --}}
                            <div x-show="!selectedFile">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-600">Arrastra tu archivo aquí o haz clic</p>
                                <p class="text-xs text-gray-400 mt-1">.xlsx o .xls (máx. 5MB)</p>
                            </div>

                            {{-- Con archivo --}}
                            <div x-show="selectedFile">
                                <i class="fas fa-file-excel text-3xl text-green-500 mb-2"></i>
                                <p class="text-sm font-medium text-gray-800" x-text="selectedFile?.name"></p>
                                <p class="text-xs text-gray-500" x-text="selectedFile ? (selectedFile.size / 1024).toFixed(1) + ' KB' : ''"></p>
                            </div>

                            <input type="file" 
                                   x-ref="bulkFileInput"
                                   @change="handleFileSelect($event)"
                                   accept=".xlsx,.xls"
                                   class="hidden">
                        </div>
                    </div>

                    {{-- Formato esperado --}}
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                        <p class="text-xs font-semibold text-gray-600 mb-2">
                            <i class="fas fa-info-circle mr-1"></i> Formato esperado:
                        </p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-gray-500">
                                        <th class="px-2 py-1 text-left font-semibold">product_sku</th>
                                        <th class="px-2 py-1 text-left font-semibold">quantity</th>
                                        <th class="px-2 py-1 text-left font-semibold">notes</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600">
                                    <tr>
                                        <td class="px-2 py-1 font-mono">PROD-001</td>
                                        <td class="px-2 py-1">5</td>
                                        <td class="px-2 py-1">Opcional</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ===== PASO 2: PROCESANDO ===== --}}
                <div x-show="step === 'processing'" class="text-center py-10">
                    <i class="fas fa-spinner fa-spin text-4xl text-green-600 mb-4"></i>
                    <p class="text-gray-700 font-medium">Procesando archivo...</p>
                    <p class="text-sm text-gray-500 mt-1">Validando SKUs contra el catálogo de productos</p>
                </div>

                {{-- ===== PASO 3: RESULTADOS ===== --}}
                <div x-show="step === 'results'">
                    
                    {{-- Resumen --}}
                    <div class="grid grid-cols-3 gap-3 mb-5">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold text-green-600" x-text="result.created"></p>
                            <p class="text-xs text-green-700">Agregados</p>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold text-yellow-600" x-text="result.skipped"></p>
                            <p class="text-xs text-yellow-700">Omitidos</p>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold text-red-600" x-text="result.errors?.length || 0"></p>
                            <p class="text-xs text-red-700">Errores</p>
                        </div>
                    </div>

                    {{-- Mensaje --}}
                    <div class="mb-4 p-3 rounded-lg border"
                         :class="result.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                        <div class="flex items-start">
                            <i class="fas mt-0.5 mr-2" 
                               :class="result.success ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600'"></i>
                            <p class="text-sm" 
                               :class="result.success ? 'text-green-800' : 'text-red-800'"
                               x-text="result.message"></p>
                        </div>
                    </div>

                    {{-- Detalle por fila --}}
                    <div x-show="result.preview && result.preview.length > 0" class="mb-4">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Detalle:</p>
                        <div class="max-h-48 overflow-y-auto border rounded-lg">
                            <table class="w-full text-xs">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">SKU</th>
                                        <th class="px-3 py-2 text-center font-medium text-gray-500">Cant.</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-for="(item, i) in result.preview" :key="i">
                                        <tr :class="{
                                            'bg-green-50': item.status === 'created',
                                            'bg-yellow-50': item.status === 'skipped',
                                            'bg-red-50': item.status === 'error'
                                        }">
                                            <td class="px-3 py-1.5 font-mono" x-text="item.sku"></td>
                                            <td class="px-3 py-1.5 text-center" x-text="item.qty"></td>
                                            <td class="px-3 py-1.5">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                      :class="{
                                                          'bg-green-100 text-green-800': item.status === 'created',
                                                          'bg-yellow-100 text-yellow-800': item.status === 'skipped',
                                                          'bg-red-100 text-red-800': item.status === 'error'
                                                      }">
                                                    <span x-text="item.reason"></span>
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Errores detallados --}}
                    <div x-show="result.errors && result.errors.length > 0">
                        <p class="text-sm font-semibold text-red-700 mb-2">Errores:</p>
                        <div class="max-h-32 overflow-y-auto p-3 bg-red-50 border border-red-200 rounded-lg">
                            <template x-for="(err, i) in result.errors" :key="i">
                                <p class="text-xs text-red-700 mb-1" x-text="err"></p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                <button @click="closeModal()" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <span x-text="step === 'results' ? 'Cerrar' : 'Cancelar'"></span>
                </button>

                {{-- Botón subir --}}
                <button x-show="step === 'upload'"
                        @click="uploadFile()"
                        :disabled="!selectedFile"
                        class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-upload mr-1.5"></i>
                    Importar
                </button>

                {{-- Botón recargar --}}
                <button x-show="step === 'results' && result.success && result.created > 0"
                        @click="window.location.reload()"
                        class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-sync mr-1.5"></i>
                    Recargar Página
                </button>
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
        warnings: [],
        
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
            legal_entities: [],
            products: []
        },
        
        // Nuevo condicional
        newConditional: {
            doctor_id: '',
            hospital_id: '',
            modality_id: '',
            legal_entity_id: '',
            action_type: 'adjust_quantity',
            quantity_override: null,
            additional_quantity: null,
            target_product_id: '',
            dependency_quantity: 1,
            exclude_from_invoice: false,
            requires_approval: false,
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
            this.warnings = [];
            
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
            this.warnings = [];
            
            console.log('=== INICIO SUBMIT ===');
            console.log('Item ID:', this.itemId);
            console.log('Action Type:', this.newConditional.action_type);
            
            try {
                const data = {
                    doctor_id: this.newConditional.doctor_id || null,
                    hospital_id: this.newConditional.hospital_id || null,
                    modality_id: this.newConditional.modality_id || null,
                    legal_entity_id: this.newConditional.legal_entity_id || null,
                    action_type: this.newConditional.action_type,
                    quantity_override: this.newConditional.action_type === 'adjust_quantity' ? this.newConditional.quantity_override : null,
                    additional_quantity: this.newConditional.action_type === 'add_product' ? this.newConditional.additional_quantity : null,
                    target_product_id: ['replace', 'add_dependency'].includes(this.newConditional.action_type) ? this.newConditional.target_product_id : null,
                    dependency_quantity: this.newConditional.action_type === 'add_dependency' ? this.newConditional.dependency_quantity : null,
                    exclude_from_invoice: this.newConditional.exclude_from_invoice,
                    requires_approval: this.newConditional.requires_approval,
                    notes: this.newConditional.notes || null
                };
                
                console.log('📤 Datos a enviar:', data);
                
                const url = `/checklist-items/${this.itemId}/conditionals`;
                console.log('🌐 URL:', url);
                
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
                
                const result = await response.json();
                console.log('📥 Respuesta:', result);
                
                if (result.success) {
                    console.log('✅ Éxito!');
                    
                    // Mostrar warnings si existen
                    if (result.warnings && result.warnings.length > 0) {
                        this.warnings = result.warnings;
                        console.warn('⚠️ Warnings:', result.warnings);
                    }
                    
                    alert('✓ ' + result.message);
                    this.resetForm();
                    await this.loadConditionals();
                    
                    if (this.warnings.length === 0) {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    console.warn('⚠️ Success = false:', result.message);
                    this.errorMessage = result.message;
                    
                    // Mostrar conflictos si existen
                    if (result.conflicts) {
                        this.errorMessage += '\n\n' + result.conflicts.join('\n');
                    }
                }
                
            } catch (error) {
                console.error('❌ EXCEPCIÓN:', error);
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
                action_type: 'adjust_quantity',
                quantity_override: null,
                additional_quantity: null,
                target_product_id: '',
                dependency_quantity: 1,
                exclude_from_invoice: false,
                requires_approval: false,
                notes: ''
            };
            this.errorMessage = '';
            this.warnings = [];
        }
    }
}


function bulkImportModal() {
    return {
        isOpen: false,
        step: 'upload', // upload | processing | results
        isDragging: false,
        selectedFile: null,
        result: {},

        openModal() {
            this.isOpen = true;
            this.step = 'upload';
            this.selectedFile = null;
            this.result = {};
        },

        closeModal() {
            // Si hubo items creados, recargar al cerrar
            if (this.result.success && this.result.created > 0) {
                window.location.reload();
                return;
            }
            this.isOpen = false;
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) this.selectedFile = file;
        },

        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls'))) {
                this.selectedFile = file;
            }
        },

        async uploadFile() {
            if (!this.selectedFile) return;

            this.step = 'processing';

            const formData = new FormData();
            formData.append('file', this.selectedFile);

            try {
                const response = await fetch('{{ route("checklist-items.bulk-import", $checklist) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                this.result = await response.json();
                this.step = 'results';

            } catch (error) {
                this.result = {
                    success: false,
                    message: 'Error de conexión: ' + error.message,
                    created: 0,
                    skipped: 0,
                    errors: [],
                    preview: [],
                };
                this.step = 'results';
            }
        },
    }
}

</script>
@endpush

</x-app-layout>