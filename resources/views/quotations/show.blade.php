<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-file-invoice mr-2 text-indigo-600"></i>
                    {{ $quotation->quotation_number }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Estado: 
                    @php
                        $statusClasses = [
                            'draft' => 'text-gray-700',
                            'sent' => 'text-blue-700',
                            'in_surgery' => 'text-yellow-700',
                            'completed' => 'text-green-700',
                            'invoiced' => 'text-indigo-700',
                        ];
                        $statusLabels = [
                            'draft' => 'Borrador',
                            'sent' => 'Enviada',
                            'in_surgery' => 'En Cirugía',
                            'completed' => 'Completada',
                            'invoiced' => 'Facturada',
                        ];
                    @endphp
                    <span class="font-medium {{ $statusClasses[$quotation->status] }}">
                        {{ $statusLabels[$quotation->status] }}
                    </span>
                </p>
            </div>
            <div class="flex items-center space-x-3">
                @if($quotation->status === 'draft')
                    <a href="{{ route('quotations.edit', $quotation) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        <i class="fas fa-edit mr-2"></i>Editar
                    </a>
                @endif
                
                @if($quotation->status === 'draft' && $quotation->items->count() > 0)
                    <form action="{{ route('quotations.send-to-surgery', $quotation) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('¿Enviar material a cirugía?')"
                                class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 transition">
                            <i class="fas fa-paper-plane mr-2"></i>Enviar a Cirugía
                        </button>
                    </form>
                @endif
                
                @if($quotation->status === 'in_surgery')
                    <a href="{{ route('quotations.return-form', $quotation) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                        <i class="fas fa-undo mr-2"></i>Registrar Retorno
                    </a>
                @endif
                
                @if($quotation->status === 'completed')
                    <form action="{{ route('quotations.generate-sales', $quotation) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('¿Generar ventas automáticamente?')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            <i class="fas fa-dollar-sign mr-2"></i>Generar Ventas
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('quotations.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="quotationApp()">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Information Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Hospital Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">
                        <i class="fas fa-hospital mr-1"></i>Hospital
                    </h3>
                    <div class="space-y-2">
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation->hospital->name }}</p>
                        @if($quotation->hospital->contact_person)
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-user mr-1"></i>{{ $quotation->hospital->contact_person }}
                            </p>
                        @endif
                        @if($quotation->hospital->phone)
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-phone mr-1"></i>{{ $quotation->hospital->phone }}
                            </p>
                        @endif
                    </div>
                </div>
                
                <!-- Doctor Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">
                        <i class="fas fa-user-md mr-1"></i>Doctor
                    </h3>
                    <div class="space-y-2">
                        @if($quotation->doctor)
                            <p class="text-lg font-semibold text-gray-900">{{ $quotation->doctor->full_name }}</p>
                            @if($quotation->doctor->specialty)
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-stethoscope mr-1"></i>{{ $quotation->doctor->specialty }}
                                </p>
                            @endif
                        @else
                            <p class="text-gray-500 italic">No asignado</p>
                        @endif
                    </div>
                </div>
                
                <!-- Surgery Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">
                        <i class="fas fa-calendar-alt mr-1"></i>Cirugía
                    </h3>
                    <div class="space-y-2">
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation->surgery_type ?? 'No especificada' }}</p>
                        @if($quotation->surgery_date)
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-calendar mr-1"></i>{{ $quotation->surgery_date->format('d/m/Y') }}
                            </p>
                        @endif
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-briefcase mr-1"></i>{{ $quotation->billingLegalEntity->business_name }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <i class="fas fa-boxes text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Total Productos</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_items'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            <i class="fas fa-paper-plane text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Enviados</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['sent_items'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Retornados</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['returned_items'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Faltantes</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['missing_items'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Add Product Form -->
            @if($quotation->status === 'draft')
                <div class="bg-white overflow-visible shadow-sm sm:rounded-xl" x-data="quotationApp()">

                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>Agregar Producto
                        </h3>

                        <form action="{{ route('quotations.add-item', $quotation) }}" method="POST" @submit="validateForm">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                                <!-- BUSCADOR -->
                                <div class="md:col-span-2 relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-search mr-1"></i>Buscar Producto <span class="text-red-500">*</span>
                                    </label>

                                    <input type="text"
                                        x-model="searchQuery"
                                        @input.debounce.300ms="searchProducts"
                                        @focus="showResults = true"
                                        @keydown.escape="showResults = false"
                                        placeholder="Nombre, código o EPC..."
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"
                                        autocomplete="off">

                                    <input type="hidden" name="product_unit_id" x-model="selectedProductId" required>

                                    <!-- LOADING -->
                                    <div x-show="loading" class="absolute right-3 top-9">
                                        <i class="fas fa-spinner fa-spin text-indigo-600"></i>
                                    </div>

                                    <!-- DROPDOWN (ÚNICO Y CORRECTO) -->
                                    <div x-show="showResults && products.length"
                                        @click.away="showResults = false"
                                        x-cloak
                                        class="absolute mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-2xl max-h-60 overflow-y-auto z-50">

                                        <template x-for="product in products" :key="product.id">
                                            <div @click="selectProduct(product)"
                                                class="px-4 py-2 cursor-pointer hover:bg-indigo-50 border-b last:border-b-0">

                                                <div class="font-medium text-sm" x-text="product.name"></div>
                                                <div class="text-xs text-gray-500">
                                                    <span x-text="product.code"></span> —
                                                    <span x-text="product.epc || product.serial_number || 'N/A'"></span>
                                                </div>

                                                <div class="text-xs text-indigo-600 mt-1">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    <span x-text="product.sub_warehouse_name"></span>
                                                    •
                                                    <span x-text="product.legal_entity"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- PRODUCTO SELECCIONADO -->
                                    <div x-show="selectedProduct" class="mt-2 p-3 bg-indigo-50 rounded-lg border">
                                        <div class="flex justify-between text-xs font-semibold text-indigo-800">
                                            <span>Producto seleccionado</span>
                                            <button type="button" @click="clearSelection" class="text-red-600">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </div>
                                        <div class="text-sm font-medium" x-text="selectedProduct.name"></div>
                                        <div class="text-xs text-gray-600">
                                            Stock: <span x-text="maxQuantity"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- CANTIDAD -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Cantidad</label>
                                    <input type="number"
                                        name="quantity"
                                        x-model.number="quantity"
                                        min="1"
                                        :max="maxQuantity"
                                        class="w-full rounded-lg border-gray-300">
                                </div>

                                <!-- MODALIDAD -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Modalidad</label>
                                    <select name="billing_mode"
                                            x-model="billingMode"
                                            @change="updatePriceField"
                                            class="w-full rounded-lg border-gray-300">
                                        <option value="rental">Renta</option>
                                        <option value="sale">Venta</option>
                                    </select>
                                </div>

                                <!-- PRECIO -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        <span x-text="billingMode === 'rental' ? 'Precio renta' : 'Precio venta'"></span>
                                    </label>
                                    <input type="number"
                                        step="0.01"
                                        min="0"
                                        x-model.number="price"
                                        :name="billingMode === 'rental' ? 'rental_price' : 'sale_price'"
                                        class="w-full rounded-lg border-gray-300">
                                </div>

                                <template x-if="billingMode === 'rental'">
                                    <input type="hidden" name="sale_price" value="0">
                                </template>
                                <template x-if="billingMode === 'sale'">
                                    <input type="hidden" name="rental_price" value="0">
                                </template>

                            </div>

                            <div class="mt-4 flex justify-end">
                                <button type="submit"
                                        :disabled="!selectedProductId || quantity > maxQuantity || price <= 0"
                                        class="px-4 py-2 rounded-lg text-white text-xs font-semibold"
                                        :class="(!selectedProductId || quantity > maxQuantity || price <= 0)
                                            ? 'bg-gray-400'
                                            : 'bg-indigo-600 hover:bg-indigo-700'">
                                    <i class="fas fa-plus mr-2"></i>Agregar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            
            
            <!-- Products Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list mr-2"></i>Productos ({{ $quotation->items->count() }})
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origen</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                @if($quotation->status === 'draft')
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($quotation->items as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                 
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                            {{ $item->productUnit->product->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-800 font-semibold text-sm">
                                            {{ $item->quantity ?? 1 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $item->sourceLegalEntity->business_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->sourceSubWarehouse->name ?? 'N/A' }}</div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-sm text-gray-900 text-right font-medium">
                                        @if($item->billing_mode === 'rental')
                                            ${{ number_format($item->rental_price, 2) }}
                                        @else
                                            ${{ number_format($item->sale_price, 2) }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 text-right font-bold">
                                        @php
                                            $price = $item->billing_mode === 'rental' ? $item->rental_price : $item->sale_price;
                                            $qty = $item->quantity ?? 1;
                                            $total = $price * $qty;
                                        @endphp
                                        ${{ number_format($total, 2) }}
                                    </td>
                                    
                                    @if($quotation->status === 'draft')
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form action="{{ route('quotations.remove-item', [$quotation, $item]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        onclick="return confirm('¿Eliminar este producto?')"
                                                        class="text-red-600 hover:text-red-900 transition-colors">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-lg font-medium mb-2">No hay productos agregados</p>
                                        @if($quotation->status === 'draft')
                                            <p class="text-sm">Usa el formulario de arriba para agregar productos a esta cotización</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($quotation->items->count() > 0)
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-right font-bold text-gray-900">TOTAL:</td>
                                    <td class="px-6 py-4 text-right font-bold text-indigo-600 text-lg">
                                        @php
                                            $grandTotal = $quotation->items->sum(function($item) {
                                                $price = $item->billing_mode === 'rental' ? $item->rental_price : $item->sale_price;
                                                return $price * ($item->quantity ?? 1);
                                            });
                                        @endphp
                                        ${{ number_format($grandTotal, 2) }}
                                    </td>
                                  
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            
        </div>

        
    </div>

    @push('scripts')
    <script>
    function quotationApp() {
        return {
            searchQuery: '',
            products: [],
            selectedProduct: null,
            selectedProductId: '',
            showResults: false,
            loading: false,
            quantity: 1,
            maxQuantity: 999,
            billingMode: 'rental',
            price: 0, // AÑADIDO: Propiedad para almacenar y modelar el precio

            // Inicialización (llamada al inicio si se usa x-init)
            init() {
                this.updatePriceField();
            },

            // Lógica para actualizar el campo de precio según la modalidad
            updatePriceField() {
                if (this.selectedProduct) {
                    if (this.billingMode === 'rental') {
                        // Usar el precio de renta si está disponible, sino 0
                        this.price = this.selectedProduct.rental_price || 0; 
                    } else { // 'sale'
                        // Usar el precio de venta si está disponible, sino 0
                        this.price = this.selectedProduct.sale_price || 0;
                    }
                } else {
                    this.price = 0;
                }
            },
            
            async searchProducts() {
                if (this.searchQuery.length < 2) {
                    this.products = [];
                    return;
                }
                
                this.loading = true;
                
                try {
                    // Nota: Asegúrate que esta ruta es accesible y devuelve un array de productos
                    const response = await fetch(`{{ route('products.searchApi') }}?q=${encodeURIComponent(this.searchQuery)}&available=true`);
                    const data = await response.json();
                    this.products = data;
                    this.showResults = true;
                    
                    this.$nextTick(() => {
                    });
                } catch (error) {
                    console.error('Error searching products:', error);
                    this.products = [];
                } finally {
                    this.loading = false;
                }
            },
            
            positionDropdown() {
                // ... (Tu función de posicionamiento actual, funciona bien si tienes el x-ref="dropdown"
                // en un elemento con posición absoluta o fija globalmente)
                if (!this.$refs.searchInput || !this.$refs.dropdown) return;
                // Si el dropdown es un elemento hijo posicionado de forma absoluta dentro de un relativo (como en el Blade que mostraste),
                // esta función no es necesaria y debería eliminarse para evitar conflictos de posicionamiento.
                // La dejaré comentada por ahora, ya que la estructura Blade original usa 'absolute' para el dropdown.
                
                /*
                const input = this.$refs.searchInput;
                const dropdown = this.$refs.dropdown;
                const rect = input.getBoundingClientRect();
                
                dropdown.style.top = (rect.bottom + window.scrollY) + 'px';
                dropdown.style.left = rect.left + 'px';
                dropdown.style.width = rect.width + 'px';
                */
            },
            
            selectProduct(product) {
                this.selectedProduct = product;
                this.selectedProductId = product.id;
                this.searchQuery = product.name;
                this.maxQuantity = product.available_quantity || 1; 
                this.quantity = 1; // Reseteamos la cantidad a 1 al seleccionar
                this.showResults = false;

                // LLAMADA CLAVE: Actualizar el campo de precio basado en la modalidad
                this.updatePriceField();
                
                this.$nextTick(() => this.$refs.searchInput.focus()); 
            },
            
            clearSelection() {
                this.selectedProduct = null;
                this.selectedProductId = '';
                this.searchQuery = '';
                this.products = [];
                this.maxQuantity = 999;
                this.quantity = 1;
                this.price = 0; // También limpiar el precio
            },
            
            validateForm(e) {
                if (!this.selectedProductId) {
                    e.preventDefault();
                    alert('Por favor selecciona un producto de la lista');
                    return false;
                }
                if (this.quantity > this.maxQuantity) {
                    e.preventDefault();
                    alert('La cantidad excede el stock disponible.');
                    return false;
                }
            }
        }
    }
    
    // Si estás usando la función externa `positionDropdown`, ya no es necesaria si el dropdown es 'absolute'
    // dentro de un contenedor 'relative'.
    /*
    window.addEventListener('scroll', () => {
        const app = window.Alpine ? Alpine.raw(document.querySelector('[x-data]').__x : null;
        if (app && app.showResults) {
             // app.positionDropdown(); // Comentado, ya que el código HTML es 'relative'/'absolute'
        }
    });
    */
</script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush
</x-app-layout>