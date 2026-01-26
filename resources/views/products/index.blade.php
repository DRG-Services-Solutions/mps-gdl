<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        Catálogo de Productos
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Fichas maestras de productos quirúrgicos y consumibles
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showDeleteModal: false, productToDelete: null, showFilters: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                
                <!-- Header Section -->
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-book text-indigo-600 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Catálogo Maestro</h3>
                                <p class="text-sm text-gray-600">{{ $products->total() }} productos registrados</p>
                            </div>
                        </div>
                        
                        <a href="{{ route('products.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                            <i class="fas fa-plus mr-2"></i>
                            Nuevo Producto
                        </a>
                        <a href="{{ route('products.import.form') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                            <i class="fas fa-file-upload mr-2"></i>
                            Importar Productos
                        </a>
                    </div>
                </div>

                <!-- Search and Filters Section -->
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <form method="GET" action="{{ route('products.index') }}" class="space-y-4">
                        <!-- Quick Search Bar -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       placeholder="Buscar por nombre o código..."
                                       class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm transition-all duration-200">
                            </div>
                            
                            <div class="flex gap-2">
                                <button type="button" 
                                        @click="showFilters = !showFilters"
                                        class="inline-flex items-center px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                    <i class="fas fa-filter mr-2"></i>
                                    Filtros
                                    <span x-show="showFilters">
                                        <i class="fas fa-chevron-up ml-2"></i>
                                    </span>
                                    <span x-show="!showFilters">
                                        <i class="fas fa-chevron-down ml-2"></i>
                                    </span>
                                </button>
                                
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                                    <i class="fas fa-search mr-2"></i>
                                    {{ __('Buscar') }}
                                </button>
                                
                                @if(request()->hasAny(['search', 'supplier_id', 'category_id', 'tracking_type', 'status']))
                                    <a href="{{ route('products.index') }}" 
                                       class="inline-flex items-center px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                                        <i class="fas fa-times mr-2"></i>
                                        {{ __('Limpiar') }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Advanced Filters (Collapsible) -->
                        <div x-show="showFilters" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-2"
                             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-200"
                             style="display: none;">
                            
                            <!-- Supplier Filter -->
                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-building mr-1 text-gray-400"></i>
                                    {{ __('Proveedor') }}
                                </label>
                                <select name="supplier_id" 
                                        id="supplier_id"
                                        class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm rounded-lg transition-all duration-200">
                                    <option value="">{{ __('Todos los proveedores') }}</option>
                                    @foreach($suppliers ?? [] as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Category Filter -->
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-tag mr-1 text-gray-400"></i>
                                    {{ __('Categoría') }}
                                </label>
                                <select name="category_id" 
                                        id="category_id"
                                        class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm rounded-lg transition-all duration-200">
                                    <option value="">{{ __('Todas las categorías') }}</option>
                                    @foreach($categories ?? [] as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tracking Type Filter -->
                            <div>
                                <label for="tracking_type" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-barcode mr-1 text-gray-400"></i>
                                    {{ __('Tipo de Tracking') }}
                                </label>
                                <select name="tracking_type" 
                                        id="tracking_type"
                                        class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm rounded-lg transition-all duration-200">
                                    <option value="">{{ __('Todos los tipos') }}</option>
                                    <option value="code" {{ request('tracking_type') == 'code' ? 'selected' : '' }}>
                                        <i class="fas fa-boxes"></i> Code
                                    </option>
                                    <option value="rfid" {{ request('tracking_type') == 'rfid' ? 'selected' : '' }}>
                                        <i class="fas fa-wifi"></i> RFID
                                    </option>
                                    <option value="serial" {{ request('tracking_type') == 'serial' ? 'selected' : '' }}>
                                        <i class="fas fa-hashtag"></i> Serial
                                    </option>
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-info-circle mr-1 text-gray-400"></i>
                                    {{ __('Estado') }}
                                </label>
                                <select name="status" 
                                        id="status"
                                        class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm rounded-lg transition-all duration-200">
                                    <option value="">{{ __('Todos los estados') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                        {{ __('Activo') }}
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                        {{ __('Inactivo') }}
                                    </option>
                                    <option value="discontinued" {{ request('status') == 'discontinued' ? 'selected' : '' }}>
                                        {{ __('Descontinuado') }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Active Filters Display -->
                        @if(request()->hasAny(['search', 'supplier_id', 'category_id', 'tracking_type', 'status']))
                            <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-200">
                                <span class="text-sm font-medium text-gray-700">{{ __('Filtros activos:') }}</span>
                                
                                @if(request('search'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{ __('Búsqueda: ') }} "{{ request('search') }}"
                                        <a href="{{ route('products.index', array_diff_key(request()->query(), ['search' => ''])) }}" 
                                           class="ml-1.5 text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-times text-xs"></i>
                                        </a>
                                    </span>
                                @endif
                                
                                @if(request('supplier_id'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ __('Proveedor: ') }} {{ $suppliers->find(request('supplier_id'))?->name }}
                                        <a href="{{ route('products.index', array_diff_key(request()->query(), ['supplier_id' => ''])) }}" 
                                           class="ml-1.5 text-purple-600 hover:text-purple-800">
                                            <i class="fas fa-times text-xs"></i>
                                        </a>
                                    </span>
                                @endif
                                
                                @if(request('category_id'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                        {{ __('Categoría: ') }} {{ $categories->find(request('category_id'))?->name }}
                                        <a href="{{ route('products.index', array_diff_key(request()->query(), ['category_id' => ''])) }}" 
                                           class="ml-1.5 text-pink-600 hover:text-pink-800">
                                            <i class="fas fa-times text-xs"></i>
                                        </a>
                                    </span>
                                @endif
                                
                                @if(request('tracking_type'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ __('Tracking: ') }} {{ strtoupper(request('tracking_type')) }}
                                        <a href="{{ route('products.index', array_diff_key(request()->query(), ['tracking_type' => ''])) }}" 
                                           class="ml-1.5 text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-times text-xs"></i>
                                        </a>
                                    </span>
                                @endif
                                
                                @if(request('status'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('Estado: ') }} {{ __(ucfirst(request('status'))) }}
                                        <a href="{{ route('products.index', array_diff_key(request()->query(), ['status' => ''])) }}" 
                                           class="ml-1.5 text-green-600 hover:text-green-800">
                                            <i class="fas fa-times text-xs"></i>
                                        </a>
                                    </span>
                                @endif
                            </div>
                        @endif
                    </form>
                </div>

                <!-- Success Message -->
                @if(session('success'))
                    <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-200" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-emerald-600 mr-2"></i>
                                <span class="text-emerald-800 font-medium">{{ session('success') }}</span>
                            </div>
                            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 transition-colors duration-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Table Section -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                  {{ __('Producto') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell">
                                    {{ __('Proveedor') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden md:table-cell">
                                    {{ __('Categoría') }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Tipo de Tracking') }}
                                </th>
                                
                                <!--
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider hidden xl:table-cell">
                                    Estado
                                </th>
                                -->

                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($products as $product)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <!-- Producto -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                                    <i class="fas fa-box text-white text-sm"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="{{ route('products.show', $product) }}" 
                                                class="text-sm font-semibold text-gray-900 hover:text-indigo-600 transition-colors duration-150">
                                                    {{ $product->name }}
                                                </a>
                                                <div class="text-xs text-gray-500 flex items-center space-x-2 mt-1">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-barcode mr-1"></i>
                                                        {{ $product->code }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Proveedor -->
                                    <td class="px-6 py-4 hidden lg:table-cell">
                                        <div class="text-sm text-gray-900 text-center capitalize">{{ Str::lower($product->supplier->name ?? 'Sin Proveedor') }}</div>
                                    </td>

                                    <!-- Categoría -->
                                    <td class="px-6 py-4 hidden md:table-cell">
                                        @if($product->category)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 capitalize">
                                                <i class="fas fa-tag mr-1 text-xs"></i>
                                                {{ Str::lower($product->category->name) }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Tipo de Tracking -->
                                    <td class="px-6 py-4 text-center">
                                        @switch($product->tracking_type)
                                            @case('code')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-boxes mr-1"></i>
                                                    Code
                                                </span>
                                                @break
                                            @case('rfid')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-wifi mr-1"></i>
                                                    RFID
                                                </span>
                                                @break
                                            @case('serial')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-hashtag mr-1"></i>
                                                    Serial
                                                </span>
                                                @break
                                        @endswitch
                                    </td>

                                   

                                    
                                    <!-- Estado -->
                                    <!--
                                    <td class="px-6 py-4 text-center hidden xl:table-cell">
                                        @switch($product->status)
                                            @case('active')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Activo
                                                </span>
                                                @break
                                            @case('inactive')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-pause-circle mr-1"></i>
                                                    Inactivo
                                                </span>
                                                @break
                                            @case('discontinued')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-ban mr-1"></i>
                                                    Descontinuado
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                    -->

                                    <!-- Acciones -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('products.edit', $product->id) }}" 
                                               class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                                               title="Editar producto">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <button @click="productToDelete = { id: {{ $product->id }}, name: '{{ addslashes($product->name) }}' }; showDeleteModal = true"
                                                    class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
                                                    title="Eliminar producto">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-box-open text-gray-400 text-5xl mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No se encontraron productos') }}</h3>
                                            <p class="text-sm text-gray-500 mb-4">{{ __('Intenta ajustar tus filtros de búsqueda') }}</p>
                                            @if(request()->hasAny(['search', 'supplier_id', 'category_id', 'tracking_type', 'status']))
                                                <a href="{{ route('products.index') }}" 
                                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                                                    <i class="fas fa-redo mr-2"></i>
                                                    {{ __('Limpiar Filtros') }}
                                                </a>
                                            @else
                                                <a href="{{ route('products.create') }}" 
                                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                                                    <i class="fas fa-plus mr-2"></i>
                                                    {{ __('Agregar Primer Producto') }}
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                {{ __('Mostrando') }} {{ $products->firstItem() }} {{ __('al') }} {{ $products->lastItem() }} {{ __('de') }} {{ $products->total() }} {{ __('productos') }}
                            </div>
                            <div>
                                {{ $products->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Info Cards -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-box text-indigo-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total en Catálogo</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $products->total() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-boxes text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Tracking Code</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $products->where('tracking_type', 'code')->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-wifi text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Tracking RFID</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $products->where('tracking_type', 'rfid')->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hashtag text-yellow-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Número de Serie</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $products->where('tracking_type', 'serial')->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="showDeleteModal = false"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showDeleteModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-2xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                {{ __('Confirmar Eliminación') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('¿Estás seguro de eliminar') }} 
                                    <span class="font-semibold" x-text="productToDelete?.name"></span>{{ __('?') }}
                                    {{ __('Esta acción no se puede deshacer.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <form x-bind:action="`{{ url('products') }}/${productToDelete?.id}`" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <i class="fas fa-trash-alt mr-2"></i>
                                {{ __('Eliminar') }}
                            </button>
                        </form>
                        <button @click="showDeleteModal = false" 
                                type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                                {{ __('Cancelar') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>