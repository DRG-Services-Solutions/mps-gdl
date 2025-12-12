<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Crear Prearmado Quirúrgico
            </h2>
            <a href="{{ route('surgical-kits.index') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            {{-- Mensajes de error --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div x-data="surgicalKitForm()" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('surgical-kits.store') }}">
                    @csrf

                    <div class="p-6 space-y-6">
                        
                        {{-- Información Básica --}}
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                            
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                {{-- Nombre --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">
                                        Nombre del Prearmado <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name') }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Tipo de Cirugía --}}
                                <div>
                                    <label for="surgery_type" class="block text-sm font-medium text-gray-700">
                                        Tipo de Cirugía <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="surgery_type" 
                                           id="surgery_type" 
                                           value="{{ old('surgery_type') }}"
                                           required
                                           placeholder="Ej: Ortopédica, Cardiovascular, etc."
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    @error('surgery_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Descripción --}}
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700">
                                        Descripción
                                    </label>
                                    <textarea name="description" 
                                              id="description" 
                                              rows="3"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Estado --}}
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Prearmado activo</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Productos del Prearmado --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Productos <span class="text-red-500">*</span>
                                </h3>
                                <button type="button" 
                                        @click="addProduct()"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Agregar Producto
                                </button>
                            </div>

                            {{-- Tabla de productos --}}
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Producto</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Cantidad</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Notas</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="(product, index) in products" :key="index">
                                            <tr>
                                                <td class="px-6 py-4">
                                                    <select :name="'products[' + index + '][product_id]'" 
                                                            required
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                        <option value="">Seleccionar producto...</option>
                                                        @foreach($products as $prod)
                                                            <option value="{{ $prod->id }}">
                                                                {{ $prod->code }} - {{ $prod->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <input type="number" 
                                                           :name="'products[' + index + '][quantity]'" 
                                                           x-model="product.quantity"
                                                           min="1"
                                                           required
                                                           class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </td>
                                                <td class="px-6 py-4">
                                                    <input type="text" 
                                                           :name="'products[' + index + '][notes]'" 
                                                           x-model="product.notes"
                                                           placeholder="Opcional"
                                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <button type="button" 
                                                            @click="removeProduct(index)"
                                                            class="text-red-600 hover:text-red-900">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>

                                        {{-- Mensaje cuando no hay productos --}}
                                        <template x-if="products.length === 0">
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center">
                                                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                                    </svg>
                                                    <p class="mt-2 text-sm text-gray-500">
                                                        No hay productos agregados. Haz clic en "Agregar Producto" para comenzar.
                                                    </p>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Resumen --}}
                            <div x-show="products.length > 0" class="mt-4 p-4 bg-blue-50 rounded-md">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-blue-900">Total de productos:</span>
                                    <span class="text-sm font-bold text-blue-900" x-text="products.length"></span>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-sm font-medium text-blue-900">Total de piezas:</span>
                                    <span class="text-sm font-bold text-blue-900" x-text="totalPieces"></span>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Botones de Acción --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('surgical-kits.index') }}" 
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Crear Prearmado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function surgicalKitForm() {
            return {
                products: [],
                
                addProduct() {
                    this.products.push({
                        product_id: '',
                        quantity: 1,
                        notes: ''
                    });
                },
                
                removeProduct(index) {
                    this.products.splice(index, 1);
                },
                
                get totalPieces() {
                    return this.products.reduce((sum, product) => {
                        return sum + parseInt(product.quantity || 0);
                    }, 0);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>