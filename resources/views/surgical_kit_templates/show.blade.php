<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('surgical_kit_templates.index') }}" class="text-gray-500 hover:text-indigo-600 mr-4 transition-colors">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center">
                        <i class="fas fa-medkit mr-2 text-indigo-600"></i>
                        {{ $surgicalKitTemplate->name }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Detalles de la lista</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('surgical_kit_templates.edit', $surgicalKitTemplate) }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Editar Lista
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm" role="alert">
                    <p class="font-medium"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- ============================================
                     COLUMNA IZQUIERDA: Información General
                     ============================================ --}}
                <div class="bg-white rounded-lg shadow-sm p-6 h-fit">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-4">
                        <i class="fas fa-info-circle text-gray-400 mr-2"></i>Información General
                    </h3>

                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Código de Referencia</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded inline-block">
                                {{ $surgicalKitTemplate->code ?? 'Sin código' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">CheckList Asociado</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded inline-block">
                                {{ $surgicalKitTemplate->surgery_type }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Estado</dt>
                            <dd class="mt-1">
                                @if($surgicalKitTemplate->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactivo
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                                {{ $surgicalKitTemplate->description ?? 'No hay descripción registrada para este kit.' }}
                            </dd>
                        </div>

                        <div class="pt-4 mt-4 border-t">
                            <dt class="text-sm font-medium text-gray-500">Total de Artículos</dt>
                            <dd class="mt-1 text-2xl font-semibold text-indigo-600">
                                {{ $surgicalKitTemplate->items->count() }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- ============================================
                     COLUMNA DERECHA: Formulario + Tabla
                     ============================================ --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- ============================================
                         AGREGAR ARTÍCULO
                         ============================================ --}}
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-blue-50">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                                Agregar Instrumental al Kit
                            </h3>
                        </div>

                        <form action="{{ route('surgical_kit_template_items.store') }}" method="POST" class="p-6">
                            @csrf
                            <input type="hidden" name="surgical_kit_template_id" value="{{ $surgicalKitTemplate->id }}">

                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                {{-- Producto --}}
                                <div class="md:col-span-2">
                                
                                  
                                 
                                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">

                                        Instrumento / Artículo <span class="text-red-500">*</span> 
                                    </label>
                                  
                                  
                                    <select id="product_id" name="product_id" required>
                                        <option value="">Selecciona un artículo...</option>
                                        
                                        @foreach($products as $product)
                                            
                                                <option
                                                    value="{{ $product->id }}"
                                                    data-name="{{ $product->name }}"
                                                    data-code="{{ $product->code }}"
                                                    {{ old('product_id') == $product->id ? 'selected' : '' }}
                                                >
                                                    {{ $product->code }} - {{ $product->name }}
                                                </option>
                                        
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Cantidad --}}
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                        Cantidad <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number"
                                           name="quantity"
                                           id="quantity"
                                           min="1"
                                           value="1"
                                           class="w-full rounded-lg focus:border-indigo-500 focus:ring-indigo-500 @error('quantity') border-red-500 @enderror"
                                           required>
                                    @error('quantity')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Botones --}}
                                <div class="flex items-end gap-3 md:col-span-2">
                                    <button type="submit"
                                            class="px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors whitespace-nowrap">
                                        <i class="fas fa-plus mr-1"></i>
                                        Agregar
                                    </button>
                                    <button type="button"
                                            @click="$dispatch('open-bulk-import-modal')"
                                            class="px-4 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap">
                                        <i class="fas fa-file-upload mr-1"></i>
                                        Carga Masiva
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- ============================================
                         LISTA DE ARTÍCULOS
                         ============================================ --}}
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-list mr-2 text-indigo-600"></i>
                                Contenido Actual ({{ $surgicalKitTemplate->items->count() }})
                            </h3>
                        </div>

                        @if($surgicalKitTemplate->items->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Artículo / Instrumental</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Condicionales</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($surgicalKitTemplate->items as $item)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-tools text-indigo-600"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        {{ $item->product->code ?? '—' }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $item->product->name ?? 'Producto eliminado del catálogo' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Cantidad editable inline con autosubmit --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <form action="{{ route('surgical_kit_template_items.update', $item) }}"
                                                  method="POST"
                                                  class="inline-flex items-center justify-center"
                                                  onchange="this.submit()">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="surgical_kit_template_id" value="{{ $surgicalKitTemplate->id }}">
                                                <input type="number"
                                                       name="quantity_required"
                                                       value="{{ $item->quantity_required  }}"
                                                       min="1"
                                                       class="w-20 text-center rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            </form>
                                        </td>
                                        <!-- Condicionales -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button type="button"
                                                    @click="$dispatch('open-kit-conditionals-modal', {
                                                        itemId: {{ $item->id }},
                                                        itemName: '{{ addslashes($item->product->name ?? '') }}',
                                                        baseQuantity: {{ $item->quantity_required }}
                                                    })"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                                                        {{ $item->conditionals->count() > 0
                                                            ? 'bg-gradient-to-r from-indigo-100 to-blue-100 text-indigo-800 hover:from-indigo-200 hover:to-blue-200 border border-indigo-300'
                                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-300' }}">
                                                <i class="fas fa-filter mr-1.5"></i>
                                                <span class="font-bold">{{ $item->conditionals->count() }}</span>
                                                <span class="ml-1">{{ $item->conditionals->count() === 1 ? 'condicional' : 'condicionales' }}</span>
                                            </button>
                                        </td>


                                        {{-- Acciones --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form action="{{ route('surgical_kit_template_items.destroy', $item) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('¿Quitar este artículo del kit quirúrgico?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-600 hover:text-red-900 transition-colors"
                                                        title="Quitar del kit">
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
                        {{-- Estado vacío --}}
                        <div class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-box-open text-4xl mb-3"></i>
                                <p class="text-sm font-medium text-gray-900 mb-2">Kit vacío</p>
                                <p class="text-xs text-gray-600">Agrega instrumental usando el formulario de arriba</p>
                            </div>
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ============================================
         MODAL DE CARGA MASIVA - Alpine.js
         ============================================ --}}
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
                                <h3 class="text-lg font-bold">Carga Masiva de Instrumental</h3>
                                <p class="text-sm text-green-100">{{ $surgicalKitTemplate->code }} - {{ $surgicalKitTemplate->name }}</p>
                            </div>
                        </div>
                        <button @click="closeModal()" class="text-white hover:text-gray-200 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">

                    {{-- PASO 1: SUBIR ARCHIVO --}}
                    <div x-show="step === 'upload'">

                        {{-- Descargar plantilla --}}
                        <div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-blue-900">Paso 1: Descarga la plantilla</p>
                                    <p class="text-xs text-blue-700 mt-1">Llénala con los SKU y cantidades del instrumental</p>
                                </div>
                                <a href="{{ route('surgical_kit_template_items.bulk-template', $surgicalKitTemplate) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors">
                                    <i class="fas fa-download mr-1.5"></i>
                                    Plantilla .xlsx
                                </a>
                            </div>
                        </div>

                        {{-- Drop zone --}}
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Paso 2: Sube tu archivo</p>

                            <div @dragover.prevent="isDragging = true"
                                 @dragleave.prevent="isDragging = false"
                                 @drop.prevent="handleDrop($event)"
                                 @click="$refs.bulkFileInput.click()"
                                 class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-all duration-200"
                                 :class="isDragging ? 'border-green-400 bg-green-50' : (selectedFile ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-green-400')">

                                <div x-show="!selectedFile">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Arrastra tu archivo aquí o haz clic</p>
                                    <p class="text-xs text-gray-400 mt-1">.xlsx o .xls (máx. 5MB)</p>
                                </div>

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
                                            <td class="px-2 py-1 font-mono">INST-001</td>
                                            <td class="px-2 py-1">5</td>
                                            <td class="px-2 py-1">Opcional</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{--  PROCESANDO --}}
                    <div x-show="step === 'processing'" class="text-center py-10">
                        <i class="fas fa-spinner fa-spin text-4xl text-green-600 mb-4"></i>
                        <p class="text-gray-700 font-medium">Procesando archivo...</p>
                        <p class="text-sm text-gray-500 mt-1">Validando SKUs contra el catálogo de productos</p>
                    </div>

                    {{-- RESULTADOS --}}
                    <div x-show="step === 'results'">

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

                    <button x-show="step === 'upload'"
                            @click="uploadFile()"
                            :disabled="!selectedFile"
                            class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-upload mr-1.5"></i>
                        Importar
                    </button>

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

    {{-- x-cloak --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>

@push('styles')
<style>
    /* ========================================
       TOM SELECT
       ======================================== */
    .ts-wrapper { position: relative !important; }

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
    .ts-control:hover { border-color: #9ca3af !important; }
    .ts-control.focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        outline: none !important;
    }
    .ts-control input { color: #111827 !important; font-size: 0.875rem !important; }
    .ts-control input::placeholder { color: #9ca3af !important; }
    .ts-control .item {
        color: #111827 !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        font-size: 0.875rem !important;
    }
    .ts-dropdown {
        position: absolute !important;
        z-index: 10000 !important;
        margin-top: 0.25rem !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        background-color: white !important;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05) !important;
    }
    .ts-dropdown .option {
        padding: 0.75rem 1rem !important;
        cursor: pointer !important;
        border-bottom: 1px solid #f3f4f6 !important;
        transition: background-color 0.15s ease !important;
    }
    .ts-dropdown .option:last-child { border-bottom: none !important; }
    .ts-dropdown .option.active { background-color: #f9fafb !important; color: #111827 !important; }
    .ts-dropdown .option.selected { background-color: #6366f1 !important; color: white !important; }
    .ts-dropdown .option.selected.active { background-color: #4f46e5 !important; }
    .ts-dropdown .no-results {
        padding: 1rem !important;
        color: #6b7280 !important;
        text-align: center !important;
        font-size: 0.875rem !important;
    }
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
</style>
@endpush

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
// ==========================================
// TOM SELECT - Inicialización
// ==========================================
document.addEventListener('DOMContentLoaded', function () {
    new TomSelect('#product_id', {
        placeholder: 'Busca un código o nombre...',
        valueField: 'id',        
        labelField: 'text',       
        searchField: ['text'],    
        optgroupField: 'optgroup',
        optgroupLabelField: 'label',
        optgroupValueField: 'value',
        lockOptgroupOrder: true,  
        
        optgroups: [
            {value: 'Insumos / Productos', label: 'Insumos / Productos'},
            {value: 'Instrumental Individual', label: 'Instrumental Individual'},
            {value: 'Kits de Cirugía', label: 'Kits de Cirugía'},
            {value: 'Otros', label: 'Otros'}
        ],

    
        load: function(query, callback) {
            if (query.length < 2) return callback();

            fetch(`/api/items/select2?search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(json => {
                    callback(json);
                }).catch(()=>{
                    callback();
                });
        },

        render: {
            option: function(item, escape) {
                let extraHtml = '';
                if (item.type === 'kit' && item.contents) {
                    extraHtml = `<div class="text-xs text-gray-400 mt-1">${escape(item.contents.join(', '))}</div>`;
                }

                return `
                    <div class="py-1">
                        <div class="font-semibold text-gray-800">${escape(item.text)}</div>
                        ${extraHtml}
                    </div>
                `;
            },
            item: function(item, escape) {
                return `<div>${escape(item.text)}</div>`;
            }
        }
    });
});

// ==========================================
// ALPINE.JS - Modal de Carga Masiva
// ==========================================
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
                const response = await fetch('{{ route("surgical_kit_template_items.bulk-import", $surgicalKitTemplate) }}', {
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
{{-- ============================================================
     MODAL DE CONDICIONALES DEL KIT — Alpine.js
     Pegar justo antes del cierre </x-app-layout> en show.blade.php
     ============================================================ --}}

<div x-data="kitConditionalsModal()"
     x-show="isOpen"
     x-cloak
     @open-kit-conditionals-modal.window="openModal($event.detail)"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeModal()"></div>

    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
             @click.away="closeModal()">

            {{-- HEADER --}}
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-white">
                        <i class="fas fa-filter mr-3 text-2xl"></i>
                        <div>
                            <h3 class="text-lg font-bold">Condicionales del Instrumental</h3>
                            <p class="text-sm text-indigo-100" x-text="itemName"></p>
                        </div>
                    </div>
                    <button @click="closeModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>

            {{-- BODY --}}
            <div class="bg-white px-6 py-4 max-h-[75vh] overflow-y-auto">

                {{-- Info del item --}}
                <div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Cantidad base del kit</p>
                        <p class="text-3xl font-bold text-blue-600" x-text="baseQuantity"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Condicionales</p>
                        <p class="text-3xl font-bold text-indigo-600" x-text="conditionals.length"></p>
                    </div>
                </div>

                {{-- Loading --}}
                <div x-show="loading" class="text-center py-10">
                    <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-3"></i>
                    <p class="text-gray-500 text-sm">Cargando condicionales...</p>
                </div>

                <div x-show="!loading">

                    {{-- LISTA --}}
                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-list mr-2 text-indigo-500"></i> Condicionales configurados
                    </h4>

                    <template x-if="conditionals.length === 0">
                        <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 mb-6">
                            <i class="fas fa-inbox text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-500">No hay condicionales. Agrega uno abajo.</p>
                        </div>
                    </template>

                    <div class="space-y-3 mb-6">
                        <template x-for="cond in conditionals" :key="cond.id">
                            <div class="p-4 rounded-lg border-2 transition-all hover:shadow-md"
                                 :class="{
                                     'bg-indigo-50 border-indigo-300': cond.action_type === 'adjust_quantity',
                                     'bg-orange-50 border-orange-300': cond.action_type === 'replace',
                                     'bg-blue-50 border-blue-300':    cond.action_type === 'add_dependency'
                                 }">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">

                                        {{-- Badge acción --}}
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold mb-3"
                                              :class="{
                                                  'bg-indigo-600 text-white': cond.action_type === 'adjust_quantity',
                                                  'bg-orange-600 text-white': cond.action_type === 'replace',
                                                  'bg-blue-600 text-white':   cond.action_type === 'add_dependency'
                                              }">
                                            <i class="fas mr-1.5"
                                               :class="{
                                                   'fa-edit':         cond.action_type === 'adjust_quantity',
                                                   'fa-exchange-alt': cond.action_type === 'replace',
                                                   'fa-link':         cond.action_type === 'add_dependency'
                                               }"></i>
                                            <span x-text="cond.action_description"></span>
                                        </span>

                                        {{-- Criterios --}}
                                        <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-xs mb-2">
                                            <div>
                                                <span class="text-gray-500">Doctor:</span>
                                                <span class="font-semibold text-gray-800 ml-1" x-text="cond.doctor_name"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Hospital:</span>
                                                <span class="font-semibold text-gray-800 ml-1" x-text="cond.hospital_name"></span>
                                            </div>
                                        </div>

                                        {{-- Producto objetivo (replace / add_dependency) --}}
                                        <template x-if="cond.target_product_name">
                                            <div class="mt-2 px-3 py-2 bg-white bg-opacity-70 rounded border border-gray-300 text-xs">
                                                <i class="fas fa-box-open mr-1 text-gray-400"></i>
                                                <span class="font-medium text-gray-600"
                                                      x-text="cond.action_type === 'replace' ? 'Reemplazar por:' : 'Requiere:'">
                                                </span>
                                                <span class="font-bold text-indigo-700 ml-1" x-text="cond.target_product_name"></span>
                                                <template x-if="cond.dependency_quantity">
                                                    <span class="ml-2 text-gray-500">
                                                        × <span class="font-bold" x-text="cond.dependency_quantity"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- Notas --}}
                                        <template x-if="cond.notes">
                                            <p class="mt-2 text-xs text-gray-500 italic">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                <span x-text="cond.notes"></span>
                                            </p>
                                        </template>

                                        <p class="mt-2 text-xs text-gray-400">
                                            Especificidad: <span class="font-bold" x-text="cond.specificity_level"></span>/2
                                        </p>
                                    </div>

                                    {{-- Eliminar --}}
                                    <button @click="deleteConditional(cond.id)"
                                            class="flex-shrink-0 text-red-500 hover:text-red-700 hover:bg-red-100 p-2 rounded-lg transition-all"
                                            title="Eliminar condicional">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- FORMULARIO --}}
                    <div class="border-t-2 border-gray-200 pt-5">
                        <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-plus-circle mr-2 text-green-500"></i> Agregar nuevo condicional
                        </h4>

                        <form @submit.prevent="submitConditional()" class="space-y-4">

                            {{-- Criterios --}}
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <p class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-3">
                                    <i class="fas fa-filter mr-1"></i> Criterios de aplicación (al menos uno)
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Doctor</label>
                                        <select x-model="form.doctor_id"
                                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">-- Todos los doctores --</option>
                                            <template x-for="d in formData.doctors" :key="d.id">
                                                <option :value="d.id" x-text="d.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Hospital</label>
                                        <select x-model="form.hospital_id"
                                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">-- Todos los hospitales --</option>
                                            <template x-for="h in formData.hospitals" :key="h.id">
                                                <option :value="h.id" x-text="h.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Tipo de acción --}}
                            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                                <p class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-3">
                                    <i class="fas fa-cog mr-1"></i> Tipo de acción <span class="text-red-500">*</span>
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                                    <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all hover:shadow-sm"
                                           :class="form.action_type === 'adjust_quantity'
                                               ? 'border-indigo-500 bg-white shadow-sm'
                                               : 'border-gray-200 bg-white'">
                                        <input type="radio" x-model="form.action_type" value="adjust_quantity" class="mt-0.5 text-indigo-600">
                                        <div class="ml-2">
                                            <p class="text-sm font-bold text-indigo-700">
                                                <i class="fas fa-edit mr-1"></i> Ajustar Cantidad
                                            </p>
                                            <p class="text-xs text-gray-500 mt-0.5">Cambia la cantidad base del kit</p>
                                        </div>
                                    </label>

                                    <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all hover:shadow-sm"
                                           :class="form.action_type === 'replace'
                                               ? 'border-orange-500 bg-white shadow-sm'
                                               : 'border-gray-200 bg-white'">
                                        <input type="radio" x-model="form.action_type" value="replace" class="mt-0.5 text-orange-600">
                                        <div class="ml-2">
                                            <p class="text-sm font-bold text-orange-700">
                                                <i class="fas fa-exchange-alt mr-1"></i> Reemplazar
                                            </p>
                                            <p class="text-xs text-gray-500 mt-0.5">Sustituir por otro instrumental</p>
                                        </div>
                                    </label>

                                    <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all hover:shadow-sm"
                                           :class="form.action_type === 'add_dependency'
                                               ? 'border-blue-500 bg-white shadow-sm'
                                               : 'border-gray-200 bg-white'">
                                        <input type="radio" x-model="form.action_type" value="add_dependency" class="mt-0.5 text-blue-600">
                                        <div class="ml-2">
                                            <p class="text-sm font-bold text-blue-700">
                                                <i class="fas fa-link mr-1"></i> Dependencia
                                            </p>
                                            <p class="text-xs text-gray-500 mt-0.5">Requiere otro instrumental (ej: broca → taladro)</p>
                                        </div>
                                    </label>

                                </div>
                            </div>

                            {{-- Campo: Nueva cantidad (adjust_quantity) --}}
                            <div x-show="form.action_type === 'adjust_quantity'" x-transition
                                 class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Nueva Cantidad <span class="text-red-500">*</span>
                                </label>
                                <input type="number" x-model.number="form.quantity_override" min="0"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500"
                                       placeholder="Ej: 5">
                            </div>

                            {{-- Campo: Producto objetivo (replace / add_dependency) --}}
                            <div x-show="form.action_type === 'replace' || form.action_type === 'add_dependency'"
                                 x-transition
                                 class="p-4 rounded-lg border"
                                 :class="form.action_type === 'replace'
                                     ? 'bg-orange-50 border-orange-200'
                                     : 'bg-blue-50 border-blue-200'">

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <span x-text="form.action_type === 'replace'
                                            ? 'Instrumental de Reemplazo'
                                            : 'Instrumental Requerido'">
                                        </span>
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="form.target_product_id"
                                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500">
                                        <option value="">-- Selecciona un instrumental --</option>
                                        <template x-for="p in formData.products" :key="p.id">
                                            <option :value="p.id" x-text="p.label"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Cantidad de dependencia --}}
                                <div x-show="form.action_type === 'add_dependency'" x-transition>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Cantidad Requerida <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" x-model.number="form.dependency_quantity" min="1"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500"
                                           placeholder="Ej: 1">
                                </div>
                            </div>

                            {{-- Notas --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notas (opcional)</label>
                                <textarea x-model="form.notes" rows="2"
                                          class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500"
                                          placeholder="Ej: El Dr. García requiere taladro adicional en procedimientos de cadera"></textarea>
                            </div>

                            {{-- Error --}}
                            <div x-show="errorMessage" x-transition
                                 class="flex items-start gap-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 flex-shrink-0"></i>
                                <p class="text-sm text-red-700" x-text="errorMessage"></p>
                            </div>

                            {{-- Botones --}}
                            <div class="flex justify-end gap-3 pt-2 border-t border-gray-200">
                                <button type="button" @click="closeModal()"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                    Cerrar
                                </button>
                                <button type="submit" :disabled="isSubmitting"
                                        class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas mr-1.5"
                                       :class="isSubmitting ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                                    <span x-text="isSubmitting ? 'Guardando...' : 'Guardar Condicional'"></span>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>{{-- /!loading --}}
            </div>{{-- /body --}}
        </div>
    </div>
</div>

{{-- ============================================================
     SCRIPT Alpine.js
     ============================================================ --}}
<script>
function kitConditionalsModal() {
    return {
        isOpen:       false,
        loading:      false,
        isSubmitting: false,
        errorMessage: '',

        itemId:       null,
        itemName:     '',
        baseQuantity: 0,
        conditionals: [],

        formData: { doctors: [], hospitals: [], products: [] },

        form: {
            doctor_id:           '',
            hospital_id:         '',
            action_type:         'adjust_quantity',
            quantity_override:   null,
            target_product_id:   '',
            dependency_quantity: 1,
            notes:               '',
        },

        async openModal(data) {
            this.itemId       = data.itemId;
            this.itemName     = data.itemName;
            this.baseQuantity = data.baseQuantity;
            this.isOpen       = true;
            this.errorMessage = '';
            this.resetForm();
            await this.loadFormData();
            await this.loadConditionals();
        },

        closeModal() {
            this.isOpen = false;
            this.resetForm();
        },

        async loadFormData() {
            try {
                const res    = await fetch('{{ route("surgical-kit-template-conditional-form-data") }}');
                const result = await res.json();
                if (result.success) this.formData = result.data;
            } catch (e) {
                console.error('Error cargando form data:', e);
            }
        },

        async loadConditionals() {
            this.loading = true;
            try {
                const res    = await fetch(`/surgical_kit_template_items/${this.itemId}/conditionals`);
                const result = await res.json();
                if (result.success) this.conditionals = result.data;
            } catch (e) {
                console.error('Error cargando condicionales:', e);
            } finally {
                this.loading = false;
            }
        },

        async submitConditional() {
            this.isSubmitting = true;
            this.errorMessage = '';

            try {
                const payload = {
                    doctor_id:           this.form.doctor_id       || null,
                    hospital_id:         this.form.hospital_id     || null,
                    action_type:         this.form.action_type,
                    quantity_override:   this.form.action_type === 'adjust_quantity'
                                            ? this.form.quantity_override : null,
                    target_product_id:   ['replace', 'add_dependency'].includes(this.form.action_type)
                                            ? this.form.target_product_id : null,
                    dependency_quantity: this.form.action_type === 'add_dependency'
                                            ? this.form.dependency_quantity : null,
                    notes:               this.form.notes || null,
                };

                const res    = await fetch(`/surgical_kit_template_items/${this.itemId}/conditionals`, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const result = await res.json();

                if (result.success) {
                    this.conditionals.unshift(result.data);
                    this.resetForm();
                } else {
                    this.errorMessage = result.message;
                }
            } catch (e) {
                this.errorMessage = 'Error de conexión: ' + e.message;
            } finally {
                this.isSubmitting = false;
            }
        },

        async deleteConditional(conditionalId) {
            if (!confirm('¿Eliminar este condicional?')) return;
            try {
                const res    = await fetch(
                    `/surgical_kit_template_items/${this.itemId}/conditionals/${conditionalId}`,
                    {
                        method:  'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    }
                );
                const result = await res.json();
                if (result.success) {
                    this.conditionals = this.conditionals.filter(c => c.id !== conditionalId);
                } else {
                    alert('✗ ' + result.message);
                }
            } catch (e) {
                alert('✗ Error al eliminar');
            }
        },

        resetForm() {
            this.form = {
                doctor_id:           '',
                hospital_id:         '',
                action_type:         'adjust_quantity',
                quantity_override:   null,
                target_product_id:   '',
                dependency_quantity: 1,
                notes:               '',
            };
            this.errorMessage = '';
        },
    };
}

</script>
@endpush

</x-app-layout>