<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="text-sm font-medium">{{ __('Volver a productos') }}</span>
                </a>
                <div class="border-l border-gray-300 pl-4">
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ __('Editar Catálogo: ') }} <span class="text-indigo-600">{{ $product->code }}</span>
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Modificando la ficha maestra de: ') }} <span class="font-semibold">{{ $product->name }}</span>
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                
                {{-- Errores de validación --}}
                @if ($errors->any())
                    <div class="px-6 py-4 bg-red-50 border-b border-red-200">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-0.5"></i>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-red-800">{{ __('Por favor corrija los siguientes errores:') }}</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form action="{{ route('products.update', $product) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')
                    
                    {{-- SECCIÓN 1: INFORMACIÓN BÁSICA --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-id-card text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Información Básica</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Nombre --}}
                            <div>
                                <label for="name" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tag text-gray-400 mr-2"></i>
                                    Nombre del Producto / Set
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('name') border-red-500 @enderror">
                                @error('name')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Código --}}
                            <div>
                                <label for="code" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-barcode text-gray-400 mr-2"></i>
                                    Código Maestro
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="text" name="code" id="code" value="{{ old('code', $product->code) }}" required
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('code') border-red-500 @enderror">
                                @error('code')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Estado --}}
                            <div>
                                <label for="status" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-toggle-on text-gray-400 mr-2"></i>
                                    Estado en Catálogo
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="status" id="status" required
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                                    <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>✅ Activo</option>
                                    <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>⏸️ Inactivo</option>
                                    <option value="discontinued" {{ old('status', $product->status) == 'discontinued' ? 'selected' : '' }}>🚫 Descontinuado</option>
                                </select>
                            </div>

                            
                        </div>
                    </div>

                    {{-- SECCIÓN 2: ARQUITECTURA DE COMPOSICIÓN --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-layer-group text-purple-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Arquitectura de Composición</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- ¿ES UN SET O KIT? (IS COMPOSITE) --}}
                            <div class="md:col-span-2">
                                <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm {{ old('is_composite', $product->is_composite) ? 'bg-purple-50 border-purple-400' : 'bg-white hover:border-purple-400' }} cursor-pointer transition-all duration-200">
                                    <input type="checkbox"
                                           name="is_composite"
                                           id="is_composite"
                                           value="1"
                                           {{ old('is_composite', $product->is_composite) ? 'checked' : '' }}
                                           class="h-5 w-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 mt-0.5">

                                    <span class="ml-3 text-sm flex-1">
                                        <span class="block font-bold text-purple-900 flex items-center">
                                            <i class="fas fa-suitcase-medical text-purple-600 mr-2"></i>
                                            Es un Producto Compuesto (Set / Kit / Torre)
                                        </span>
                                        <span class="block text-purple-700 mt-1">
                                            Determina si este producto es un contenedor de otros insumos. <strong>Nota:</strong> Desmarcar esta opción si el producto ya tiene componentes guardados podría causar inconsistencias.
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: CLASIFICACIÓN --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-sitemap text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Clasificación</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Proveedor --}}
                            <div>
                                <label for="supplier_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-industry text-gray-400 mr-2"></i> Proveedor
                                </label>
                                <select name="supplier_id" id="supplier_id"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                                    <option value="">-- Seleccione un proveedor --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                            {{ str($supplier->name)->title() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Categoría --}}
                            <div>
                                <label for="category_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tags text-gray-400 mr-2"></i> Categoría Anatómica
                                </label>
                                <select name="category_id" id="category_id"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                                    <option value="">-- Seleccione --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ str($category->name)->title() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tipo de Producto --}}
                            <div>
                                <label for="product_type_id" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-cubes text-gray-400 mr-2"></i> Tipo de Producto
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="product_type_id" id="product_type_id" required
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                                    <option value="">-- Seleccione --</option>
                                    @foreach($product_types as $type)
                                        <option value="{{ $type->id }}" {{ old('product_type_id', $product->product_type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 4: TIPO DE TRACKING Y REQUISITOS --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-clipboard-check text-indigo-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Control Físico y Trazabilidad</h3>
                        </div>

                        {{-- Alerta Precaución Edición --}}
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-800">
                                        <strong>Precaución al modificar el rastreo:</strong> Cambiar el "Tipo de Rastreo" de un producto que ya tiene existencias físicas en el almacén podría requerir actualizar manualmente el inventario existente para evitar conflictos de lectura.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            {{-- Tipo de Rastreo --}}
                            <div>
                                <label for="tracking_type" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-radar text-gray-400 mr-2"></i> Tipo de Rastreo
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="tracking_type" id="tracking_type" required
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('tracking_type') border-red-500 @enderror">
                                    <option value="code" {{ old('tracking_type', $product->tracking_type) == 'code' ? 'selected' : '' }}>📦 Solo Code (Sin rastreo especial)</option>
                                    <option value="lote" {{ old('tracking_type', $product->tracking_type) == 'lote' ? 'selected' : '' }}>📅 Control por Lote (Consumibles)</option>
                                    <option value="rfid" {{ old('tracking_type', $product->tracking_type) == 'rfid' ? 'selected' : '' }}>📡 Etiqueta RFID (Implantes / Sets)</option>
                                    <option value="serial" {{ old('tracking_type', $product->tracking_type) == 'serial' ? 'selected' : '' }}>🔢 Número de Serie (Equipos Caros)</option>
                                </select>
                                @error('tracking_type')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                {{-- TIENE CADUCIDAD --}}
                                <div>
                                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm {{ old('has_expiration_date', $product->has_expiration_date) ? 'bg-red-50 border-red-400' : 'bg-white hover:border-red-400' }} cursor-pointer transition-all duration-200">
                                        <input type="checkbox" name="has_expiration_date" id="has_expiration_date" value="1" {{ old('has_expiration_date', $product->has_expiration_date) ? 'checked' : '' }} class="h-5 w-5 text-red-600 border-gray-300 rounded focus:ring-red-500 mt-0.5">
                                        <span class="ml-3 text-sm flex-1">
                                            <span class="block font-medium text-gray-900 flex items-center"><i class="fas fa-calendar-times text-red-600 mr-2"></i>Tiene Caducidad</span>
                                        </span>
                                    </label>
                                </div>

                                {{-- REQUIERE ESTERILIZACIÓN --}}
                                <div>
                                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm {{ old('requires_sterilization', $product->requires_sterilization) ? 'bg-green-50 border-green-400' : 'bg-white hover:border-green-400' }} cursor-pointer transition-all duration-200">
                                        <input type="checkbox" name="requires_sterilization" id="requires_sterilization" value="1" {{ old('requires_sterilization', $product->requires_sterilization) ? 'checked' : '' }} class="h-5 w-5 text-green-600 border-gray-300 rounded focus:ring-green-500 mt-0.5">
                                        <span class="ml-3 text-sm flex-1">
                                            <span class="block font-medium text-gray-900 flex items-center"><i class="fas fa-shield-virus text-green-600 mr-2"></i>Requiere Esterilización</span>
                                        </span>
                                    </label>
                                </div>

                                {{-- REQUIERE REFRIGERACIÓN --}}
                                <div>
                                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm {{ old('requires_refrigeration', $product->requires_refrigeration) ? 'bg-blue-50 border-blue-400' : 'bg-white hover:border-blue-400' }} cursor-pointer transition-all duration-200">
                                        <input type="checkbox" name="requires_refrigeration" id="requires_refrigeration" value="1" {{ old('requires_refrigeration', $product->requires_refrigeration) ? 'checked' : '' }} class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                                        <span class="ml-3 text-sm flex-1">
                                            <span class="block font-medium text-gray-900 flex items-center"><i class="fas fa-snowflake text-blue-600 mr-2"></i>Refrigeración</span>
                                        </span>
                                    </label>
                                </div>

                                {{-- CONTROL TEMPERATURA --}}
                                <div>
                                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm {{ old('requires_temperature', $product->requires_temperature) ? 'bg-orange-50 border-orange-400' : 'bg-white hover:border-orange-400' }} cursor-pointer transition-all duration-200">
                                        <input type="checkbox" name="requires_temperature" id="requires_temperature" value="1" {{ old('requires_temperature', $product->requires_temperature) ? 'checked' : '' }} class="h-5 w-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 mt-0.5">
                                        <span class="ml-3 text-sm flex-1">
                                            <span class="block font-medium text-gray-900 flex items-center"><i class="fas fa-thermometer-half text-orange-600 mr-2"></i>Temperatura < 45°C</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('products.index') }}" 
                           class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </a>
                        <div class="flex items-center space-x-3">
                            <button type="button" onclick="if(confirm('¿Está seguro de eliminar este producto?')) { document.getElementById('delete-form').submit(); }"
                                    class="inline-flex items-center px-6 py-3 border border-red-300 shadow-sm text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                                <i class="fas fa-trash mr-2"></i> Eliminar
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5">
                                <i class="fas fa-save mr-2"></i> Actualizar Producto
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Formulario de Eliminación (oculto) --}}
                <form id="delete-form" action="{{ route('products.destroy', $product) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            {{-- Tarjeta de Información Adicional --}}
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-blue-900 mb-1">{{ __('Información del Registro') }}</h4>
                        <div class="text-xs text-blue-800 space-y-1">
                            <p><strong>{{ __('Creado:') }}</strong> {{ $product->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>{{ __('Última actualización:') }}</strong> {{ $product->updated_at->format('d/m/Y H:i') }}</p>
                            @if($product->deleted_at)
                                <p class="text-red-600"><strong>{{ __('Eliminado:') }}</strong> {{ $product->deleted_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>