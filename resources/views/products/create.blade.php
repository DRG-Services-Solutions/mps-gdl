<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="text-sm font-medium">Volver a productos</span>
                </a>
                <div class="border-l border-gray-300 pl-4">
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        Agregar Producto al Catálogo
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Cree la ficha maestra del producto, instrumental o Set.
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Alerta informativa --}}
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Nota importante:</strong> Este formulario crea la información teórica en el catálogo (El "Deber Ser"). 
                            Las cantidades físicas (stock) y ubicaciones se gestionan en el módulo de Inventario.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                
                {{-- Errores de validación --}}
                @if ($errors->any())
                    <div class="px-6 py-4 bg-red-50 border-b border-red-200">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-0.5"></i>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-red-800">Por favor corrija los siguientes errores:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form action="{{ route('products.store') }}" method="POST" class="p-6">
                    @csrf
                    
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
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('name') border-red-500 @enderror"
                                       placeholder="Ej: Set de Artroscopia o Bisturí">
                                @error('name')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Código --}}
                            <div>
                                <label for="code" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-barcode text-gray-400 mr-2"></i>
                                    Código Maestro
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="text" name="code" id="code" value="{{ old('code') }}" required
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('code') border-red-500 @enderror"
                                       placeholder="'Ej: AR-001'">
                                @error('code')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            {{-- Estado --}}
                            <div>
                                <label for="status" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-toggle-on text-gray-400 mr-2"></i>
                                    Estado en Catálogo
                                </label>
                                <select name="status" id="status"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>✅ Activo</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>⏸️ Inactivo</option>
                                    <option value="discontinued" {{ old('status') == 'discontinued' ? 'selected' : '' }}>🚫 Descontinuado</option>
                                </select>
                            </div>

                            
                        </div>
                    </div>

                    {{-- SECCIÓN 2: ARQUITECTURA DE COMPOSICIÓN (NUEVO) --}}
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-3 border-b border-gray-200">
                            <i class="fas fa-layer-group text-purple-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Arquitectura de Composición</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- ¿ES UN SET O KIT? (IS COMPOSITE) --}}
                            <div class="md:col-span-2">
                                <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm bg-purple-50 hover:border-purple-400 cursor-pointer transition-all duration-200">
                                    <input type="checkbox"
                                           name="is_composite"
                                           id="is_composite"
                                           value="1"
                                           {{ old('is_composite') ? 'checked' : '' }}
                                           class="h-5 w-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 mt-0.5">

                                    <span class="ml-3 text-sm flex-1">
                                        <span class="block font-bold text-purple-900 flex items-center">
                                            <i class="fas fa-suitcase-medical text-purple-600 mr-2"></i>
                                            Es un Producto Compuesto (Set / Kit / Torre)
                                        </span>
                                        <span class="block text-purple-700 mt-1">
                                            Marque esta opción si este producto NO es una pieza individual, sino un <strong>contenedor</strong> que lleva otros productos adentro (Ej. Set de Artroscopia). Esto habilitará la creación de su Receta (Checklist).
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
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 @error('supplier_id') border-red-500 @enderror">
                                    <option value="">-- Seleccione un proveedor --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ str($supplier->name)->title() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
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
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                        <option value="{{ $type->id }}" {{ old('product_type_id') == $type->id ? 'selected' : '' }}>
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

                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700"><strong>Tipos de trazabilidad física en almacén:</strong></p>
                                    <ul class="mt-2 text-sm text-blue-700 space-y-1 list-disc list-inside">
                                        <li><strong>Code:</strong> Control genérico (Ej. Gasas sin lote).</li>
                                        <li><strong>Lote:</strong> Exige número de lote al ingresar inventario.</li>
                                        <li><strong>RFID:</strong> Se rastrea mediante arcos/antenas.</li>
                                        <li><strong>Serial:</strong> Cada pieza tiene un número único (Ej. Consolas, Motores).</li>
                                    </ul>
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
                                    <option value="code" {{ old('tracking_type', 'code') == 'code' ? 'selected' : '' }}>📦 Codigo</option>
                                    <option value="lote" {{ old('tracking_type') == 'lote' ? 'selected' : '' }}>📅 Control por Lote</option>
                                    <option value="rfid" {{ old('tracking_type') == 'rfid' ? 'selected' : '' }}>📡 Etiqueta RFID</option>
                                    <option value="serial" {{ old('tracking_type') == 'serial' ? 'selected' : '' }}>🔢 Número de Serie</option>
                                </select>
                                @error('tracking_type')<p class="mt-1 text-sm text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                {{-- TIENE CADUCIDAD (NUEVO) --}}
                                <div>
                                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm bg-white hover:border-red-400 cursor-pointer transition-all duration-200">
                                        <input type="checkbox" name="has_expiration_date" id="has_expiration_date" value="1" {{ old('has_expiration_date') ? 'checked' : '' }} class="h-5 w-5 text-red-600 border-gray-300 rounded focus:ring-red-500 mt-0.5">
                                        <span class="ml-3 text-sm flex-1">
                                            <span class="block font-medium text-gray-900 flex items-center"><i class="fas fa-calendar-times text-red-600 mr-2"></i>Tiene Caducidad</span>
                                            <span class="block text-gray-500 mt-1">Obliga al almacenista a capturar fecha de expiración.</span>
                                        </span>
                                    </label>
                                </div>

                                {{-- REQUIERE ESTERILIZACIÓN --}}
                                <div>
                                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm bg-white hover:border-green-400 cursor-pointer transition-all duration-200">
                                        <input type="checkbox" name="requires_sterilization" id="requires_sterilization" value="1" {{ old('requires_sterilization') ? 'checked' : '' }} class="h-5 w-5 text-green-600 border-gray-300 rounded focus:ring-green-500 mt-0.5">
                                        <span class="ml-3 text-sm flex-1">
                                            <span class="block font-medium text-gray-900 flex items-center"><i class="fas fa-shield-virus text-green-600 mr-2"></i>Requiere Esterilización</span>
                                            <span class="block text-gray-500 mt-1">Debe pasar por CEYE tras su uso.</span>
                                        </span>
                                    </label>
                                </div>

                                {{-- REQUIERE REFRIGERACIÓN --}}
                                <div>
                                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm bg-white hover:border-blue-400 cursor-pointer transition-all duration-200">
                                        <input type="checkbox" name="requires_refrigeration" id="requires_refrigeration" value="1" {{ old('requires_refrigeration') ? 'checked' : '' }} class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                                        <span class="ml-3 text-sm flex-1">
                                            <span class="block font-medium text-gray-900 flex items-center"><i class="fas fa-snowflake text-blue-600 mr-2"></i>Refrigeración</span>
                                            <span class="block text-gray-500 mt-1">Almacenamiento entre 2°C y 8°C.</span>
                                        </span>
                                    </label>
                                </div>

                                {{-- CONTROL TEMPERATURA --}}
                                <div>
                                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg shadow-sm bg-white hover:border-orange-400 cursor-pointer transition-all duration-200">
                                        <input type="checkbox" name="requires_temperature" id="requires_temperature" value="1" {{ old('requires_temperature') ? 'checked' : '' }} class="h-5 w-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 mt-0.5">
                                        <span class="ml-3 text-sm flex-1">
                                            <span class="block font-medium text-gray-900 flex items-center"><i class="fas fa-thermometer-half text-orange-600 mr-2"></i>Temperatura < 45°C</span>
                                            <span class="block text-gray-500 mt-1">Control ambiental necesario.</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('products.index') }}" 
                           class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5">
                            <i class="fas fa-save mr-2"></i>
                            Guardar en Catálogo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>