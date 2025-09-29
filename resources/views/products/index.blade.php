<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ __('Inventario de Productos Quirúrgicos') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Administra los sets, equipos y consumibles utilizados en procedimientos MPS.') }}
                </p>
            </div>
           
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
                    <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-200" x-data="{ show: true }" x-show="show" x-transition>
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
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Producto / Código') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Categoría') }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Especialidad') }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Stock / Mínimo') }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('RFID') }}
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($products as $product)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-600">Ref: {{ $product->code ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $product->category?->name ?? 'Sin categoría' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $product->medicalSpecialty?->name ?? 'General' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div>{{ $product->current_stock }}</div>
                                        <div class="text-xs text-gray-500">Mín: {{ $product->minimum_stock }}</div>
                                        @if($product->current_stock <= $product->minimum_stock)
                                            <i class="fas fa-exclamation-circle text-red-500 ml-1" title="Stock bajo"></i>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($product->rfid_enabled)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-check w-3 h-3 mr-1"></i> Sí
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                <i class="fas fa-ban w-3 h-3 mr-1"></i> No
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('products.edit', $product->id) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border rounded hover:bg-gray-50">
                                                <i class="fas fa-edit mr-1"></i> {{ __('Editar') }}
                                            </a>
                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-2 text-sm text-red-700 bg-red-50 border rounded hover:bg-red-100">
                                                    <i class="fas fa-trash-alt mr-1"></i> {{ __('Eliminar') }}
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

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</x-app-layout>
