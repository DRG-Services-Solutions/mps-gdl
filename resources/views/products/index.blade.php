<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Catalogo de Productos Quirúrgicos') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Administra equipos y consumibles utilizados en procedimientos MPS.') }}
                </p>
            </div>
            
            {{-- Botón de Crear Producto --}}
          
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                
                <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-cyan-50 border-b border-gray-200">
                    
                    <div class="flex items-center justify-between">
                        
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-boxes w-8 h-8 text-teal-600 text-2xl"></i>
                            
                            <div>
                                
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Lista de Artículos') }}</h3>
                                
                                <p class="text-sm text-gray-600">{{ $products->total() }} {{ __('artículos registrados') }}</p>
                            </div>
                        </div>
                            <a href="{{ route('products.create') }}" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                                    <i class="fas fa-plus w-5 h-5 mr-2"></i>
                                    {{ __('Crear Producto') }}
                            </a>
                    </div>    
                </div>

                @if(session('success'))
                    <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-200" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle w-5 h-5 text-emerald-600 mr-2"></i>
                                <span class="text-emerald-800 font-medium">{{ session('success') }}</span>
                            </div>
                            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                               <i class="fas fa-times w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-1/4">
                                    {{ __('Producto / Código') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell">
                                    {{ __('Fabricante') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden md:table-cell">
                                    {{ __('Categoría') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden xl:table-cell">
                                    {{ __('Subcategoría') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Especialidad') }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider w-1/12">
                                    {{ __('Stock') }}
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider w-1/12">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($products as $product)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    {{-- Columna Producto / Código --}}
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-600">Ref: {{ $product->code ?? 'N/A' }}</div>
                                    </td>
                                    
                                    {{-- Columna Fabricante --}}
                                    <td class="px-6 py-4 text-sm text-gray-700 hidden lg:table-cell">
                                        {{ $product->manufacturer->name ?? 'Sin fabricante' }}
                                    </td>

                                    {{-- Columna Categoría --}}
                                    <td class="px-6 py-4 text-sm text-gray-700 hidden md:table-cell">
                                        {{ $product->category->name ?? 'Sin categoría' }}
                                    </td>

                                    {{-- Columna Subcategoría --}}
                                    <td class="px-6 py-4 text-sm text-gray-700 hidden xl:table-cell">
                                        {{ $product->subcategory->name ?? 'N/A' }}
                                    </td>

                                    {{-- Columna Especialidad --}}
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $product->medicalSpecialty->name ?? 'General' }}
                                    </td>

                                    {{-- Columna Stock / RFID (Combinada) --}}
                                    <td class="px-6 py-4 text-center">
                                        <div class="font-bold text-base {{ $product->current_stock <= $product->minimum_stock ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ $product->current_stock }}
                                            @if($product->current_stock <= $product->minimum_stock)
                                                <i class="fas fa-exclamation-circle text-red-500 ml-1 text-sm" title="Stock bajo"></i>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            RFID: @if($product->rfid_enabled)
                                                <i class="fas fa-check-circle text-blue-500"></i>
                                            @else
                                                <i class="fas fa-ban text-gray-400"></i>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Columna Acciones --}}
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('products.edit', $product->id) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border rounded hover:bg-gray-50">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-2 text-sm text-red-700 bg-red-50 border rounded hover:bg-red-100"
                                                        onclick="return confirm('¿Está seguro de eliminar este producto?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($products->hasPages())
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>