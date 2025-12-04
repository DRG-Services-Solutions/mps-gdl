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

    <div class="py-6" x-data="{ showAddModal: false, selectedProductUnit: null, billingMode: 'rental', rentalPrice: 0, salePrice: 0 }">
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
            
            <!-- Products -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list mr-2"></i>Productos
                    </h3>
                    @if($quotation->status === 'draft')
                        <button @click="showAddModal = true" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            <i class="fas fa-plus mr-2"></i>Agregar Producto
                        </button>
                    @endif
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EPC/Serial</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origen</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modalidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                @if($quotation->status === 'draft')
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($quotation->items as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->product->code }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $item->productUnit->epc ?? $item->productUnit->serial_number ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $item->sourceLegalEntity->business_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->sourceSubWarehouse->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->billing_mode === 'rental')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <i class="fas fa-sync mr-1"></i>RENTA
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                <i class="fas fa-handshake mr-1"></i>CONSIGNACIÓN
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if($item->billing_mode === 'rental')
                                            ${{ number_format($item->rental_price, 2) }}
                                        @else
                                            ${{ number_format($item->sale_price, 2) }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $itemStatusClasses = [
                                                'pending' => 'bg-gray-100 text-gray-800',
                                                'sent' => 'bg-yellow-100 text-yellow-800',
                                                'returned' => 'bg-green-100 text-green-800',
                                                'used' => 'bg-red-100 text-red-800',
                                                'invoiced' => 'bg-indigo-100 text-indigo-800',
                                            ];
                                            $itemStatusLabels = [
                                                'pending' => 'Pendiente',
                                                'sent' => 'Enviado',
                                                'returned' => 'Retornado',
                                                'used' => 'Usado',
                                                'invoiced' => 'Facturado',
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $itemStatusClasses[$item->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $itemStatusLabels[$item->status] ?? $item->status }}
                                        </span>
                                    </td>
                                    @if($quotation->status === 'draft')
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form action="{{ route('quotations.remove-item', [$quotation, $item]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        onclick="return confirm('¿Eliminar este producto?')"
                                                        class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                        <p>No hay productos agregados</p>
                                        @if($quotation->status === 'draft')
                                            <button @click="showAddModal = true" class="mt-4 inline-block text-indigo-600 hover:text-indigo-900 font-medium">
                                                <i class="fas fa-plus mr-1"></i>Agregar primer producto
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>

        <!-- Modal: Add Product -->
        <div x-show="showAddModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <!-- Background overlay -->
                <div x-show="showAddModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="showAddModal = false"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     aria-hidden="true"></div>

                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div x-show="showAddModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <form action="{{ route('quotations.add-item', $quotation) }}" method="POST">
                        @csrf
                        
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-plus text-indigo-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Agregar Producto
                                    </h3>
                                    <div class="mt-4 space-y-4">
                                        
                                        <!-- Product Unit -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Producto <span class="text-red-500">*</span>
                                            </label>
                                            <select name="product_unit_id" 
                                                    required
                                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                                <option value="">Seleccionar...</option>
                                                @foreach($availableProducts as $pu)
                                                    <option value="{{ $pu->id }}">
                                                        {{ $pu->product->name }} - {{ $pu->epc ?? $pu->serial_number ?? 'N/A' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <!-- Billing Mode -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Modalidad <span class="text-red-500">*</span>
                                            </label>
                                            <select name="billing_mode" 
                                                    x-model="billingMode"
                                                    required
                                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                                <option value="rental">Renta</option>
                                                <option value="consignment">Consignación</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Rental Price -->
                                        <div x-show="billingMode === 'rental'">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Precio de Renta <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" 
                                                   name="rental_price" 
                                                   step="0.01"
                                                   min="0"
                                                   placeholder="0.00"
                                                   class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                        </div>
                                        
                                        <!-- Sale Price -->
                                        <div x-show="billingMode === 'consignment'">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Precio de Venta <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" 
                                                   name="sale_price" 
                                                   step="0.01"
                                                   min="0"
                                                   placeholder="0.00"
                                                   class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <i class="fas fa-check mr-2"></i>Agregar
                            </button>
                            <button type="button" 
                                    @click="showAddModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>